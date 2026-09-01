<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PejabatProfileResource\Pages;
use App\Models\PejabatProfile;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PejabatProfileResource extends Resource
{
    protected static ?string $model = PejabatProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Profil Pejabat';
    protected static ?string $pluralModelLabel = 'Profil Pejabat';
    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tipe & Jabatan')
                ->schema([
                    Forms\Components\Select::make('tipe_pejabat')
                        ->options([
                            'bupati' => 'Bupati',
                            'wakil_bupati' => 'Wakil Bupati',
                            'sekda' => 'Sekretaris Daerah',
                            'asisten' => 'Asisten',
                            'staf_ahli' => 'Staf Ahli',
                            'kepala_dinas' => 'Kepala Dinas',
                            'lainnya' => 'Lainnya',
                        ])->required(),
                    Forms\Components\TextInput::make('jabatan')->required()->maxLength(200),
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Select::make('status')
                        ->options(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'])
                        ->default('aktif')
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Identitas')
                ->schema([
                    Forms\Components\TextInput::make('gelar_depan')->maxLength(50)->placeholder('Dr.'),
                    Forms\Components\TextInput::make('nama_lengkap')->required()->maxLength(200),
                    Forms\Components\TextInput::make('gelar_belakang')->maxLength(50)->placeholder('M.Si'),
                    Forms\Components\TextInput::make('nip')->maxLength(30),
                    ])
                ->columns(2),

            Forms\Components\Section::make('Data Pribadi')
                ->schema([
                    Forms\Components\TextInput::make('tempat_lahir')->maxLength(100),
                    Forms\Components\DatePicker::make('tanggal_lahir'),
                    Forms\Components\Select::make('jenis_kelamin')->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])->native(false),
                    Forms\Components\TextInput::make('agama')->maxLength(20),
                ])->columns(4),

            Forms\Components\Section::make('Foto & Tanda Tangan')
                ->schema([
                    Forms\Components\FileUpload::make('foto')
                        ->image()
                        ->directory('tentang-daerah/pejabat/foto')
                        ->imageEditor()
                        ->helperText('Rasio 3:4 ideal. Maks 5 MB.'),
                    Forms\Components\FileUpload::make('tanda_tangan')
                        ->image()
                        ->directory('tentang-daerah/pejabat/ttd')
                        ->helperText('Untuk section Sambutan.'),
                ])->columns(2),

            Forms\Components\Section::make('Biodata & Riwayat')
                ->schema([
                    Forms\Components\Textarea::make('biodata_singkat')->rows(4)->columnSpanFull(),
                    Forms\Components\KeyValue::make('riwayat_pendidikan')
                        ->keyLabel('Jenjang')
                        ->valueLabel('Institusi (Tahun)')
                        ->helperText('cth: S3 | Universitas Indonesia (2010-2015)'),
                    Forms\Components\KeyValue::make('riwayat_jabatan')
                        ->keyLabel('Jabatan')
                        ->valueLabel('Periode')
                        ->helperText('cth: Wabup Sangihe | 2020-2025'),
                    Forms\Components\KeyValue::make('penghargaan'),
                ]),

            Forms\Components\Section::make('Periode Jabatan')
                ->schema([
                    Forms\Components\DatePicker::make('tanggal_mulai'),
                    Forms\Components\DatePicker::make('tanggal_selesai')->helperText('Kosongkan jika masih aktif.'),
                    Forms\Components\DateTimePicker::make('published_at')->label('Tanggal Publish'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')->square()->size(48),
                Tables\Columns\TextColumn::make('nama_lengkap')->label('Nama')->searchable()->wrap()->formatStateUsing(fn ($r) => $r->nama_lengkap_gelar ?? $r->nama_lengkap),
                Tables\Columns\BadgeColumn::make('tipe_pejabat')->label('Tipe'),
                Tables\Columns\TextColumn::make('jabatan')->searchable()->wrap()->limit(40),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'aktif', 'gray' => 'nonaktif']),
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
            ])
            ->defaultSort('tipe_pejabat')->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPejabatProfiles::route('/'),
            'create' => Pages\CreatePejabatProfile::route('/create'),
            'edit'   => Pages\EditPejabatProfile::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
