<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Wilayah;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;

class ManageDesa extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Manajemen Desa';
    protected static ?string $title = 'Manajemen Desa';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.manage-desa';

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
            return 'Manajemen Desa Kabupaten';
        } elseif (PanelAccess::isAdminDesa()) {
            return 'Manajemen Desa';
        } elseif (PanelAccess::isOperatorOpd()) {
            return 'Manajemen OPD';
        }
        return 'Manajemen Desa';
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->orderBy('nama')->pluck('nama', 'id'))
                ->default(fn () => AdminKecamatan::kecamatanId())
                ->disabled(fn () => PanelAccess::isAdminKecamatan())
                ->required(),
            TextInput::make('nama')
                ->label('Nama Desa')
                ->required()
                ->maxLength(255),
            TextInput::make('kode')
                ->label('Kode Desa')
                ->required()
                ->maxLength(32),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function create(): void
    {
        $state = $this->form->getState();

        $desa = Wilayah::firstOrCreate(
            ['parent_id' => $state['kecamatan_id'], 'tipe' => Wilayah::TIPE_DESA, 'nama' => $state['nama']],
            ['kode_bps' => $state['kode']]
        );

        if ($desa->wasRecentlyCreated) {
            \Filament\Notifications\Notification::make()->success()->title('Desa baru berhasil ditambahkan.')->send();
        } else {
            \Filament\Notifications\Notification::make()->warning()->title('Desa sudah ada untuk kecamatan ini.')->send();
        }

        $this->form->fill();
    }

    public function getDesasProperty()
    {
        return Wilayah::query()
            ->where('tipe', Wilayah::TIPE_DESA)
            ->with('parent')
            ->orderBy('nama')
            ->when(AdminKecamatan::kecamatanId(), fn ($q) => $q->where('parent_id', AdminKecamatan::kecamatanId()))
            ->get();
    }
}
