<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasusPenyakitResource\Pages;
use App\Models\KasusPenyakit;
use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class KasusPenyakitResource extends Resource
{
    protected static ?string $model = KasusPenyakit::class;
    protected static ?string $navigationIcon = "heroicon-o-beaker";
    protected static ?string $navigationLabel = "Data Kasus Penyakit";
    protected static ?int $navigationSort = 21;

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
            Forms\Components\Section::make("Pilih Pasien")
                ->description("Cari dan pilih pasien.")
                ->icon("heroicon-o-user")
                ->schema([
                    Forms\Components\Select::make("penduduk_id")
                        ->label("Cari Nama Pasien")
                        ->options(fn () => \App\Models\Penduduk::query()->inApprovedBatch()->orderBy("nama_lengkap")->pluck("nama_lengkap", "id"))
                        ->searchable(["nama_lengkap", "nik"])
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => self::fillFromPenduduk($state, $set))
                        ->required(),
                ]),
            Forms\Components\Section::make("Data Penyakit")
                ->icon("heroicon-o-beaker")
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make("jenis_penyakit")
                            ->label("Jenis Penyakit")
                            ->options([
                                'Diabetes' => 'Diabetes',
                                'Hipertensi' => 'Hipertensi',
                                'Jantung' => 'Jantung',
                                'Stroke' => 'Stroke',
                                'TBC' => 'TBC',
                                'Kanker' => 'Kanker',
                                'ISPA' => 'ISPA',
                                'Diare' => 'Diare',
                                'DBD' => 'DBD',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make("tanggal_diagnosis")
                            ->label("Tanggal Diagnosis"),
                        Forms\Components\Select::make("status_kasus")
                            ->label("Status")
                            ->options([
                                'aktif' => 'Aktif',
                                'sembuh' => 'Sembuh',
                                'meninggal' => 'Meninggal',
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
                Tables\Columns\TextColumn::make("penduduk.nama_lengkap")->label("Nama Pasien")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("jenis_penyakit")->label("Penyakit")->sortable(),
                Tables\Columns\TextColumn::make("tanggal_diagnosis")->label("Tgl Diagnosis")->date("d/m/Y"),
                Tables\Columns\BadgeColumn::make("status_kasus")
                    ->label("Status")
                    ->colors(["success" => "sembuh", "warning" => "aktif", "danger" => "meninggal"]),
                Tables\Columns\TextColumn::make("created_at")->label("Tgl Data")->date("d/m/Y")->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("kecamatan_id")->label("Kecamatan")->options(fn () => \App\Models\Kecamatan::query()->orderBy("nama")->pluck("nama","id")),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort("created_at","desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListKasusPenyakits::route("/"),
            "create" => Pages\CreateKasusPenyakit::route("/create"),
            "edit" => Pages\EditKasusPenyakit::route("/{record}/edit"),
        ];
    }
}
