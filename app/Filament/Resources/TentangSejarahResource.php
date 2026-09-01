<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TentangSejarahResource\Pages;
use App\Models\TentangSejarah;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TentangSejarahResource extends Resource
{
    protected static ?string $model = TentangSejarah::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Timeline Sejarah';
    protected static ?string $pluralModelLabel = 'Timeline Sejarah';
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('tahun')->required()->maxLength(10)->placeholder('1945'),
            Forms\Components\TextInput::make('judul_peristiwa')->required()->maxLength(200),
            Forms\Components\Textarea::make('deskripsi')->required()->rows(4),
            Forms\Components\TextInput::make('icon')->maxLength(50)->placeholder('heroicon-o-flag'),
            Forms\Components\FileUpload::make('gambar')->image()->directory('tentang-daerah/sejarah'),
            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('tampilkan')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')->sortable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('judul_peristiwa')->label('Peristiwa')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('deskripsi')->limit(50),
                Tables\Columns\IconColumn::make('tampilkan')->boolean(),
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTentangSejarahs::route('/'),
            'create' => Pages\CreateTentangSejarah::route('/create'),
            'edit'   => Pages\EditTentangSejarah::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
