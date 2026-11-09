<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Unit;
use App\Models\WORKFLOW\SubmissionBatch;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * GreetingWidget — strip atas dengan sapaan personal + quick stats 1-baris.
 *
 * Update otomatis setiap 30 detik (wire:poll.30s di view) untuk indikator realtime-ish
 * tanpa biaya server berlebihan.
 */
class GreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.greeting';

    /** Full-width (12-col). */
    protected int|string|array $columnSpan = 'full';

    public string $userName = '';
    public string $userRole = '';
    public string $userRoleLabel = '';
    public string $roleColor = '#6B7280';
    public string $greeting = '';
    public string $quickStats = '';

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }

        $this->userName        = $u->nama;
        $this->userRole        = $u->role?->value ?? '';
        $this->userRoleLabel   = $u->role?->label() ?? '-';
        $this->roleColor       = $this->resolveRoleColor($this->userRole);
        $this->greeting        = $this->resolveGreeting();
        $this->quickStats      = $this->resolveQuickStats($u);
    }

    private function resolveGreeting(): string
    {
        $h = (int) date('G');
        return match (true) {
            $h < 11 => 'Selamat pagi',
            $h < 15 => 'Selamat siang',
            $h < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    private function resolveRoleColor(string $role): string
    {
        return match ($role) {
            'pembina'        => '#10B981',
            'walidata'       => '#1c4ed8',
            'admin_opd'      => '#3B82F6',
            'operator_opd'   => '#6366F1',
            'kepala_dinas'   => '#F59E0B',
            'admin_kecamatan' => '#8B5CF6',
            'admin_desa'     => '#6B7280',
            default          => '#6B7280',
        };
    }

    private function resolveQuickStats(User $u): string
    {
        // Bentuk: "📊 54 unit aktif · 📋 5 batch menunggu · ✅ 3 dipublikasi minggu ini"
        $unitCount = Unit::where('aktif', 1)->count();
        $pendingCount = SubmissionBatch::whereIn('status', ['draft','approved_internal','submitted','verified'])->count();
        $weekPublished = SubmissionBatch::where('status', 'published')
            ->where('published_at', '>=', now()->subWeek())->count();

        return sprintf(
            '📊 %d unit aktif · 📋 %d batch menunggu · ✅ %d dipublikasi minggu ini',
            $unitCount,
            $pendingCount,
            $weekPublished,
        );
    }
}
