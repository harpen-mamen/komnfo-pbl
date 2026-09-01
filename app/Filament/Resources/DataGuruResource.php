<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataGuruResource\Pages;
use App\Models\DataGuru;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class DataGuruResource extends Resource
{
    protected static ?string $model = DataGuru::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Data Guru';
    protected static ?string $pluralModelLabel = 'Data Guru & Tendik';
    protected static ?string $modelLabel = 'Data Guru';
    protected static ?int $navigationSort = 26;
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
            Forms\Components\Fieldset::make('Jumlah Guru')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('guru_total')->label('Total Guru')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_laki')->label('Laki-laki')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_perempuan')->label('Perempuan')->numeric()->default(0),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('guru_PNS')->label('PNS')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_PPPK')->label('PPPK')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_Honorer')->label('Honorer')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Fieldset::make('Pendidikan & Sertifikasi')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('guru_S1_keatas')->label('S1 ke Atas')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_S2_keatas')->label('S2 ke Atas')->numeric()->default(0),
                    Forms\Components\TextInput::make('guru_sertifikasi')->label('Sertifikasi')->numeric()->default(0),
                ]),
                Forms\Components\TextInput::make('guru_belum_sertifikasi')->label('Belum Sertifikasi')->numeric()->default(0),
            ]),
            Forms\Components\Fieldset::make('Diklat GTK')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('GTK_Diklat_12_jam')->label('Diklat >= 12 Jam')->numeric()->default(0),
                    Forms\Components\TextInput::make('GTK_Diklat_24_jam')->label('Diklat >= 24 Jam')->numeric()->default(0),
                ]),
            ]),
            Forms\Components\Fieldset::make('Tenaga Kependidikan')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('tenaga_administrasi')->label('Administrasi')->numeric()->default(0),
                    Forms\Components\TextInput::make('tenaga_perpustakaan')->label('Perpustakaan')->numeric()->default(0),
                    Forms\Components\TextInput::make('tenaga_lab')->label('Laboran')->numeric()->default(0),
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
                Tables\Columns\BadgeColumn::make('semester')->label('Semester')->colors(['primary' => 'ganjil', 'success' => 'genap']),
                Tables\Columns\TextColumn::make('guru_total')->label('Total')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('guru_PNS')->label('PNS')->numeric(),
                Tables\Columns\TextColumn::make('guru_sertifikasi')->label('Sertifikasi')->numeric(),
                Tables\Columns\TextColumn::make('guru_S1_keatas')->label('S1+')->numeric(),
            ])
            ->defaultSort('tahun_ajaran', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sekolah_id')->relationship('sekolah', 'nama_sekolah')->label('Sekolah'),
                Tables\Filters\SelectFilter::make('semester')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->label('Semester'),
                Tables\Filters\Filter::make('tahun_ajaran')->form([
                    Forms\Components\TextInput::make('tahun')->placeholder('2025/2026'),
                ])->query(function (Builder $query, array $data) {
                    if (!empty($data['tahun'])) {
                        $query->where('tahun_ajaran', $data['tahun']);
                    }
                })->label('Tahun Ajaran'),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataGurus::route('/'),
            'create' => Pages\CreateDataGuru::route('/create'),
            'view' => Pages\ViewDataGuru::route('/{record}'),
            'edit' => Pages\EditDataGuru::route('/{record}/edit'),
        ];
    }
}
