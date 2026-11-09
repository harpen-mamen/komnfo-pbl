<?php

namespace App\Filament\Resources\PortalContentResource\Pages;

use App\Filament\Resources\PortalContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPortalContents extends ListRecords
{
    protected static string $resource = PortalContentResource::class;

    protected function getHeaderActions(): array
    {
        $type = $this->tableFilters['type']['value'] ?? request()->query('filters.type.value', 'hero');
        $slug = $this->tableFilters['slug']['value'] ?? request()->query('filters.slug.value');
        $labels = ['hero' => 'Konten Beranda', 'news' => 'Berita', 'service' => 'Layanan Publik', 'page' => 'Halaman Tentang Daerah', 'history' => 'Sejarah', 'vision' => 'Visi & Misi', 'official' => 'Profil Pejabat', 'opd_profile' => 'Profil OPD'];
        $label = $slug === 'sambutan-bupati' ? 'Sambutan' : ($slug === 'geografi' ? 'Geografi' : ($slug === 'lambang-daerah' ? 'Lambang Daerah' : ($labels[$type] ?? 'Konten')));
        return [CreateAction::make()->label('Tambah ' . $label)->url(fn () => PortalContentResource::getUrl('create', array_filter(['type' => $type, 'slug' => $slug])))];
    }

    public function getTitle(): string | Htmlable
    {
        $type = $this->tableFilters['type']['value'] ?? request()->query('filters.type.value', 'hero');
        $slug = $this->tableFilters['slug']['value'] ?? request()->query('filters.slug.value');
        $sections = ['sambutan-bupati' => 'Sambutan Bupati', 'tentang-daerah' => 'Halaman Utama Tentang Daerah', 'geografi' => 'Geografi', 'lambang-daerah' => 'Lambang Daerah'];
        if ($slug && isset($sections[$slug])) return 'Konten ' . $sections[$slug];
        return match ($type) {
            'news' => 'Berita Website', 'service' => 'Layanan Publik', 'page' => 'Tentang Daerah',
            'history' => 'Sejarah Sangihe', 'vision' => 'Visi & Misi', 'official' => 'Profil Pejabat',
            'opd_profile' => 'Profil Dinas & OPD', default => 'Beranda Website',
        };
    }
}
