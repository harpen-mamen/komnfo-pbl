<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\OpdResource\Pages\ListOpds;
use App\Filament\Resources\OpdResource\Pages\CreateOpd;
use App\Filament\Resources\OpdResource\Pages\ViewOpd;
use App\Filament\Resources\OpdResource\Pages\EditOpd;
use App\Filament\Resources\OpdResource\Pages;
use App\Models\Opd;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class OpdResource extends Resource
{
    protected static ?string $model = Opd::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Master OPD';

    protected static ?string $pluralModelLabel = 'Organisasi Perangkat Daerah';

    protected static ?string $modelLabel = 'OPD';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKominfoKabupaten();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKominfoKabupaten();
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
        return $schema
            ->components([
                Section::make('Informasi OPD')
                    ->description('Data dasar Organisasi Perangkat Daerah')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kode')
                                    ->label('Kode OPD')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(Opd::class, 'kode', ignoreRecord: true),
                                TextInput::make('nama')
                                    ->label('Nama OPD')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Select::make('kategori')
                            ->label('Kategori OPD')
                            ->options(Opd::kategoriOptions())
                            ->required()
                            ->reactive(),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Struktur')
                    ->description('Hubungan hierarki dengan OPD lain')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Select::make('parent_id')
                            ->label('OPD Induk')
                            ->options(fn () => Opd::whereNull('parent_id')->aktif()->pluck('nama', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),

                Section::make('Informasi Kontak')
                    ->description('Data kontak OPD')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->maxLength(500),
                                TextInput::make('telepon')
                                    ->label('Telepon')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Kepala Dinas')
                    ->description('Data kepala daerah')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kepala_dinas')
                                    ->label('Nama Kepala Dinas')
                                    ->maxLength(255),
                                TextInput::make('nip_kepala_dinas')
                                    ->label('NIP Kepala Dinas')
                                    ->maxLength(50),
                            ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('aktif')
                            ->label('OPD Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama')
                    ->label('Nama OPD')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => Opd::kategoriLabels($state))
                    ->colors([
                        'primary' => Opd::KATEGORI_KESEHATAN,
                        'success' => Opd::KATEGORI_PENDIDIKAN,
                        'warning' => Opd::KATEGORI_PU,
                        'danger' => Opd::KATEGORI_SOSIAL,
                        'info' => Opd::KATEGORI_PERDAGANGAN,
                        'gray' => Opd::KATEGORI_KEPENDUDUKAN,
                        'secondary' => Opd::KATEGORI_LINGKUNGAN,
                    ])
                    ->sortable(),
                TextColumn::make('parent.nama')
                    ->label('Induk')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('kepala_dinas')
                    ->label('Kepala Dinas')
                    ->sortable()
                    ->default('-'),
                IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('User')
                    ->getStateUsing(fn ($record) => $record->users()->count())
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Opd::kategoriOptions()),
                SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpds::route('/'),
            'create' => CreateOpd::route('/create'),
            'view' => ViewOpd::route('/{record}'),
            'edit' => EditOpd::route('/{record}/edit'),
        ];
    }
}
