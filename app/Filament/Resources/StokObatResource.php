<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokObatResource\Pages;
use App\Models\StokObat;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class StokObatResource extends Resource
{
    protected static ?string $model = StokObat::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Stok Obat';
    protected static ?string $pluralModelLabel = 'Stok Obat & Alkes';
    protected static ?string $modelLabel = 'Stok Obat';
    protected static ?string $recordTitleAttribute = 'nama_obat';
    protected static ?int $navigationSort = 21;
    protected static ?string $navigationGroup = 'Kesehatan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['fasilitasKesehatan', 'submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('fasilitas_kesehatan_id')
                ->label('Fasilitas Kesehatan')
                ->relationship('fasilitasKesehatan', 'nama')
                ->required(),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('kode_obat')->label('Kode Obat')->required()->maxLength(50),
                Forms\Components\TextInput::make('nama_obat')->label('Nama Obat')->required()->maxLength(255),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('satuan')->label('Satuan')
                    ->options(['Tablet' => 'Tablet', 'Kapsul' => 'Kapsul', 'Sirup' => 'Sirup', 'Vial' => 'Vial', 'Ampul' => 'Ampul', 'Botol' => 'Botol', 'Pcs' => 'Pcs', 'Lembar' => 'Lembar', 'Set' => 'Set'])
                    ->default('Tablet')->required(),
                Forms\Components\Select::make('kategori')->label('Kategori')
                    ->options(['Obat Keras' => 'Obat Keras', 'Obat Bebas' => 'Obat Bebas', 'Obat Bebas Terbatas' => 'Obat Bebas Terbatas', 'BMHP' => 'BMHP', 'Alat Kesehatan' => 'Alat Kesehatan'])
                    ->default('Obat Bebas')->required(),
            ]),
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('stok_awal')->label('Stok Awal')->numeric()->default(0),
                Forms\Components\TextInput::make('terima_dari_gudang')->label('Terima dari Gudang')->numeric()->default(0),
                Forms\Components\TextInput::make('jumlah_dipakai')->label('Jumlah Dipakai')->numeric()->default(0),
                Forms\Components\TextInput::make('stok_minimum')->label('Stok Minimum')->numeric()->default(0),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('stok_akhir')->label('Stok Akhir')->numeric()->default(0)->helperText('Otomatis: Stok Awal + Terima - Dipakai'),
                Forms\Components\TextInput::make('jumlah_rusak')->label('Rusak')->numeric()->default(0),
                Forms\Components\TextInput::make('jumlah_kadaluarsa')->label('Kadaluarsa')->numeric()->default(0),
            ]),
            Forms\Components\DatePicker::make('tanggal_kadaluarsa')->label('Tanggal Kadaluarsa')->nullable(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->maxLength(500)->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_obat')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_obat')->label('Nama Obat')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fasilitasKesehatan.nama')->label('Faskes')->sortable(),
                Tables\Columns\BadgeColumn::make('satuan')->label('Satuan')->colors(['primary']),
                Tables\Columns\BadgeColumn::make('kategori')->label('Kategori')->colors(['info']),
                Tables\Columns\TextColumn::make('stok_akhir')->label('Stok')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('stok_minimum')->label('Min')->numeric(),
                Tables\Columns\IconColumn::make('stok_akhir')
                    ->label('Status Stok')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->stok_akhir > $record->stok_minimum)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('tanggal_kadaluarsa')->label('Kadaluarsa')->date('d M Y')->sortable(),
            ])
            ->defaultSort('nama_obat')
            ->filters([
                Tables\Filters\SelectFilter::make('fasilitas_kesehatan_id')->relationship('fasilitasKesehatan', 'nama')->label('Faskes'),
                Tables\Filters\SelectFilter::make('kategori')->options(['Obat Keras' => 'Obat Keras', 'Obat Bebas' => 'Obat Bebas', 'Obat Bebas Terbatas' => 'Obat Bebas Terbatas', 'BMHP' => 'BMHP', 'Alat Kesehatan' => 'Alat Kesehatan'])->label('Kategori'),
                Tables\Filters\SelectFilter::make('satuan')->options(['Tablet' => 'Tablet', 'Kapsul' => 'Kapsul', 'Sirup' => 'Sirup', 'Vial' => 'Vial', 'Ampul' => 'Ampul', 'Botol' => 'Botol', 'Pcs' => 'Pcs'])->label('Satuan'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStokObats::route('/'),
            'create' => Pages\CreateStokObat::route('/create'),
            'view' => Pages\ViewStokObat::route('/{record}'),
            'edit' => Pages\EditStokObat::route('/{record}/edit'),
        ];
    }
}
