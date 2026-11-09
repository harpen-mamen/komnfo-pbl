<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PendudukResource\Pages\ListPenduduks;
use App\Filament\Resources\PendudukResource\Pages\CreatePenduduk;
use App\Filament\Resources\PendudukResource\Pages\ViewPenduduk;
use App\Filament\Resources\PendudukResource\Pages\EditPenduduk;
use App\Filament\Resources\PendudukResource\Pages;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\Wilayah;
use App\Services\Authorization\WewenangService;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class PendudukResource extends Resource
{
    protected static ?string $model = Penduduk::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Penduduk';
    protected static ?string $pluralModelLabel = 'Data Penduduk';
    protected static ?string $modelLabel = 'Penduduk';
    protected static ?string $recordTitleAttribute = 'nama_lengkap';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['keluarga.rumah', 'latestSubmissionBatchItem.submissionBatch']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner();
    }

    public static function canEdit(Model $record): bool
    {
        if (! PanelAccess::isSuperAdmin() && ! PanelAccess::isKependudukanDataOwner()) {
            return false;
        }

        return app(WewenangService::class)
            ->setUser(auth()->user())
            ->canEditRecord(Penduduk::class, $record->id);
    }

    public static function canDelete(Model $record): bool
    {
        if (! PanelAccess::isSuperAdmin() && ! PanelAccess::isKependudukanDataOwner()) {
            return false;
        }

        return app(WewenangService::class)
            ->setUser(auth()->user())
            ->canDeleteRecord(Penduduk::class, $record->id);
    }

    public static function canDeleteAny(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('kecamatan_readonly')
                ->label('Kecamatan')
                ->content(fn (): string => AdminKecamatan::kecamatan()?->nama ?? 'Admin Kominfo Kabupaten'),
            Select::make('desa_id')
                ->label('STEP 1 - Desa')
                ->dehydrated(false)
                ->options(fn () => AdminKecamatan::desaOptions())
                ->searchable()
                ->required()
                ->reactive()
                ->disabled(fn () => AdminKecamatan::isAdminDesa())
                ->afterStateHydrated(function ($component, $state, ?Penduduk $record): void {
                    if ($record?->keluarga?->rumah?->desa_id) {
                        $component->state($record->keluarga->rumah->desa_id);
                    }
                })
                ->afterStateUpdated(function (callable $set): void {
                    $set('rumah_id', null);
                    $set('keluarga_id', null);
                }),
            Select::make('rumah_id')
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
            Select::make('keluarga_id')
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
            Section::make('STEP 4 - Input Penduduk')->schema([
                TextInput::make('nik')->required()->unique(ignoreRecord: true)->maxLength(32),
                TextInput::make('nama_lengkap')->label('Nama Lengkap')->required()->maxLength(255),
                Select::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN)->required(),
                TextInput::make('tempat_lahir')->required()->maxLength(255),
                DatePicker::make('tanggal_lahir')->required(),
                Select::make('agama')->options(Penduduk::AGAMA)->required(),
                Select::make('status_perkawinan')->options(Penduduk::STATUS_PERKAWINAN)->required(),
                Select::make('pendidikan')->options(Penduduk::PENDIDIKAN)->required(),
                TextInput::make('pekerjaan')->maxLength(255),
                TextInput::make('penghasilan')->numeric()->minValue(0),
                Select::make('status_dalam_keluarga')->options(Penduduk::STATUS_DALAM_KELUARGA)->required(),
                TextInput::make('nomor_hp')->tel()->maxLength(32),
                Select::make('status_penduduk')->options(Penduduk::STATUS_PENDUDUK)->required(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik')->searchable()->sortable(),
                TextColumn::make('nama_lengkap')->label('Nama')->searchable()->sortable(),
                BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                BadgeColumn::make('jenis_kelamin')->label('JK'),
                TextColumn::make('keluarga.nomor_kk')->label('Nomor KK')->searchable(),
                TextColumn::make('keluarga.nama_kepala_keluarga')->label('Kepala Keluarga')->searchable(),
                TextColumn::make('keluarga.rumah.desa.nama')->label('Desa')->searchable(),
                TextColumn::make('keluarga.rumah.kode_rumah')->label('Kode Rumah')->searchable(),
                TextColumn::make('agama')->sortable(),
                TextColumn::make('pendidikan')->sortable(),
                TextColumn::make('umur')->label('Umur'),
            ])
            ->filters([
                SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $kecamatanId) => $query
                            ->whereHas('keluarga.rumah.desa', fn (Builder $desa) => $desa->where('parent_id', $kecamatanId)))),
                SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->options(fn () => AdminKecamatan::desaOptions())
                    ->query(fn (Builder $query, array $data) => $query
                        ->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $desaId) => $query
                            ->whereHas('keluarga.rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId))
                        )),
                SelectFilter::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN),
                SelectFilter::make('agama')->options(Penduduk::AGAMA),
                SelectFilter::make('pendidikan')->options(Penduduk::PENDIDIKAN),
                SelectFilter::make('batch_status')
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
                Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-arrow-down')->url(route('laporan.penduduk.pdf'))->openUrlInNewTab(),
                Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table-cells')->url(route('laporan.penduduk.excel'))->openUrlInNewTab(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
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
            'index' => ListPenduduks::route('/'),
            'create' => CreatePenduduk::route('/create'),
            'view' => ViewPenduduk::route('/{record}'),
            'edit' => EditPenduduk::route('/{record}/edit'),
        ];
    }
}
