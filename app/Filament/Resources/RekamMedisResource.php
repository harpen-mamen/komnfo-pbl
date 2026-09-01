<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekamMedisResource\Pages;
use App\Models\RekamMedis;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class RekamMedisResource extends Resource
{
    protected static ?string $model = RekamMedis::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekam Medis';
    protected static ?string $pluralModelLabel = 'Rekam Medis';
    protected static ?string $modelLabel = 'Rekam Medis';
    protected static ?int $navigationSort = 22;
    protected static ?string $navigationGroup = 'Kesehatan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['fasilitasKesehatan', 'penduduk', 'tenagaKesehatan']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminPuskes() || PanelAccess::canAccessKabupatenPanel();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('fasilitas_kesehatan_id')
                ->label('Fasilitas Kesehatan')
                ->relationship('fasilitasKesehatan', 'nama')->required(),
            Forms\Components\DatePicker::make('tanggal_kunjungan')->label('Tanggal Kunjungan')->required()->default(now()),
            Forms\Components\Select::make('jenis_pelayanan')->label('Jenis Pelayanan')
                ->options(['Rawat Jalan' => 'Rawat Jalan', 'Rawat Inap' => 'Rawat Inap', 'IGD' => 'IGD', 'Promotif' => 'Promotif', 'Preventif' => 'Preventif'])
                ->default('Rawat Jalan')->required(),
            Forms\Components\TextInput::make('poli')->label('Poli')->maxLength(100),
            Forms\Components\TextInput::make('penduduk_id')
                ->label('NIK Pasien')
                ->helperText('Cari penduduk berdasarkan NIK'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('diagnosa_utama')->label('Diagnosa Utama (ICD-10)')->required()->maxLength(255),
                Forms\Components\TextInput::make('kode_icd10')->label('Kode ICD-10')->maxLength(20),
            ]),
            Forms\Components\Textarea::make('diagnosa_sekunder')->label('Diagnosa Sekunder (JSON array)')->rows(2)->helperText('Format: ["Kode1", "Kode2"]'),
            Forms\Components\Textarea::make('tindakan')->label('Tindakan (ICD-9 JSON)')->rows(2)->helperText('Format: ["Kode1", "Kode2"]'),
            Forms\Components\Textarea::make('obat_diberikan')->label('Obat Diberikan (JSON)')->rows(2)->helperText('Format: ["Nama Obat 1", "Nama Obat 2"]'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('cara_bayar')->label('Cara Bayar')
                    ->options(['BPJS' => 'BPJS', 'Asuransi' => 'Asuransi', 'Umum' => 'Umum', 'Gratis' => 'Gratis'])
                    ->default('BPJS')->required(),
                Forms\Components\Select::make('rujukan')->label('Rujukan')
                    ->options(['Tidak' => 'Tidak', 'RS Kabupaten' => 'RS Kabupaten', 'RS Provinsi' => 'RS Provinsi', 'Pusat Kesehatan Lain' => 'Pusat Kesehatan Lain'])
                    ->default('Tidak'),
            ]),
            Forms\Components\TextInput::make('nomor_rujukan')->label('Nomor Rujukan')->maxLength(50),
            Forms\Components\Textarea::make('catatan')->label('Catatan')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fasilitasKesehatan.nama')->label('Faskes')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kunjungan')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\BadgeColumn::make('jenis_pelayanan')->label('Pelayanan')->colors(['primary']),
                Tables\Columns\TextColumn::make('diagnosa_utama')->label('Diagnosa')->limit(30),
                Tables\Columns\TextColumn::make('kode_icd10')->label('ICD-10')->limit(15),
                Tables\Columns\BadgeColumn::make('cara_bayar')->label('Bayar')->colors(['info' => 'BPJS', 'warning' => 'Umum']),
                Tables\Columns\BadgeColumn::make('rujukan')->label('Rujukan')->colors(['danger' => fn ($state) => $state !== 'Tidak', 'gray' => fn ($state) => $state === 'Tidak']),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->date('d M Y'),
            ])
            ->defaultSort('tanggal_kunjungan', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('fasilitas_kesehatan_id')->relationship('fasilitasKesehatan', 'nama')->label('Faskes'),
                Tables\Filters\SelectFilter::make('jenis_pelayanan')->options(['Rawat Jalan' => 'Rawat Jalan', 'Rawat Inap' => 'Rawat Inap', 'IGD' => 'IGD', 'Promotif' => 'Promotif', 'Preventif' => 'Preventif'])->label('Pelayanan'),
                Tables\Filters\SelectFilter::make('cara_bayar')->options(['BPJS' => 'BPJS', 'Asuransi' => 'Asuransi', 'Umum' => 'Umum', 'Gratis' => 'Gratis'])->label('Cara Bayar'),
                Tables\Filters\SelectFilter::make('rujukan')->options(['Tidak' => 'Tidak', 'RS Kabupaten' => 'RS Kabupaten', 'RS Provinsi' => 'RS Provinsi'])->label('Rujukan'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekamMedis::route('/'),
            'create' => Pages\CreateRekamMedis::route('/create'),
            'view' => Pages\ViewRekamMedis::route('/{record}'),
            'edit' => Pages\EditRekamMedis::route('/{record}/edit'),
        ];
    }
}
