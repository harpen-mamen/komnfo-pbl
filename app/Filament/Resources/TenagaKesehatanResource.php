<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenagaKesehatanResource\Pages;
use App\Models\TenagaKesehatan;
use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TenagaKesehatanResource extends Resource
{
    protected static ?string $model = TenagaKesehatan::class;
    protected static ?string $navigationIcon = "heroicon-o-user-group";
    protected static ?string $navigationLabel = "Data Tenaga Kesehatan";
    protected static ?int $navigationSort = 19;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(["penduduk", "desa", "kecamatan", "createdBy"]);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan() || PanelAccess::isOperatorPuskesmas();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan() || PanelAccess::isOperatorPuskesmas();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::isOperatorPuskesmas();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Pilih Tenaga Kesehatan")
                ->description("Cari dan pilih tenaga kesehatan.")
                ->icon("heroicon-o-user")
                ->schema([
                    Forms\Components\Select::make("penduduk_id")
                        ->label("Cari Nama")
                        ->options(fn () => \App\Models\Penduduk::query()->inApprovedBatch()->orderBy("nama_lengkap")->pluck("nama_lengkap", "id"))
                        ->searchable(["nama_lengkap", "nik"])
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => self::fillFromPenduduk($state, $set)),
                ]),
            Forms\Components\Section::make("Data Profesi")
                ->icon("heroicon-o-academic-cap")
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make("profesi")
                            ->label("Profesi")
                            ->options([
                                'Dokter Umum' => 'Dokter Umum',
                                'Dokter Gigi' => 'Dokter Gigi',
                                'Dokter Spesialis' => 'Dokter Spesialis',
                                'Perawat' => 'Perawat',
                                'Bidan' => 'Bidan',
                                'Apoteker' => 'Apoteker',
                                'Tenaga Farmasi' => 'Tenaga Farmasi',
                                'Ahli Gizi' => 'Ahli Gizi',
                                'Tenaga Kesehatan Masyarakat' => 'Tenaga Kesehatan Masyarakat',
                                'Sanitarian' => 'Sanitarian',
                                'Analis Kesehatan' => 'Analis Kesehatan',
                                'Radiografer' => 'Radiografer',
                                'Fisioterapis' => 'Fisioterapis',
                                'Teknisi Elektromedis' => 'Teknisi Elektromedis',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make("str")
                            ->label("No. STR"),
                        Forms\Components\DatePicker::make("masa_berlaku_str")
                            ->label("Masa Berlaku STR"),
                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make("sip")
                            ->label("No. SIP"),
                        Forms\Components\DatePicker::make("masa_berlaku_sip")
                            ->label("Masa Berlaku SIP"),
                        Forms\Components\TextInput::make("tempat_kerja")
                            ->label("Tempat Kerja"),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make("jenis_fasilitas")
                            ->label("Jenis Fasilitas")
                            ->options([
                                'Puskesmas' => 'Puskesmas',
                                'Puskesmas Pembantu' => 'Puskesmas Pembantu',
                                'Posyandu' => 'Posyandu',
                                'Rumah Sakit' => 'Rumah Sakit',
                                'Klinik' => 'Klinik',
                                'Praktik Mandiri' => 'Praktik Mandiri',
                            ]),
                        Forms\Components\Select::make("status_kepegawaian")
                            ->label("Status Kepegawaian")
                            ->options([
                                'PNS' => 'PNS',
                                'PPPK' => 'PPPK',
                                'Honorer' => 'Honorer',
                                'Kontrak' => 'Kontrak',
                                'Swasta' => 'Swasta',
                            ]),
                    ]),
                ]),
            Forms\Components\Section::make("Catatan")
                ->icon("heroicon-o-document-text")
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make("catatan")->label("Catatan")->rows(3),
                ]),
        ]);
    }

    private static function fillFromPenduduk(?int $pendudukId, callable $set): void
    {
        if (!$pendudukId) return;
        $p = \App\Models\Penduduk::query()->with("keluarga.rumah.desa")->find($pendudukId);
        if (!$p) return;
        $set("desa_id", $p->keluarga?->rumah?->desa_id);
        $set("kecamatan_id", $p->keluarga?->rumah?->desa?->kecamatan_id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("penduduk.nama_lengkap")->label("Nama")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("profesi")->label("Profesi")->sortable(),
                Tables\Columns\TextColumn::make("str")->label("No. STR"),
                Tables\Columns\TextColumn::make("tempat_kerja")->label("Tempat Kerja"),
                Tables\Columns\TextColumn::make("created_at")->label("Tgl Data")->date("d/m/Y")->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("kecamatan_id")->label("Kecamatan")->options(fn () => \App\Models\Kecamatan::query()->orderBy("nama")->pluck("nama","id")),
                Tables\Filters\SelectFilter::make("profesi")->label("Profesi"),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort("created_at","desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListTenagaKesehatans::route("/"),
            "create" => Pages\CreateTenagaKesehatan::route("/create"),
            "edit" => Pages\EditTenagaKesehatan::route("/{record}/edit"),
        ];
    }
}
