<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AIConversationRepository
{
    public function forUser(User $user): Collection
    {
        return AIConversation::query()
            ->whereBelongsTo($user)
            ->latest('updated_at')
            ->get();
    }

    public function create(User $user, string $title = 'Chat Baru'): AIConversation
    {
        return $user->aiConversations()->create(['title' => $title]);
    }

    public function findForUser(User $user, int $id): AIConversation
    {
        return AIConversation::query()->whereBelongsTo($user)->with('messages')->findOrFail($id);
    }

    public function addMessage(AIConversation $conversation, string $role, string $content, ?int $input = null, ?int $output = null): AIMessage
    {
        $message = $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'token_input' => $input,
            'token_output' => $output,
        ]);

        $conversation->touch();

        return $message;
    }

    public function delete(AIConversation $conversation): void
    {
        $conversation->delete();
    }
}
