<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelayananKesehatanResource\Pages;
use App\Models\PelayananKesehatan;
use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class PelayananKesehatanResource extends Resource
{
    protected static ?string $model = PelayananKesehatan::class;
    protected static ?string $navigationIcon = "heroicon-o-plus-circle";
    protected static ?string $navigationLabel = "Data Pelayanan Kesehatan";
    protected static ?int $navigationSort = 20;

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
            Forms\Components\Section::make("Data Pasien")
                ->description("Cari dan pilih pasien.")
                ->icon("heroicon-o-user")
                ->schema([
                    Forms\Components\Select::make("penduduk_id")
                        ->label("Cari Nama Pasien")
                        ->options(fn () => \App\Models\Penduduk::query()->inApprovedBatch()->orderBy("nama_lengkap")->pluck("nama_lengkap", "id"))
                        ->searchable(["nama_lengkap", "nik"])
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => self::fillFromPenduduk($state, $set)),
                ]),
            Forms\Components\Section::make("Pelayanan")
                ->icon("heroicon-o-plus-circle")
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make("jenis_pelayanan")
                            ->label("Jenis Pelayanan")
                            ->options([
                                'Kunjungan_Rawat_Jalan' => 'Kunjungan Rawat Jalan',
                                'Kunjungan_Rawat_Inap' => 'Kunjungan Rawat Inap',
                                'Kunjungan_Darurat' => 'Kunjungan Darurat',
                                'Pendaftaran_BPJS' => 'Pendaftaran BPJS',
                                'Rujukan' => 'Rujukan',
                                'Pemeriksaan_kehamilan' => 'Pemeriksaan Kehamilan',
                                'Pelayanan_Gizi' => 'Pelayanan Gizi',
                                'Pemeriksaan_Bayi_Balita' => 'Pemeriksaan Bayi/Balita',
                                'Konseling_KB' => 'Konseling KB',
                                'Imunisasi' => 'Imunisasi',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make("tanggal_pelayanan")
                            ->label("Tanggal Pelayanan"),
                        Forms\Components\TextInput::make("nama_pelayanan")
                            ->label("Nama Pelayanan"),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make("diagnosa")
                            ->label("Diagnosa"),
                        Forms\Components\TextInput::make("tindakan")
                            ->label("Tindakan"),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make("tenaga_kesehatan_yang_melayani")
                            ->label("Nakes yang Melayani"),
                        Forms\Components\TextInput::make("hasil_pemeriksaan")
                            ->label("Hasil Pemeriksaan"),
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
                Tables\Columns\TextColumn::make("penduduk.nama_lengkap")->label("Nama Pasien")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("jenis_pelayanan")->label("Jenis Pelayanan")->sortable(),
                Tables\Columns\TextColumn::make("tanggal_pelayanan")->label("Tgl Pelayanan")->date("d/m/Y"),
                Tables\Columns\TextColumn::make("diagnosa")->label("Diagnosa")->limit(30),
                Tables\Columns\TextColumn::make("created_at")->label("Tgl Data")->date("d/m/Y")->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("kecamatan_id")->label("Kecamatan")->options(fn () => \App\Models\Kecamatan::query()->orderBy("nama")->pluck("nama","id")),
                Tables\Filters\SelectFilter::make("jenis_pelayanan")->label("Jenis Pelayanan"),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort("created_at","desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListPelayananKesehatans::route("/"),
            "create" => Pages\CreatePelayananKesehatan::route("/create"),
            "edit" => Pages\EditPelayananKesehatan::route("/{record}/edit"),
        ];
    }
}
