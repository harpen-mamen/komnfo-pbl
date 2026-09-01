<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpdResource\Pages;
use App\Models\Opd;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class OpdResource extends Resource
{
    protected static ?string $model = Opd::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $navigationLabel = 'Data OPD';

    protected static ?string $pluralModelLabel = 'Organisasi Perangkat Daerah';

    protected static ?string $modelLabel = 'OPD';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?int $navigationSort = 1;

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canManageUsers();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canManageUsers();
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi OPD')
                    ->description('Data dasar Organisasi Perangkat Daerah')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('kode')
                                    ->label('Kode OPD')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(Opd::class, 'kode', ignoreRecord: true),
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama OPD')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('kategori')
                            ->label('Kategori OPD')
                            ->options(Opd::kategoriOptions())
                            ->required()
                            ->reactive(),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Struktur')
                    ->description('Hubungan hierarki dengan OPD lain')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('OPD Induk')
                            ->options(fn () => Opd::whereNull('parent_id')->aktif()->pluck('nama', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),

                Forms\Components\Section::make('Informasi Kontak')
                    ->description('Data kontak OPD')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->maxLength(500),
                                Forms\Components\TextInput::make('telepon')
                                    ->label('Telepon')
                                    ->tel()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Section::make('Kepala Dinas')
                    ->description('Data kepala daerah')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('kepala_dinas')
                                    ->label('Nama Kepala Dinas')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nip_kepala_dinas')
                                    ->label('NIP Kepala Dinas')
                                    ->maxLength(50),
                            ]),
                    ]),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('aktif')
                            ->label('OPD Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama OPD')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => Opd::kategoriLabels($state))
                    ->colors([
                        'primary' => Opd::KATEGORI_KESEHATAN,
                        'success' => Opd::KATEGORI_PENDIDIKAN,
                        'warning' => Opd::KATEGORI_PU,
                        'danger' => Opd::KATEGORI_SOSIAL,
                        'info' => Opd::KATEGORI_EKONOMI,
                        'gray' => Opd::KATEGORI_KEPENDUDUKAN,
                        'secondary' => Opd::KATEGORI_LINGKUNGAN,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.nama')
                    ->label('Induk')
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('kepala_dinas')
                    ->label('Kepala Dinas')
                    ->sortable()
                    ->default('-'),
                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('User')
                    ->getStateUsing(fn ($record) => $record->users()->count())
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Opd::kategoriOptions()),
                Tables\Filters\SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Nonaktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpds::route('/'),
            'create' => Pages\CreateOpd::route('/create'),
            'view' => Pages\ViewOpd::route('/{record}'),
            'edit' => Pages\EditOpd::route('/{record}/edit'),
        ];
    }
}
