<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\TentangOpdResource\Pages\ListTentangOpds;
use App\Filament\Resources\TentangOpdResource\Pages\CreateTentangOpd;
use App\Filament\Resources\TentangOpdResource\Pages\EditTentangOpd;
use App\Filament\Resources\TentangOpdResource\Pages;
use App\Models\TentangOpd;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

abstract class TentangOpdResource extends Resource
{
    protected static ?string $model = TentangOpd::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';
    protected static string | \UnitEnum | null $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Dinas / OPD';
    protected static ?string $pluralModelLabel = 'Dinas / OPD';
    protected static ?int $navigationSort = 70;

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
            Section::make('Identitas Instansi')
                ->schema([
                    TextInput::make('nama_instansi')->required()->maxLength(200),
                    TextInput::make('singkatan')->maxLength(50),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true)
                        ->helperText('Akan jadi URL: /tentang-daerah/opd/[slug]'),
                    Select::make('kategori')
                        ->options([
                            'dinas' => 'Dinas',
                            'badan' => 'Badan',
                            'kantor' => 'Kantor',
                            'sekretariat' => 'Sekretariat',
                            'rsud' => 'RSUD',
                            'lainnya' => 'Lainnya',
                        ])->default('dinas'),
                    FileUpload::make('logo')
                        ->image()->directory('tentang-daerah/opd/logo')->imageEditor(),
                ])->columns(2),

            Section::make('Kontak')
                ->schema([
                    Textarea::make('alamat')->rows(2)->columnSpanFull(),
                    TextInput::make('telepon')->maxLength(20),
                    TextInput::make('email')->email(),
                    TextInput::make('website')->url()->maxLength(255),
                ])->columns(3),

            Section::make('Profil & Tupoksi')
                ->schema([
                    RichEditor::make('profil_singkat')->label('Profil Singkat'),
                    RichEditor::make('tupoksi')->label('Tugas Pokok & Fungsi'),
                ]),

            Section::make('Kepala Instansi')
                ->schema([
                    Select::make('kepala_instansi_id')
                        ->relationship('kepala', 'nama_lengkap')
                        ->searchable()
                        ->preload()
                        ->label('Kepala Saat Ini')
                        ->helperText('Pilih dari Pejabat Profile yang aktif.'),
                    TextInput::make('urutan')->numeric()->default(0),
                    Toggle::make('tampilkan')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->square()->size(40),
                TextColumn::make('nama_instansi')->searchable()->wrap()->label('Nama'),
                TextColumn::make('singkatan')->badge(),
                BadgeColumn::make('kategori'),
                TextColumn::make('kepala.nama_lengkap')->label('Kepala')->default('-'),
                IconColumn::make('tampilkan')->boolean()->label('Aktif'),
                TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->filters([SelectFilter::make('kategori')->options([
                'dinas' => 'Dinas', 'badan' => 'Badan', 'kantor' => 'Kantor',
                'sekretariat' => 'Sekretariat', 'rsud' => 'RSUD', 'lainnya' => 'Lainnya',
            ])])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTentangOpds::route('/'),
            'create' => CreateTentangOpd::route('/create'),
            'edit'   => EditTentangOpd::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
