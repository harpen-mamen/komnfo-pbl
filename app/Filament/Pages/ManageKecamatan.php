<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;

class ManageKecamatan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Manajemen Kecamatan';
    protected static ?string $title = 'Manajemen Kecamatan';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.manage-kecamatan';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(PanelAccess::isSuperAdmin(), 403);

        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public function getTitle(): string
    {
        if (PanelAccess::isKominfoKabupaten()) {
            return 'Manajemen Kecamatan Kabupaten';
        } elseif (PanelAccess::isAdminDesa()) {
            return 'Manajemen Desa';
        } elseif (PanelAccess::isOperatorOpd()) {
            return 'Manajemen OPD';
        }
        return 'Manajemen Kecamatan';
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('kabupaten_id')
                ->label('Kabupaten')
                ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KABUPATEN)->orderBy('nama')->pluck('nama', 'id'))
                ->required(),
            TextInput::make('nama')
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

        $kecamatan = Wilayah::firstOrCreate(
            ['parent_id' => $state['kabupaten_id'], 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => $state['nama']],
            []
        );

        if ($kecamatan->wasRecentlyCreated) {
            \Filament\Notifications\Notification::make()->success()->title('Kecamatan baru berhasil ditambahkan.')->send();
        } else {
            \Filament\Notifications\Notification::make()->warning()->title('Kecamatan sudah ada, tidak ada yang ditambahkan.')->send();
        }

        $this->form->fill();
    }

    public function getKecamatansProperty()
    {
        return Wilayah::query()
            ->where('tipe', Wilayah::TIPE_KECAMATAN)
            ->with('parent')
            ->orderBy('nama')
            ->get();
    }
}
