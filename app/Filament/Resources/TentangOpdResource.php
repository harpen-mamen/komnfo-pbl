<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TentangOpdResource\Pages;
use App\Models\TentangOpd;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TentangOpdResource extends Resource
{
    protected static ?string $model = TentangOpd::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Dinas / OPD';
    protected static ?string $pluralModelLabel = 'Dinas / OPD';
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Instansi')
                ->schema([
                    Forms\Components\TextInput::make('nama_instansi')->required()->maxLength(200),
                    Forms\Components\TextInput::make('singkatan')->maxLength(50),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true)
                        ->helperText('Akan jadi URL: /tentang-daerah/opd/[slug]'),
                    Forms\Components\Select::make('kategori')
                        ->options([
                            'dinas' => 'Dinas',
                            'badan' => 'Badan',
                            'kantor' => 'Kantor',
                            'sekretariat' => 'Sekretariat',
                            'rsud' => 'RSUD',
                            'lainnya' => 'Lainnya',
                        ])->default('dinas'),
                    Forms\Components\FileUpload::make('logo')
                        ->image()->directory('tentang-daerah/opd/logo')->imageEditor(),
                ])->columns(2),

            Forms\Components\Section::make('Kontak')
                ->schema([
                    Forms\Components\Textarea::make('alamat')->rows(2)->columnSpanFull(),
                    Forms\Components\TextInput::make('telepon')->maxLength(20),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('website')->url()->maxLength(255),
                ])->columns(3),

            Forms\Components\Section::make('Profil & Tupoksi')
                ->schema([
                    Forms\Components\RichEditor::make('profil_singkat')->label('Profil Singkat'),
                    Forms\Components\RichEditor::make('tupoksi')->label('Tugas Pokok & Fungsi'),
                ]),

            Forms\Components\Section::make('Kepala Instansi')
                ->schema([
                    Forms\Components\Select::make('kepala_instansi_id')
                        ->relationship('kepala', 'nama_lengkap')
                        ->searchable()
                        ->preload()
                        ->label('Kepala Saat Ini')
                        ->helperText('Pilih dari Pejabat Profile yang aktif.'),
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('tampilkan')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->square()->size(40),
                Tables\Columns\TextColumn::make('nama_instansi')->searchable()->wrap()->label('Nama'),
                Tables\Columns\TextColumn::make('singkatan')->badge(),
                Tables\Columns\BadgeColumn::make('kategori'),
                Tables\Columns\TextColumn::make('kepala.nama_lengkap')->label('Kepala')->default('-'),
                Tables\Columns\IconColumn::make('tampilkan')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->filters([Tables\Filters\SelectFilter::make('kategori')->options([
                'dinas' => 'Dinas', 'badan' => 'Badan', 'kantor' => 'Kantor',
                'sekretariat' => 'Sekretariat', 'rsud' => 'RSUD', 'lainnya' => 'Lainnya',
            ])])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTentangOpds::route('/'),
            'create' => Pages\CreateTentangOpd::route('/create'),
            'edit'   => Pages\EditTentangOpd::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
