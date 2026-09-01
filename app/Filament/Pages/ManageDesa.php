<?php

namespace App\Filament\Pages;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;

class ManageDesa extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Manajemen Desa';
    protected static ?string $title = 'Manajemen Desa';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.manage-desa';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(
            PanelAccess::isAdminKecamatan() || 
            PanelAccess::isKominfoKabupaten() || 
            false || 
            PanelAccess::isOperatorOpd(), 
            403
        );

        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan() || 
               PanelAccess::isKominfoKabupaten() || 
               false || 
               PanelAccess::isOperatorOpd();
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
            Forms\Components\Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                ->default(fn () => PanelAccess::user()?->kecamatan_id)
                ->disabled(fn () => PanelAccess::isAdminKecamatan())
                ->required(),
            Forms\Components\TextInput::make('nama')
                ->label('Nama Desa')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('kode')
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

        $desa = Desa::firstOrCreate(
            ['kecamatan_id' => $state['kecamatan_id'], 'nama' => $state['nama']],
            ['kode' => $state['kode']]
        );

        if ($desa->wasRecentlyCreated) {
            $this->notify('success', 'Desa baru berhasil ditambahkan.');
        } else {
            $this->notify('warning', 'Desa sudah ada untuk kecamatan ini.');
        }

        $this->form->fill();
    }

    public function getDesasProperty()
    {
        return Desa::query()
            ->with('kecamatan')
            ->orderBy('nama')
            ->where('kecamatan_id', PanelAccess::user()?->kecamatan_id)
            ->get();
    }
}
