<?php

namespace App\Filament\Pages;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;

class ManageKecamatan extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-location-marker';
    protected static ?string $navigationLabel = 'Manajemen Kecamatan';
    protected static ?string $title = 'Manajemen Kecamatan';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.manage-kecamatan';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel(), 403);

        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('kabupaten_id')
                ->label('Kabupaten')
                ->options(fn () => Kabupaten::query()->orderBy('nama')->pluck('nama', 'id'))
                ->required(),
            Forms\Components\TextInput::make('nama')
                ->label('Nama Kecamatan')
                ->required()
                ->maxLength(255),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function create(): void
    {
        $state = $this->form->getState();

        $kecamatan = Kecamatan::firstOrCreate(
            ['kabupaten_id' => $state['kabupaten_id'], 'nama' => $state['nama']],
            ['kabupaten_id' => $state['kabupaten_id'], 'nama' => $state['nama']]
        );

        if ($kecamatan->wasRecentlyCreated) {
            $this->notify('success', 'Kecamatan baru berhasil ditambahkan.');
        } else {
            $this->notify('warning', 'Kecamatan sudah ada, tidak ada yang ditambahkan.');
        }

        $this->form->fill();
    }

    public function getKecamatansProperty()
    {
        return Kecamatan::query()
            ->with('kabupaten')
            ->orderBy('nama')
            ->get();
    }
}
