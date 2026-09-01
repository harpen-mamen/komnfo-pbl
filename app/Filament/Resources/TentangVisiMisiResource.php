<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TentangVisiMisiResource\Pages;
use App\Models\TentangVisiMisi;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TentangVisiMisiResource extends Resource
{
    protected static ?string $model = TentangVisiMisi::class;
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Visi & Misi';
    protected static ?int $navigationSort = 75;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tipe')
                ->options(['visi' => 'Visi', 'misi' => 'Misi', 'tujuan' => 'Tujuan', 'sasaran' => 'Sasaran'])
                ->required()->native(false),
            Forms\Components\Textarea::make('konten')->required()->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\TextInput::make('periode')->placeholder('2025-2030')->maxLength(20),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('tipe')->colors([
                    'primary' => 'visi',
                    'success' => 'misi',
                    'warning' => 'tujuan',
                    'info' => 'sasaran',
                ]),
                Tables\Columns\TextColumn::make('konten')->wrap()->limit(80),
                Tables\Columns\TextColumn::make('periode')->badge(),
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('tipe')
            ->filters([Tables\Filters\SelectFilter::make('tipe')->options([
                'visi' => 'Visi', 'misi' => 'Misi', 'tujuan' => 'Tujuan', 'sasaran' => 'Sasaran',
            ])])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTentangVisiMisis::route('/'),
            'create' => Pages\CreateTentangVisiMisi::route('/create'),
            'edit'   => Pages\EditTentangVisiMisi::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
