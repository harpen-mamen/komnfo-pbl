<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use App\Filament\Resources\AuditTrailResource\Pages\ListAuditTrails;
use App\Filament\Resources\AuditTrailResource\Pages\ViewAuditTrail;
use App\Filament\Resources\AuditTrailResource\Pages;
use App\Models\AuditTrail;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Audit';
    protected static ?string $pluralModelLabel = 'Audit';
    protected static ?string $modelLabel = 'Audit';
    protected static ?int $navigationSort = 13;

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isSuperAdmin();
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('entity_type')->label('Entitas')->disabled(),
            TextInput::make('entity_id')->label('ID Entitas')->disabled(),
            TextInput::make('field')->label('Field')->disabled(),
            TextInput::make('aksi')->label('Aksi')->disabled(),
            TextInput::make('pengguna')->label('Pengguna')->disabled(),
            TextInput::make('role')->label('Role')->disabled(),
            Textarea::make('nilai_lama')->label('Nilai Lama')->disabled()->columnSpanFull(),
            Textarea::make('nilai_baru')->label('Nilai Baru')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('entity_type')->label('Entitas')->searchable()->limit(32),
                TextColumn::make('field')->label('Field')->searchable(),
                TextColumn::make('aksi')->label('Aksi')->searchable(),
                TextColumn::make('pengguna')->label('Pengguna')->searchable(),
                TextColumn::make('role')->label('Role'),
                TextColumn::make('ip')->label('IP'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditTrails::route('/'),
            'view' => ViewAuditTrail::route('/{record}'),
        ];
    }
}
