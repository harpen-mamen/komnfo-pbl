<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataSiswaResource\Pages;
use App\Models\DataSiswa;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class DataSiswaResource extends Resource
{
    protected static ?string $model = DataSiswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $pluralModelLabel = 'Data Siswa';
    protected static ?string $modelLabel = 'Data Siswa';
    protected static ?int $navigationSort = 27;
    protected static ?string $navigationGroup = 'Pendidikan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['sekolah', 'submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminSekolahKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('sekolah_id')->label('Sekolah')
                    ->relationship('sekolah', 'nama_sekolah')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('tahun_ajaran')->label('Tahun Ajaran')->placeholder('2025/2026')->required()->maxLength(9),
                Forms\Components\Select::make('semester')->label('Semester')
                    ->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->default('ganjil')->required(),
            ]),
            Forms\Components\Fieldset::make('Jumlah Siswa')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('jumlah_siswa_total')->label('Total Siswa')->numeric()->default(0),
                    Forms\Components\TextInput::make('laki_laki')->label('Laki-laki')->numeric()->default(0),
                    Forms\Components\TextInput::make('perempuan')->label('Perempuan')->numeric()->default(0),
                ]),
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\TextInput::make('siswa_diterima_baru')->label('Diterima Baru')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_lulus')->label('Lulus')->numeric()->default(0),
                    Forms\Components\TextInput::make('putus_sekolah')->label('Putus Sekolah')->numeric()->default(0),
                    Forms\Components\TextInput::make('jumlah_rombel')->label('Jumlah Rombel')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Fieldset::make('Mutasi & Kenaikan')->schema([
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\TextInput::make('naik_kelas')->label('Naik Kelas')->numeric()->default(0),
                    Forms\Components\TextInput::make('tidak_naik')->label('Tidak Naik')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_mutasi_keluar')->label('Mutasi Keluar')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_mutasi_datang')->label('Mutasi Datang')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Fieldset::make('Bantuan Siswa')->schema([
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\TextInput::make('siswa_ABK')->label('ABK ( Anak Berkebutuhan Khusus)')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_BOS')->label('BOS')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_PIP')->label('PIP')->numeric()->default(0),
                    Forms\Components\TextInput::make('siswa_KIP')->label('KIP')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Textarea::make('catatan')->label('Catatan')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sekolah.nama_sekolah')->label('Sekolah')->sortable(),
                Tables\Columns\TextColumn::make('tahun_ajaran')->label('Th. Ajaran')->sortable(),
                Tables\Columns\BadgeColumn::make('semester')->label('Smr')->colors(['primary' => 'ganjil', 'success' => 'genap']),
                Tables\Columns\TextColumn::make('jumlah_siswa_total')->label('Total')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('laki_laki')->label('L')->numeric(),
                Tables\Columns\TextColumn::make('perempuan')->label('P')->numeric(),
                Tables\Columns\TextColumn::make('jumlah_rombel')->label('Rombel')->numeric(),
                Tables\Columns\TextColumn::make('siswa_BOS')->label('BOS')->numeric(),
                Tables\Columns\TextColumn::make('siswa_KIP')->label('KIP')->numeric(),
            ])
            ->defaultSort('tahun_ajaran', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')->relationship('sekolah', 'nama_sekolah')->label('Sekolah'),
                Tables\Filters\SelectFilter::make('semester')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->label('Semester'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataSiswas::route('/'),
            'create' => Pages\CreateDataSiswa::route('/create'),
            'view' => Pages\ViewDataSiswa::route('/{record}'),
            'edit' => Pages\EditDataSiswa::route('/{record}/edit'),
        ];
    }
}
