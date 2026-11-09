<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * QuickLinksWidget — 4 tile action pintas, role-aware.
 *
 * NOTE: Menggunakan URL path langsung (`/admin/...`) bukan Resource::getUrl()
 * karena Filament v2 punya bug slugification untuk namespaced resource
 * (route jadi 'w-o-r-k-f-l-o-w/submission-batches.create'). Path langsung
 * lebih reliable di Filament 2.x.
 */
class QuickLinksWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-links';
    protected int|string|array $columnSpan = 'full';

    /** @var array<int, array{label:string,url:string,icon:string,bg:string,desc?:string}> */
    public array $links = [];

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }
        $this->links = $this->resolveLinks($u);
    }

    private function resolveLinks(User $u): array
    {
        $admin = '/admin';
        $editProfile = fn () => $admin . '/pengaturan-profil';

        return match ($u->role?->tier()) {
            'kabupaten' => [
                ['label' => 'Buat Batch',      'icon' => 'plus',     'bg' => '#1c4ed8', 'url' => $admin . '/submission-batches/create', 'desc' => 'Submission baru'],
                ['label' => 'Tambah User',     'icon' => 'user',     'bg' => '#0B2447', 'url' => $admin . '/users/create',                'desc' => 'Akun baru'],
                ['label' => 'Kelola User',     'icon' => 'users',    'bg' => '#3B82F6', 'url' => $admin . '/users',                      'desc' => 'Semua akun'],
                ['label' => 'Profil Saya',     'icon' => 'user-cog', 'bg' => '#10B981', 'url' => $editProfile(),                         'desc' => 'Edit akun'],
            ],
            'opd' => [
                ['label' => 'Buat Batch',      'icon' => 'plus',     'bg' => '#1c4ed8', 'url' => $admin . '/submission-batches/create', 'desc' => 'Submission unit saya'],
                ['label' => 'Tambah Dataset',  'icon' => 'database', 'bg' => '#3B82F6', 'url' => $admin . '/datasets/create',            'desc' => 'Katalog dataset'],
                ['label' => 'Input Objek',     'icon' => 'map',      'bg' => '#8B5CF6', 'url' => $admin . '/objek-lapangans/create',     'desc' => 'Lapangan mobile'],
                ['label' => 'Profil Saya',     'icon' => 'user-cog', 'bg' => '#10B981', 'url' => $editProfile(),                         'desc' => 'Edit akun'],
            ],
            'kecamatan' => [
                ['label' => 'Data Keluarga',   'icon' => 'home',     'bg' => '#1c4ed8', 'url' => $admin . '/keluargas',                  'desc' => 'Lihat KK'],
                ['label' => 'Data Penduduk',   'icon' => 'user',     'bg' => '#3B82F6', 'url' => $admin . '/penduduks',                  'desc' => 'Lihat penduduk'],
                ['label' => 'Objek Lapangan',  'icon' => 'map',      'bg' => '#8B5CF6', 'url' => $admin . '/objek-lapangans',             'desc' => 'List objek'],
                ['label' => 'Profil Saya',     'icon' => 'user-cog', 'bg' => '#10B981', 'url' => $editProfile(),                         'desc' => 'Edit akun'],
            ],
            'desa' => [
                ['label' => 'Input Objek',     'icon' => 'map',      'bg' => '#1c4ed8', 'url' => $admin . '/objek-lapangans/create',     'desc' => 'Tambah objek desa'],
                ['label' => 'Data Keluarga',   'icon' => 'home',     'bg' => '#3B82F6', 'url' => $admin . '/keluargas',                  'desc' => 'Cek KK'],
                ['label' => 'Data Penduduk',   'icon' => 'user',     'bg' => '#8B5CF6', 'url' => $admin . '/penduduks',                  'desc' => 'Cek penduduk'],
                ['label' => 'Profil Saya',     'icon' => 'user-cog', 'bg' => '#10B981', 'url' => $editProfile(),                         'desc' => 'Edit akun'],
            ],
            default => [
                ['label' => 'Profil Saya',     'icon' => 'user-cog', 'bg' => '#1c4ed8', 'url' => $editProfile(), 'desc' => 'Edit akun'],
            ],
        };
    }
}
