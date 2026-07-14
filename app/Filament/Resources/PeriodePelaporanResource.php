<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodePelaporanResource\Pages;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class PeriodePelaporanResource extends Resource
{
    protected static ?string $model = PeriodePelaporan::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Periode Pelaporan';
    protected static ?string $pluralModelLabel = 'Periode Pelaporan';
    protected static ?string $modelLabel = 'Periode Pelaporan';
    protected static ?string $recordTitleAttribute = 'nama_periode';
    protected static ?int $navigationSort = 14;

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOperatorPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOperatorPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function canEdit(Model $record): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_periode')
                ->label('Nama Periode')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('bulan')
                ->label('Bulan')
                ->options(DataPendidikanBulananResource::monthOptions())
                ->required(),
            Forms\Components\TextInput::make('tahun')
                ->label('Tahun')
                ->numeric()
                ->minValue(2020)
                ->maxValue(2100)
                ->default((int) now()->year)
                ->required(),
            Forms\Components\DatePicker::make('tanggal_mulai')
                ->label('Tanggal Mulai')
                ->required(),
            Forms\Components\DatePicker::make('tanggal_selesai')
                ->label('Tanggal Selesai')
                ->required()
                ->afterOrEqual('tanggal_mulai'),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(PeriodePelaporan::STATUS)
                ->default('Draft')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_periode')->label('Nama Periode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('bulan')->label('Bulan')->formatStateUsing(fn ($state) => DataPendidikanBulananResource::monthOptions()[(int) $state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('tahun')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')->label('Mulai')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')->label('Selesai')->date('d M Y')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime('d M Y H:i')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime('d M Y H:i')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(PeriodePelaporan::STATUS),
                Tables\Filters\SelectFilter::make('bulan')->options(DataPendidikanBulananResource::monthOptions()),
                Tables\Filters\SelectFilter::make('tahun')->options(DataPendidikanBulananResource::yearOptions()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('tahun', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodePelaporans::route('/'),
            'create' => Pages\CreatePeriodePelaporan::route('/create'),
            'view' => Pages\ViewPeriodePelaporan::route('/{record}'),
            'edit' => Pages\EditPeriodePelaporan::route('/{record}/edit'),
        ];
    }
}
