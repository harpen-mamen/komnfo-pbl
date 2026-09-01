<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPrasaranaResource\Pages;
use App\Models\DataPrasarana;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class DataPrasaranaResource extends Resource
{
    protected static ?string $model = DataPrasarana::class;
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    protected static ?string $navigationLabel = 'Data Prasarana';
    protected static ?string $pluralModelLabel = 'Data Prasarana & Sarana';
    protected static ?string $modelLabel = 'Data Prasarana';
    protected static ?int $navigationSort = 28;
    protected static ?string $navigationGroup = 'Pendidikan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['sekolah', 'submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('sekolah_id')->label('Sekolah')
                    ->relationship('sekolah', 'nama_sekolah')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('tahun_ajaran')->label('Tahun Ajaran')->placeholder('2025/2026')->required()->maxLength(9),
                Forms\Components\Select::make('semester')->label('Semester')
                    ->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->default('ganjil')->required(),
            ]),
            Forms\Components\Fieldset::make('Ruang Kelas')->schema([
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\TextInput::make('jumlah_ruang_kelas')->label('Total Ruang Kelas')->numeric()->default(0),
                    Forms\Components\TextInput::make('kondisi_ruang_baik')->label('Baik')->numeric()->default(0),
                    Forms\Components\TextInput::make('rusak_ringan')->label('Rusak Ringan')->numeric()->default(0),
                    Forms\Components\TextInput::make('rusak_berat')->label('Rusak Berat')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Fieldset::make('Sarana Prasarana')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Toggle::make('laboratorium_ipa')->label('Laboratorium IPA'),
                    Forms\Components\Toggle::make('laboratorium_komputer')->label('Laboratorium Komputer'),
                    Forms\Components\Toggle::make('laboratorium_bahasa')->label('Laboratorium Bahasa'),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Toggle::make('perpustakaan')->label('Perpustakaan'),
                    Forms\Components\Toggle::make('uks')->label('UKS'),
                    Forms\Components\Toggle::make('musholla')->label('Musholla/Masjid'),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Toggle::make('sarana_olahraga')->label('Sarana Olahraga'),
                    Forms\Components\Toggle::make('sumber_listrik')->label('Sumber Listrik'),
                    Forms\Components\Toggle::make('sumber_internet')->label('Akses Internet'),
                ]),
            ]),
            Forms\Components\Select::make('kondisi_gedung')->label('Kondisi Gedung')
                ->options(['Baik' => 'Baik', 'Rusak Ringan' => 'Rusak Ringan', 'Rusak Berat' => 'Rusak Berat'])
                ->default('Baik'),
            Forms\Components\Textarea::make('catatan')->label('Catatan')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')->label('Sekolah')->sortable(),
                Tables\Columns\TextColumn::make('tahun_ajaran')->label('Th. Ajaran')->sortable(),
                Tables\Columns\BadgeColumn::make('semester')->label('Smr')->colors(['primary' => 'ganjil', 'success' => 'genap']),
                Tables\Columns\TextColumn::make('jumlah_ruang_kelas')->label('Ruang')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('kondisi_ruang_baik')->label('Baik')->numeric(),
                Tables\Columns\TextColumn::make('rusak_ringan')->label('R.Ringan')->numeric(),
                Tables\Columns\TextColumn::make('rusak_berat')->label('R.Berat')->numeric(),
                Tables\Columns\BadgeColumn::make('kondisi_gedung')->label('Gedung')
                    ->colors(['success' => 'Baik', 'warning' => 'Rusak Ringan', 'danger' => 'Rusak Berat']),
                Tables\Columns\IconColumn::make('laboratorium_ipa')->label('Lab IPA')->boolean(),
                Tables\Columns\IconColumn::make('perpustakaan')->label('Perpus')->boolean(),
                Tables\Columns\IconColumn::make('sumber_internet')->label('Internet')->boolean(),
            ])
            ->defaultSort('tahun_ajaran', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')->relationship('sekolah', 'nama_sekolah')->label('Sekolah'),
                Tables\Filters\SelectFilter::make('semester')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->label('Semester'),
                Tables\Filters\SelectFilter::make('kondisi_gedung')->options(['Baik' => 'Baik', 'Rusak Ringan' => 'Rusak Ringan', 'Rusak Berat' => 'Rusak Berat'])->label('Kondisi Gedung'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPrasaranas::route('/'),
            'create' => Pages\CreateDataPrasarana::route('/create'),
            'view' => Pages\ViewDataPrasarana::route('/{record}'),
            'edit' => Pages\EditDataPrasarana::route('/{record}/edit'),
        ];
    }
}
