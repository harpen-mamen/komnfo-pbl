<?php

namespace App\Filament\Pages;

use App\Models\AIConversation;
use App\Services\AI\AIConversationRepository;
use App\Services\AI\AIException;
use App\Services\AI\AIService;
use App\Services\AI\SipanduAIContext;
use App\Support\PanelAccess;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

abstract class AIAssistant extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'AI Assistant';
    protected static ?string $title = 'AI Assistant';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.ai-assistant';

    public string $message = '';
    public string $search = '';
    public string $conversationTitle = '';
    public ?int $conversationId = null;
    public bool $showRename = false;
    public array $messages = [];

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessAIAssistant();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessAIAssistant(), 403);

        $conversation = app(AIConversationRepository::class)->forUser(Auth::user())->first();

        if ($conversation) {
            $this->selectConversation($conversation->id);
        }
    }

    public function getConversationsProperty()
    {
        return app(AIConversationRepository::class)
            ->forUser(Auth::user())
            ->filter(fn (AIConversation $conversation): bool => $this->search === '' || Str::contains(Str::lower($conversation->title), Str::lower($this->search)))
            ->groupBy(fn (AIConversation $conversation): string => $conversation->updated_at->isToday() ? 'Hari ini' : ($conversation->updated_at->isYesterday() ? 'Kemarin' : 'Minggu lalu'));
    }

    public function newConversation(): void
    {
        $conversation = app(AIConversationRepository::class)->create(Auth::user());
        $this->selectConversation($conversation->id);
    }

    public function selectConversation(int $conversationId): void
    {
        $conversation = app(AIConversationRepository::class)->findForUser(Auth::user(), $conversationId);
        $this->conversationId = $conversation->id;
        $this->conversationTitle = $conversation->title;
        $this->messages = $conversation->messages->map(fn ($message): array => [
            'role' => $message->role,
            'content' => $message->content,
        ])->values()->all();
    }

    public function sendMessage(AIService $ai, SipanduAIContext $context): void
    {
        $text = trim($this->message);

        if ($text === '' || $this->conversationId === null) {
            return;
        }

        $key = 'ai-chat:'.Auth::id();
        abort_if(RateLimiter::tooManyAttempts($key, (int) config('ai.rate_limit.max_attempts', 20)), 429);
        RateLimiter::hit($key, (int) config('ai.rate_limit.decay_seconds', 60));

        $repository = app(AIConversationRepository::class);
        $conversation = $repository->findForUser(Auth::user(), $this->conversationId);
        $history = $conversation->messages->whereIn('role', ['user', 'assistant'])->map(fn ($item): array => [
            'role' => $item->role,
            'content' => $item->content,
        ])->values()->all();
        $repository->addMessage($conversation, 'user', $text);

        if ($conversation->title === 'Chat Baru') {
            $conversation->update(['title' => Str::limit($text, 45)]);
            $this->conversationTitle = $conversation->title;
        }

        $systemPrompt = config('ai.system_prompt')."\n\nKonteks database SIPANDU: ".$context->cachedForQuestion($text);
        try {
            $response = $ai->chat($text, $history, $systemPrompt);
        } catch (AIException $exception) {
            Notification::make()->title('AI tidak dapat memproses pesan')->body($exception->getMessage())->danger()->send();
            return;
        }
        $repository->addMessage($conversation, 'assistant', $response->content, $response->inputTokens, $response->outputTokens);
        $this->message = '';
        $this->selectConversation($conversation->id);
    }

    public function renameConversation(): void
    {
        if ($this->conversationId === null || trim($this->conversationTitle) === '') {
            return;
        }

        $conversation = app(AIConversationRepository::class)->findForUser(Auth::user(), $this->conversationId);
        $conversation->update(['title' => Str::limit(trim($this->conversationTitle), 100)]);
        $this->showRename = false;
    }

    public function deleteConversation(): void
    {
        if ($this->conversationId === null) {
            return;
        }

        $repository = app(AIConversationRepository::class);
        $repository->delete($repository->findForUser(Auth::user(), $this->conversationId));
        $this->conversationId = null;
        $this->messages = [];
        $this->newConversation();
    }

    public function exportConversation(string $format = 'txt')
    {
        abort_unless($this->conversationId !== null, 404);
        $conversation = app(AIConversationRepository::class)->findForUser(Auth::user(), $this->conversationId);
        $body = "# {$conversation->title}\n\n".$conversation->messages->map(fn ($message): string => "**".ucfirst($message->role).":**\n\n{$message->content}")->implode("\n\n---\n\n");

        if ($format === 'pdf') {
            return Pdf::loadView('filament.pages.ai-assistant-print', compact('conversation', 'body'))->download('ai-'.$conversation->id.'.pdf');
        }

        if ($format === 'word') {
            return Response::make(view('filament.pages.ai-assistant-print', compact('conversation', 'body')))->header('Content-Type', 'application/msword')->header('Content-Disposition', 'attachment; filename="ai-'.$conversation->id.'.doc"');
        }

        if ($format === 'print') {
            return Response::make(view('filament.pages.ai-assistant-print', compact('conversation', 'body')));
        }

        return Response::make($body)->header('Content-Type', 'text/plain; charset=UTF-8')->header('Content-Disposition', 'attachment; filename="ai-'.$conversation->id.'.txt"');
    }
}
