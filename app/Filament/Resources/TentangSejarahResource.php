<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\TentangSejarahResource\Pages\ListTentangSejarahs;
use App\Filament\Resources\TentangSejarahResource\Pages\CreateTentangSejarah;
use App\Filament\Resources\TentangSejarahResource\Pages\EditTentangSejarah;
use App\Filament\Resources\TentangSejarahResource\Pages;
use App\Models\TentangSejarah;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

abstract class TentangSejarahResource extends Resource
{
    protected static ?string $model = TentangSejarah::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | \UnitEnum | null $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Timeline Sejarah';
    protected static ?string $pluralModelLabel = 'Timeline Sejarah';
    protected static ?int $navigationSort = 60;

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
            TextInput::make('tahun')->required()->maxLength(10)->placeholder('1945'),
            TextInput::make('judul_peristiwa')->required()->maxLength(200),
            Textarea::make('deskripsi')->required()->rows(4),
            TextInput::make('icon')->maxLength(50)->placeholder('heroicon-o-flag'),
            FileUpload::make('gambar')->image()->directory('tentang-daerah/sejarah'),
            TextInput::make('urutan')->numeric()->default(0),
            Toggle::make('tampilkan')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahun')->sortable()->badge()->color('primary'),
                TextColumn::make('judul_peristiwa')->label('Peristiwa')->searchable()->wrap(),
                TextColumn::make('deskripsi')->limit(50),
                IconColumn::make('tampilkan')->boolean(),
                TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTentangSejarahs::route('/'),
            'create' => CreateTentangSejarah::route('/create'),
            'edit'   => EditTentangSejarah::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
