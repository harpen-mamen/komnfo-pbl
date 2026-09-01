<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FasilitasKesehatanResource\Pages;
use App\Models\FasilitasKesehatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FasilitasKesehatanResource extends Resource
{
    protected static ?string $model = FasilitasKesehatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Fasilitas Kesehatan';
    protected static ?string $pluralModelLabel = 'Fasilitas Kesehatan';
    protected static ?string $modelLabel = 'Fasilitas Kesehatan';
    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationGroup = 'Kesehatan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['kecamatan', 'desa']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canEdit(Model $record): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canDelete(Model $record): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('kode_faskes_label')
                ->label('Kode Faskes')
                ->content(fn (?FasilitasKesehatan $record) => $record?->kode_faskes ?? 'Akan digenerate otomatis'),
            Forms\Components\Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->relationship('kecamatan', 'nama')
                ->searchable()->preload()->required(),
            Forms\Components\Select::make('desa_id')
                ->label('Desa')
                ->relationship('desa', 'nama', fn (Builder $query, ?callable $get) => $query->when($get('kecamatan_id'), fn ($q) => $q->where('kecamatan_id', $get('kecamatan_id'))))
                ->searchable()->preload()->nullable(),
            Forms\Components\TextInput::make('nama')->label('Nama Fasilitas Kesehatan')->required()->maxLength(255),
            Forms\Components\Select::make('jenis')->label('Jenis')
                ->options(['Puskesmas' => 'Puskesmas', 'Pusat Pembantu' => 'Pusat Pembantu (Pustu)', 'Posyandu' => 'Posyandu', 'Klinik' => 'Klinik'])
                ->default('Puskesmas')->required(),
            Forms\Components\Textarea::make('alamat')->label('Alamat')->maxLength(500)->rows(2),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('telepon')->label('Telepon')->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255),
                Forms\Components\YearPicker::make('tahun_berdiri')->label('Tahun Berdiri'),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('kepala_puskes')->label('Nama Kepala/Penanggung Jawab')->maxLength(255),
                Forms\Components\TextInput::make('nip_kepala')->label('NIP Kepala')->maxLength(50),
            ]),
            Forms\Components\Toggle::make('status_operasional')->label('Status Operasional')->default(true),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('koordinat_lat')->label('Latitude')->numeric()->step(0.0000001),
                Forms\Components\TextInput::make('koordinat_lng')->label('Longitude')->numeric()->step(0.0000001),
                Placeholder::make('info')->label('')->content('Format: Decimal Degrees'),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('jumlah_posyandu')->label('Jumlah Posyandu')->numeric()->default(0),
                Forms\Components\TextInput::make('jumlah_pustu')->label('Jumlah Pustu')->numeric()->default(0),
                Forms\Components\TextInput::make('jumlah_kader')->label('Jumlah Kader')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_faskes')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('jenis')->label('Jenis')->colors(['primary']),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->sortable(),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->sortable(),
                Tables\Columns\IconColumn::make('status_operasional')->label('Status')->boolean(),
                Tables\Columns\TextColumn::make('jumlah_posyandu')->label('Posyandu')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('jumlah_kader')->label('Kader')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('kepala_puskes')->label('Kepala')->limit(30),
            ])
            ->defaultSort('nama')
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')->relationship('kecamatan', 'nama')->label('Kecamatan'),
                Tables\Filters\SelectFilter::make('jenis')->options(['Puskesmas' => 'Puskesmas', 'Pusat Pembantu' => 'Pustu', 'Posyandu' => 'Posyandu', 'Klinik' => 'Klinik'])->label('Jenis'),
                Tables\Filters\TernaryFilter::make('status_operasional')->label('Status Operasional'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFasilitasKesehatans::route('/'),
            'create' => Pages\CreateFasilitasKesehatan::route('/create'),
            'view' => Pages\ViewFasilitasKesehatan::route('/{record}'),
            'edit' => Pages\EditFasilitasKesehatan::route('/{record}/edit'),
        ];
    }
}
