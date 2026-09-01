<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IbuHamilResource\Pages;
use App\Models\IbuHamil;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class IbuHamilResource extends Resource
{
    protected static ?string $model = IbuHamil::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Data Ibu Hamil';
    protected static ?int $navigationSort = 17;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleToCurrentUser()
            ->with(['penduduk', 'desa', 'kecamatan', 'createdBy']);
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
            Forms\Components\Select::make('penduduk_id')
                ->label('Cari Nama Penduduk')
                ->options(fn () => \App\Models\Penduduk::query()
                    ->inApprovedBatch()
                    ->where('jenis_kelamin', 'Perempuan')
                    ->orderBy('nama_lengkap')
                    ->pluck('nama_lengkap', 'id'))
                ->searchable(['nama_lengkap', 'nik'])
                ->preload()
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) => self::fillFromPenduduk($state, $set))
                ->required(),
            Forms\Components\Section::make('Data Kehamilan')
                ->icon('heroicon-o-heart')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('status_kehamilan')
                            ->label('Status Kehamilan')
                            ->options([
                                'Hamil' => 'Hamil',
                                'Melahirkan' => 'Melahirkan',
                                'Abortus' => 'Abortus',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->default('Hamil')
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_hpl')->label('Tanggal HPL'),
                        Forms\Components\TextInput::make('usia_kehamilan_minggu')
                            ->label('Usia Kehamilan (minggu)')
                            ->numeric(),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('tanggal_periksa_terakhir')
                            ->label('Tanggal Periksa Terakhir'),
                        Forms\Components\TextInput::make('frekuensi_periksa')
                            ->label('Frekuensi Periksa'),
                    ]),
                ]),
            Forms\Components\Section::make('Risiko Kehamilan')
                ->icon('heroicon-o-exclamation')
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('kategori_resiko')
                            ->label('Kategori Risiko')
                            ->options([
                                'Resiko Rendah' => 'Resiko Rendah',
                                'Resiko Tinggi' => 'Resiko Tinggi',
                            ])
                            ->default('Resiko Rendah'),
                        Forms\Components\TextInput::make('kondisi_resiko')->label('Kondisi Risiko'),
                    ]),
                ]),
            Forms\Components\Section::make('Pelayanan')
                ->icon('heroicon-o-check-circle')
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Checkbox::make('mendapat_fe')->label('Mendapat Tablet Fe'),
                        Forms\Components\Checkbox::make('mendapat_imunisasi_tt')->label('Mendapat Imunisasi TT'),
                        Forms\Components\Checkbox::make('mendapat_konseling')->label('Mendapat Konseling'),
                    ]),
                ]),
            Forms\Components\Textarea::make('catatan')->label('Catatan')->rows(3),
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
                    ->colors(['success' => 'Hamil', 'info' => 'Melahirkan', 'warning' => 'Lainnya']),
                Tables\Columns\TextColumn::make('tanggal_hpl')->label('HPL')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('usia_kehamilan_minggu')->label('Usia (minggu)'),
                Tables\Columns\BadgeColumn::make('kategori_resiko')->label('Risiko'),
                Tables\Columns\IconColumn::make('mendapat_fe')->label('Fe')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Tgl Data')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => \App\Models\Kecamatan::query()->orderBy('nama')->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('status_kehamilan')->label('Status'),
                Tables\Filters\SelectFilter::make('kategori_resiko')->label('Risiko'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIbuHamils::route('/'),
            'create' => Pages\CreateIbuHamil::route('/create'),
            'edit' => Pages\EditIbuHamil::route('/{record}/edit'),
        ];
    }
}
