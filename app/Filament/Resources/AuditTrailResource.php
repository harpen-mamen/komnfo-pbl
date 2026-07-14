<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditTrailResource\Pages;
use App\Models\AuditTrail;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static ?string $navigationLabel = 'Audit';
    protected static ?string $pluralModelLabel = 'Audit';
    protected static ?string $modelLabel = 'Audit';
    protected static ?int $navigationSort = 13;

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('entity_type')->label('Entitas')->disabled(),
            Forms\Components\TextInput::make('entity_id')->label('ID Entitas')->disabled(),
            Forms\Components\TextInput::make('field')->label('Field')->disabled(),
            Forms\Components\TextInput::make('aksi')->label('Aksi')->disabled(),
            Forms\Components\TextInput::make('pengguna')->label('Pengguna')->disabled(),
            Forms\Components\TextInput::make('role')->label('Role')->disabled(),
            Forms\Components\Textarea::make('nilai_lama')->label('Nilai Lama')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('nilai_baru')->label('Nilai Baru')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('entity_type')->label('Entitas')->searchable()->limit(32),
                Tables\Columns\TextColumn::make('field')->label('Field')->searchable(),
                Tables\Columns\TextColumn::make('aksi')->label('Aksi')->searchable(),
                Tables\Columns\TextColumn::make('pengguna')->label('Pengguna')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Role'),
                Tables\Columns\TextColumn::make('ip')->label('IP'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditTrails::route('/'),
            'view' => Pages\ViewAuditTrail::route('/{record}'),
        ];
    }
}
