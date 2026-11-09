<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\TentangVisiMisiResource\Pages\ListTentangVisiMisis;
use App\Filament\Resources\TentangVisiMisiResource\Pages\CreateTentangVisiMisi;
use App\Filament\Resources\TentangVisiMisiResource\Pages\EditTentangVisiMisi;
use App\Filament\Resources\TentangVisiMisiResource\Pages;
use App\Models\TentangVisiMisi;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

abstract class TentangVisiMisiResource extends Resource
{
    protected static ?string $model = TentangVisiMisi::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-eye';
    protected static string | \UnitEnum | null $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Visi & Misi';
    protected static ?int $navigationSort = 75;

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
        return PanelAccess::isSuperAdmin();
    }
    public static function canEdit($record): bool
    {
        return PanelAccess::isSuperAdmin();
    }
    public static function canDelete($record): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tipe')
                ->options(['visi' => 'Visi', 'misi' => 'Misi', 'tujuan' => 'Tujuan', 'sasaran' => 'Sasaran'])
                ->required()->native(false),
            Textarea::make('konten')->required()->rows(3)->columnSpanFull(),
            TextInput::make('urutan')->numeric()->default(0),
            TextInput::make('periode')->placeholder('2025-2030')->maxLength(20),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('tipe')->colors([
                    'primary' => 'visi',
                    'success' => 'misi',
                    'warning' => 'tujuan',
                    'info' => 'sasaran',
                ]),
                TextColumn::make('konten')->wrap()->limit(80),
                TextColumn::make('periode')->badge(),
                TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('tipe')
            ->filters([SelectFilter::make('tipe')->options([
                'visi' => 'Visi', 'misi' => 'Misi', 'tujuan' => 'Tujuan', 'sasaran' => 'Sasaran',
            ])])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTentangVisiMisis::route('/'),
            'create' => CreateTentangVisiMisi::route('/create'),
            'edit'   => EditTentangVisiMisi::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
