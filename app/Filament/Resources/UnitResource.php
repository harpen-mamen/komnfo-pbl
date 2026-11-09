<?php

namespace App\Filament\Resources;

use App\Policies\UnitPolicy;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\UnitResource\Pages\ListUnit;
use App\Filament\Resources\UnitResource\Pages\ViewUnit;
use App\Filament\Resources\UnitResource\Pages\EditUnit;
use App\Models\Unit;
use App\Filament\Resources\UnitResource\Pages;
use Filament\Resources\Resource;

abstract class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Unit';
    protected static string | \UnitEnum | null $navigationGroup = 'Administrasi';
    protected static ?string $policy = UnitPolicy::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Residual resource from the removed 4-domain schema; the legacy app
        // does not have the Unit model or this role API.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tipe')
                ->options(['kabupaten' => 'Kabupaten','kecamatan' => 'Kecamatan','desa' => 'Desa','opd' => 'OPD'])
                ->required(),
            Select::make('parent_id')->label('Parent Unit')
                ->relationship('parent', 'nama')
                ->searchable()
                ->preload(),
            TextInput::make('kode')->required(),
            TextInput::make('kode_bps')->label('Kode BPS'),
            TextInput::make('nama')->required(),
            TextInput::make('kategori'),
            TextInput::make('peran_sdi')->label('Peran SDI'),
            Toggle::make('aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            BadgeColumn::make('tipe')
                ->colors([
                    'primary' => 'kabupaten',
                    'info' => 'kecamatan',
                    'success' => 'desa',
                    'warning' => 'opd',
                ]),
            TextColumn::make('kode')->searchable(),
            TextColumn::make('kode_bps')->label('Kode BPS')->placeholder('-'),
            TextColumn::make('nama')->searchable(),
            TextColumn::make('parent.nama')->label('Parent')->placeholder('-'),
            IconColumn::make('aktif')->boolean()->label('Aktif'),
        ])
        ->defaultSort('tipe')
        ->filters([
            SelectFilter::make('tipe')
                ->options(['kabupaten' => 'Kabupaten','kecamatan' => 'Kecamatan','desa' => 'Desa','opd' => 'OPD']),
            TernaryFilter::make('aktif'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnit::route('/'),
            'view' => ViewUnit::route('/{record}'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
