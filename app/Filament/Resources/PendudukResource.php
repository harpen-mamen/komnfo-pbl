<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendudukResource\Pages;
use App\Models\Keluarga;
use App\Models\Kecamatan;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PendudukResource extends Resource
{
    protected static ?string $model = Penduduk::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Penduduk';
    protected static ?string $pluralModelLabel = 'Data Penduduk';
    protected static ?string $modelLabel = 'Penduduk';
    protected static ?string $recordTitleAttribute = 'nama_lengkap';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['keluarga.rumah', 'latestSubmissionBatchItem.submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canCreate(): bool
    {
        return AdminKecamatan::isAdminKecamatan();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canEdit(Model $record): bool
    {
        return AdminKecamatan::isAdminKecamatan();
    }

    public static function canDelete(Model $record): bool
    {
        return AdminKecamatan::isAdminKecamatan();
    }

    public static function canDeleteAny(): bool
    {
        return AdminKecamatan::isAdminKecamatan();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('kecamatan_readonly')
                ->label('Kecamatan')
                ->content(fn (): string => AdminKecamatan::kecamatan()?->nama ?? 'Admin Kominfo Kabupaten'),
            Forms\Components\Select::make('desa_id')
                ->label('STEP 1 - Desa')
                ->dehydrated(false)
                ->options(fn () => AdminKecamatan::desaOptions())
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateHydrated(function ($component, $state, ?Penduduk $record): void {
                    if ($record?->keluarga?->rumah?->desa_id) {
                        $component->state($record->keluarga->rumah->desa_id);
                    }
                })
                ->afterStateUpdated(function (callable $set): void {
                    $set('rumah_id', null);
                    $set('keluarga_id', null);
                }),
            Forms\Components\Select::make('rumah_id')
                ->label('STEP 2 - Rumah')
                ->dehydrated(false)
                ->options(fn (callable $get) => Rumah::query()
                    ->visibleToCurrentUser()
                    ->when($get('desa_id'), fn (Builder $query, $desaId) => $query->where('desa_id', $desaId))
                    ->orderBy('kode_rumah')
                    ->pluck('kode_rumah', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateHydrated(function ($component, $state, ?Penduduk $record): void {
                    if ($record?->keluarga?->rumah_id) {
                        $component->state($record->keluarga->rumah_id);
                    }
                })
                ->afterStateUpdated(fn (callable $set) => $set('keluarga_id', null)),
            Forms\Components\Select::make('keluarga_id')
                ->label('STEP 3 - KK')
                ->options(function (callable $get) {
                    return Keluarga::query()
                        ->visibleToCurrentUser()
                        ->when($get('rumah_id'), fn (Builder $query, $rumahId) => $query->where('rumah_id', $rumahId))
                        ->orderBy('nomor_kk')
                        ->pluck('nomor_kk', 'id');
                })
                ->searchable()
                ->required(),
            Forms\Components\Section::make('STEP 4 - Input Penduduk')->schema([
                Forms\Components\TextInput::make('nik')->required()->unique(ignoreRecord: true)->maxLength(32),
                Forms\Components\TextInput::make('nama_lengkap')->label('Nama Lengkap')->required()->maxLength(255),
                Forms\Components\Select::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN)->required(),
                Forms\Components\TextInput::make('tempat_lahir')->required()->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_lahir')->required(),
                Forms\Components\Select::make('agama')->options(Penduduk::AGAMA)->required(),
                Forms\Components\Select::make('status_perkawinan')->options(Penduduk::STATUS_PERKAWINAN)->required(),
                Forms\Components\Select::make('pendidikan')->options(Penduduk::PENDIDIKAN)->required(),
                Forms\Components\TextInput::make('pekerjaan')->maxLength(255),
                Forms\Components\TextInput::make('penghasilan')->numeric()->minValue(0),
                Forms\Components\Select::make('status_dalam_keluarga')->options(Penduduk::STATUS_DALAM_KELUARGA)->required(),
                Forms\Components\TextInput::make('nomor_hp')->tel()->maxLength(32),
                Forms\Components\Select::make('status_penduduk')->options(Penduduk::STATUS_PENDUDUK)->required(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')->label('Nama')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                Tables\Columns\BadgeColumn::make('jenis_kelamin')->label('JK'),
                Tables\Columns\TextColumn::make('keluarga.nomor_kk')->label('Nomor KK')->searchable(),
                Tables\Columns\TextColumn::make('keluarga.nama_kepala_keluarga')->label('Kepala Keluarga')->searchable(),
                Tables\Columns\TextColumn::make('keluarga.rumah.desa.nama')->label('Desa')->searchable(),
                Tables\Columns\TextColumn::make('keluarga.rumah.kode_rumah')->label('Kode Rumah')->searchable(),
                Tables\Columns\TextColumn::make('agama')->sortable(),
                Tables\Columns\TextColumn::make('pendidikan')->sortable(),
                Tables\Columns\TextColumn::make('umur')->label('Umur'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $kecamatanId) => $query
                            ->whereHas('keluarga.rumah.desa', fn (Builder $desa) => $desa->where('kecamatan_id', $kecamatanId)))),
                Tables\Filters\SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->options(fn () => AdminKecamatan::desaOptions())
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $desaId) => $query
                            ->whereHas('keluarga.rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId))
                        )),
                Tables\Filters\SelectFilter::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN),
                Tables\Filters\SelectFilter::make('agama')->options(Penduduk::AGAMA),
                Tables\Filters\SelectFilter::make('pendidikan')->options(Penduduk::PENDIDIKAN),
                Tables\Filters\SelectFilter::make('batch_status')
                    ->label('Status Batch')
                    ->options(fn () => collect([
                        SubmissionBatch::STATUS_DRAFT => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_DRAFT),
                        SubmissionBatch::STATUS_PENDING => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                        SubmissionBatch::STATUS_IN_REVIEW => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_IN_REVIEW),
                        SubmissionBatch::STATUS_REVISION => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REVISION),
                        SubmissionBatch::STATUS_APPROVED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_APPROVED),
                        SubmissionBatch::STATUS_REJECTED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REJECTED),
                    ])->all())
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, string $value) => $query
                            ->withLatestSubmissionBatchStatus($value))),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-download')->url(route('laporan.penduduk.pdf'))->openUrlInNewTab(),
                Tables\Actions\Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table')->url(route('laporan.penduduk.excel'))->openUrlInNewTab(),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nik',
            'nama_lengkap',
            'keluarga.nomor_kk',
            'keluarga.nama_kepala_keluarga',
            'keluarga.rumah.kode_rumah',
            'keluarga.rumah.alamat_lengkap',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->visibleToCurrentUser()->with('keluarga.rumah');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Penduduk' => $record->nama_lengkap,
            'NIK' => $record->nik,
            'KK' => $record->keluarga?->nomor_kk,
            'Kepala Keluarga' => $record->keluarga?->nama_kepala_keluarga,
            'Rumah' => $record->keluarga?->rumah?->kode_rumah,
            'Alamat' => $record->keluarga?->rumah?->alamat_lengkap,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenduduks::route('/'),
            'create' => Pages\CreatePenduduk::route('/create'),
            'view' => Pages\ViewPenduduk::route('/{record}'),
            'edit' => Pages\EditPenduduk::route('/{record}/edit'),
        ];
    }
}
