<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\KeluargaResource\RelationManagers\PenduduksRelationManager;
use App\Filament\Resources\KeluargaResource\Pages\ListKeluargas;
use App\Filament\Resources\KeluargaResource\Pages\CreateKeluarga;
use App\Filament\Resources\KeluargaResource\Pages\ViewKeluarga;
use App\Filament\Resources\KeluargaResource\Pages\EditKeluarga;
use App\Filament\Resources\KeluargaResource\Pages;
use App\Models\Keluarga;
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

abstract class KeluargaResource extends Resource
{
    protected static ?string $model = Keluarga::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Keluarga';
    protected static ?string $pluralModelLabel = 'Data Keluarga';
    protected static ?string $modelLabel = 'Keluarga';
    protected static ?string $recordTitleAttribute = 'nomor_kk';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['rumah', 'latestSubmissionBatchItem.submissionBatch']);
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
            ->canEditRecord(Keluarga::class, $record->id);
    }

    public static function canDelete(Model $record): bool
    {
        if (! PanelAccess::isSuperAdmin() && ! PanelAccess::isKependudukanDataOwner()) {
            return false;
        }

        return app(WewenangService::class)
            ->setUser(auth()->user())
            ->canDeleteRecord(Keluarga::class, $record->id);
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
                ->label('Desa')
                ->dehydrated(false)
                ->options(fn () => AdminKecamatan::desaOptions())
                ->searchable()
                ->required()
                ->reactive()
                ->disabled(fn () => AdminKecamatan::isAdminDesa())
                ->afterStateHydrated(function ($component, $state, ?Keluarga $record): void {
                    if ($record?->rumah?->desa_id) {
                        $component->state($record->rumah->desa_id);
                    }
                })
                ->afterStateUpdated(fn (callable $set) => $set('rumah_id', null)),
            Select::make('rumah_id')
                ->label('Rumah')
                ->options(fn (callable $get) => Rumah::query()
                    ->visibleToCurrentUser()
                    ->when($get('desa_id'), fn (Builder $query, $desaId) => $query->where('desa_id', $desaId))
                    ->orderBy('kode_rumah')
                    ->pluck('kode_rumah', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('info_rumah', null)),
            Placeholder::make('detail_rumah')
                ->label('Detail Rumah')
                ->content(function (callable $get): string {
                    $rumah = Rumah::query()->visibleToCurrentUser()->find($get('rumah_id'));

                    return $rumah
                        ? "{$rumah->kode_rumah} | {$rumah->alamat_lengkap} | {$rumah->latitude}, {$rumah->longitude}"
                        : 'Pilih rumah untuk melihat kode, alamat, dan koordinat.';
                })
                ->columnSpanFull(),
            TextInput::make('nomor_kk')->label('Nomor KK')->required()->unique(ignoreRecord: true)->maxLength(32),
            TextInput::make('nama_kepala_keluarga')->label('Nama Kepala Keluarga')->required()->maxLength(255),
            TextInput::make('jumlah_anggota')->numeric()->minValue(0)->default(0)->required(),
            Select::make('status_ekonomi')->options(Keluarga::STATUS_EKONOMI)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_kk')->label('Nomor KK')->searchable()->sortable(),
                TextColumn::make('nama_kepala_keluarga')->label('Kepala Keluarga')->searchable(),
                BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                TextColumn::make('rumah.desa.nama')->label('Desa')->searchable(),
                TextColumn::make('rumah.kode_rumah')->label('Kode Rumah')->searchable(),
                TextColumn::make('rumah.alamat_lengkap')->label('Alamat')->limit(50)->searchable(),
                TextColumn::make('jumlah_anggota')->sortable(),
                BadgeColumn::make('status_ekonomi')->label('Status Ekonomi'),
            ])
            ->filters([
                SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $kecamatanId) => $query
                            ->whereHas('rumah.desa', fn (Builder $desa) => $desa->where('parent_id', $kecamatanId)))),
                SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->options(fn () => AdminKecamatan::desaOptions())
                    ->query(fn (Builder $query, array $data) => $query
                        ->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $desaId) => $query
                            ->whereHas('rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId))
                        )),
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
                SelectFilter::make('status_ekonomi')->options(Keluarga::STATUS_EKONOMI),
            ])
            ->headerActions(PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner() ? [
                Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-arrow-down')->url(route('laporan.keluarga.pdf'))->openUrlInNewTab(),
                Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table-cells')->url(route('laporan.keluarga.excel'))->openUrlInNewTab(),
            ] : [])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor_kk', 'nama_kepala_keluarga', 'rumah.kode_rumah', 'rumah.alamat_lengkap'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->visibleToCurrentUser()->with('rumah');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'KK' => $record->nomor_kk,
            'Kepala Keluarga' => $record->nama_kepala_keluarga,
            'Rumah' => $record->rumah?->kode_rumah,
            'Alamat' => $record->rumah?->alamat_lengkap,
        ];
    }

    public static function getRelations(): array
    {
        return [
            PenduduksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeluargas::route('/'),
            'create' => CreateKeluarga::route('/create'),
            'view' => ViewKeluarga::route('/{record}'),
            'edit' => EditKeluarga::route('/{record}/edit'),
        ];
    }
}
