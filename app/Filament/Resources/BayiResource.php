<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BayiResource\Pages;
use App\Models\Bayi;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class BayiResource extends Resource
{
    protected static ?string $model = Bayi::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Data Bayi & Balita';
    protected static ?int $navigationSort = 18;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleToCurrentUser()
            ->with(['penduduk', 'ibu', 'desa', 'kecamatan', 'createdBy']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel()
            || PanelAccess::isAdminKecamatan()
            || PanelAccess::isOperatorPuskesmas();
    }

    public static function canViewAny(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::isOperatorPuskesmas();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('desa_id'),
            Forms\Components\Hidden::make('kecamatan_id'),
            Forms\Components\Section::make('Pilih Penduduk')
                ->description('Cari dan pilih penduduk perempuan.')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\Select::make('penduduk_id')
                        ->label('Cari Nama')
                        ->options(fn () => \App\Models\Penduduk::query()
                            ->inApprovedBatch()
                            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= 5')
                            ->orderBy('nama_lengkap')
                            ->pluck('nama_lengkap', 'id'))
                        ->searchable(['nama_lengkap', 'nik'])
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => self::fillFromPenduduk($state, $set))
                        ->required(),
                ]),
            Forms\Components\Select::make('ibu_id')
                ->label('Ibu')
                ->options(fn () => \App\Models\IbuHamil::query()
                    ->visibleToCurrentUser()
                    ->with('penduduk')
                    ->get()
                    ->mapWithKeys(fn (\App\Models\IbuHamil $ibu) => [
                        $ibu->id => $ibu->penduduk?->nama_lengkap ?? $ibu->nama ?? 'Ibu #'.$ibu->id,
                    ]))
                ->searchable(),
            Forms\Components\Section::make('Data Bayi')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\DatePicker::make('tanggal_lahir')->label('Tanggal Lahir'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options(['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']),
                        Forms\Components\TextInput::make('nik')->label('NIK')->maxLength(16),
                    ]),
                    Forms\Components\TextInput::make('alamat')->label('Alamat'),
                ]),
            Forms\Components\Section::make('Pertumbuhan')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('berat_badan_lahir')->label('Berat Lahir (gram)')->numeric(),
                        Forms\Components\TextInput::make('panjang_badan_lahir')->label('Panjang Lahir (cm)')->numeric(),
                        Forms\Components\TextInput::make('berat_badan_sekarang')->label('Berat Sekarang (gram)')->numeric(),
                        Forms\Components\TextInput::make('panjang_badan_sekarang')->label('Panjang Sekarang (cm)')->numeric(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('status_gizi')->label('Status Gizi')->options([
                            'Baik' => 'Baik', 'Kurang' => 'Kurang', 'Buruk' => 'Buruk', 'Lebih' => 'Lebih',
                        ])->default('Baik'),
                        Forms\Components\Select::make('kategori_balita')->label('Kategori Balita')->options([
                            'Normal' => 'Normal', 'Stunting' => 'Stunting', 'Wasting' => 'Wasting', 'Underweight' => 'Underweight',
                        ]),
                    ]),
                ]),
            Forms\Components\Section::make('Imunisasi')
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Checkbox::make('imunisasi_hb0')->label('HB0'),
                        Forms\Components\Checkbox::make('imunisasi_bcg')->label('BCG'),
                        Forms\Components\Checkbox::make('imunisasi_dpt_hb1')->label('DPT HB1'),
                        Forms\Components\Checkbox::make('imunisasi_dpt_hb2')->label('DPT HB2'),
                        Forms\Components\Checkbox::make('imunisasi_dpt_hb3')->label('DPT HB3'),
                        Forms\Components\Checkbox::make('imunisasi_polio1')->label('Polio 1'),
                        Forms\Components\Checkbox::make('imunisasi_polio2')->label('Polio 2'),
                        Forms\Components\Checkbox::make('imunisasi_polio3')->label('Polio 3'),
                        Forms\Components\Checkbox::make('imunisasi_polio4')->label('Polio 4'),
                        Forms\Components\Checkbox::make('imunisasi_campak')->label('Campak'),
                        Forms\Components\Checkbox::make('imunisasi_mmr')->label('MMR'),
                    ]),
                ]),
            Forms\Components\Section::make('Vitamin dan Kondisi')
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Checkbox::make('mendapat_vitamin_a')->label('Vitamin A'),
                        Forms\Components\DatePicker::make('tanggal_vitamin_a')->label('Tanggal Vitamin A'),
                        Forms\Components\Checkbox::make('mendapat_taburia')->label('Taburia'),
                        Forms\Components\Checkbox::make('asi_eksklusif')->label('ASI Eksklusif'),
                    ]),
                ]),
            Forms\Components\Section::make('Catatan')
                ->icon('heroicon-o-document-text')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('catatan')->label('Catatan')->rows(2),
                ]),
        ]);
    }

    private static function fillFromPenduduk(?int $pendudukId, callable $set): void
    {
        if (! $pendudukId) {
            return;
        }

        $penduduk = \App\Models\Penduduk::query()
            ->with('keluarga.rumah.desa')
            ->find($pendudukId);

        if (! $penduduk) {
            return;
        }

        $set('desa_id', $penduduk->keluarga?->rumah?->desa_id);
        $set('kecamatan_id', $penduduk->keluarga?->rumah?->desa?->kecamatan_id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('penduduk.nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status_kehamilan')
                    ->label('Status')
                    ->colors(['success' => 'Hamil', 'info' => 'Melahirkan']),
                Tables\Columns\TextColumn::make('tanggal_hpl')->label('HPL')->date('d/m/Y'),
                Tables\Columns\IconColumn::make('mendapat_fe')->label('Fe')->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => \App\Models\Kecamatan::query()
                        ->orderBy('nama')
                        ->pluck('nama', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBayis::route('/'),
            'create' => Pages\CreateBayi::route('/create'),
            'edit' => Pages\EditBayi::route('/{record}/edit'),
        ];
    }
}
