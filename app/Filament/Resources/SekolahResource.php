<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SekolahResource\Pages;
use App\Models\Sekolah;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SekolahResource extends Resource
{
    protected static ?string $model = Sekolah::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Sekolah';
    protected static ?string $pluralModelLabel = 'Data Sekolah';
    protected static ?string $modelLabel = 'Sekolah';
    protected static ?string $recordTitleAttribute = 'nama_sekolah';
    protected static ?int $navigationSort = 25;
    protected static ?string $navigationGroup = 'Pendidikan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['kecamatan', 'desa']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canEdit(Model $record): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canDelete(Model $record): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('nama_sekolah')->label('Nama Sekolah')->required()->maxLength(255),
                Forms\Components\TextInput::make('npsn')->label('NPSN')->maxLength(20)->unique(ignorable: fn (?Sekolah $record) => $record),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('kecamatan_id')->label('Kecamatan')
                    ->relationship('kecamatan', 'nama')->searchable()->preload()->required(),
                Forms\Components\Select::make('desa_id')->label('Desa')
                    ->relationship('desa', 'nama', fn (Builder $query, ?callable $get) => $query->when($get('kecamatan_id'), fn ($q) => $q->where('kecamatan_id', $get('kecamatan_id'))))
                    ->searchable()->preload()->nullable(),
                Forms\Components\TextInput::make('nss')->label('NSS')->maxLength(50)->nullable(),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('jenjang')->label('Jenjang')
                    ->options(['SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'SMK' => 'SMK', 'SLB' => 'SLB'])
                    ->required(),
                Forms\Components\Select::make('status')->label('Status')
                    ->options(['Negeri' => 'Negeri', 'Swasta' => 'Swasta'])->default('Negeri')->required(),
            ]),
            Forms\Components\Textarea::make('alamat')->label('Alamat')->maxLength(500)->rows(2),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('telepon')->label('Telepon')->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255),
                Forms\Components\TextInput::make('website')->label('Website')->url()->maxLength(255),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('kepala_sekolah')->label('Nama Kepala Sekolah')->maxLength(255),
                Forms\Components\TextInput::make('nip_kepala')->label('NIP Kepala Sekolah')->maxLength(50),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\YearPicker::make('tahun_berdiri')->label('Tahun Berdiri'),
                Forms\Components\Select::make('akreditasi')->label('Akreditasi')
                    ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'Tidak Terakreditasi' => 'Tidak Terakreditasi', 'Dalam Proses' => 'Dalam Proses']),
                Forms\Components\TextInput::make('kurikulum')->label('Kurikulum')->maxLength(50),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('koordinat_lat')->label('Latitude')->numeric()->step(0.0000001),
                Forms\Components\TextInput::make('koordinat_lng')->label('Longitude')->numeric()->step(0.0000001),
                Placeholder::make('info')->label('')->content('Format: Decimal Degrees'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_sekolah')->label('Nama Sekolah')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('npsn')->label('NPSN')->searchable(),
                Tables\Columns\BadgeColumn::make('jenjang')->label('Jenjang')->colors(['primary']),
                Tables\Columns\BadgeColumn::make('status')->label('Status')
                    ->colors(['success' => 'Negeri', 'warning' => 'Swasta']),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->sortable(),
                Tables\Columns\BadgeColumn::make('akreditasi')->label('Akreditasi')
                    ->colors(['success' => 'A', 'warning' => 'B', 'danger' => 'C', 'gray' => 'Tidak Terakreditasi']),
                Tables\Columns\TextColumn::make('kepala_sekolah')->label('Kepala Sekolah')->limit(25),
            ])
            ->defaultSort('nama_sekolah')
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')->relationship('kecamatan', 'nama')->label('Kecamatan'),
                Tables\Filters\SelectFilter::make('jenjang')->options(['SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'SMK' => 'SMK', 'SLB' => 'SLB'])->label('Jenjang'),
                Tables\Filters\SelectFilter::make('status')->options(['Negeri' => 'Negeri', 'Swasta' => 'Swasta'])->label('Status'),
                Tables\Filters\SelectFilter::make('akreditasi')->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'Tidak Terakreditasi' => 'Tidak Terakreditasi'])->label('Akreditasi'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSekolahs::route('/'),
            'create' => Pages\CreateSekolah::route('/create'),
            'view' => Pages\ViewSekolah::route('/{record}'),
            'edit' => Pages\EditSekolah::route('/{record}/edit'),
        ];
    }
}
