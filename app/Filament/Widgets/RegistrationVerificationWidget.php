<?php

namespace App\Filament\Widgets;

use App\Models\UserRegistration;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RegistrationVerificationWidget extends Widget
{
    protected string $view = 'filament.widgets.registration-verification-widget';
    protected static ?string $cluster = 'Verifikasi';
    protected static ?int $sort = 2;

    public ?string $filter = 'pending';

    protected function getHeading(): string
    {
        return 'Verifikasi Registrasi';
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['admin_kominfo', 'admin', 'super_admin']);
    }

    protected function getViewData(): array
    {
        $query = UserRegistration::query();

        if ($this->filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filter === 'all') {
            // show all
        }

        $pendingCount = UserRegistration::where('status', 'pending')->count();
        $recentRegistrations = $query->with(['kecamatan', 'opd'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'pendingCount' => $pendingCount,
            'registrations' => $recentRegistrations,
        ];
    }

    public static function getCluster(): ?string
    {
        return 'Verifikasi';
    }
}
