<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeluargaResource\Pages;
use App\Models\Keluarga;
use App\Models\Kecamatan;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Services\Authorization\WewenangService;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KeluargaResource extends Resource
{
    protected static ?string $model = Keluarga::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Keluarga';
    protected static ?string $pluralModelLabel = 'Data Keluarga';
    protected static ?string $modelLabel = 'Keluarga';
    protected static ?string $recordTitleAttribute = 'nomor_kk';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['rumah', 'latestSubmissionBatchItem.submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // SPRINT 6: Admin Kecamatan BISA membuat data master
        if ($user->isAdminKecamatan()) {
            return true;
        }

        // Admin Desa
        if ($user->isAdminDesa()) {
            return true;
        }

        // Kabupaten level
        if (PanelAccess::canAccessKabupatenPanel()) {
            return true;
        }

        return false;
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public static function canEdit(Model $record): bool
    {
        return app(WewenangService::class)
            ->setUser(auth()->user())
            ->canEditRecord(Keluarga::class, $record->id);
    }

    public static function canDelete(Model $record): bool
    {
        return app(WewenangService::class)
            ->setUser(auth()->user())
            ->canDeleteRecord(Keluarga::class, $record->id);
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
            Forms\Components\Select::make('rumah_id')
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
            Forms\Components\Placeholder::make('detail_rumah')
                ->label('Detail Rumah')
                ->content(function (callable $get): string {
                    $rumah = Rumah::query()->visibleToCurrentUser()->find($get('rumah_id'));

                    return $rumah
                        ? "{$rumah->kode_rumah} | {$rumah->alamat_lengkap} | {$rumah->latitude}, {$rumah->longitude}"
                        : 'Pilih rumah untuk melihat kode, alamat, dan koordinat.';
                })
                ->columnSpanFull(),
            Forms\Components\TextInput::make('nomor_kk')->label('Nomor KK')->required()->unique(ignoreRecord: true)->maxLength(32),
            Forms\Components\TextInput::make('nama_kepala_keluarga')->label('Nama Kepala Keluarga')->required()->maxLength(255),
            Forms\Components\TextInput::make('jumlah_anggota')->numeric()->minValue(0)->default(0)->required(),
            Forms\Components\Select::make('status_ekonomi')->options(Keluarga::STATUS_EKONOMI)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_kk')->label('Nomor KK')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_kepala_keluarga')->label('Kepala Keluarga')->searchable(),
                Tables\Columns\BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                Tables\Columns\TextColumn::make('rumah.desa.nama')->label('Desa')->searchable(),
                Tables\Columns\TextColumn::make('rumah.kode_rumah')->label('Kode Rumah')->searchable(),
                Tables\Columns\TextColumn::make('rumah.alamat_lengkap')->label('Alamat')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('jumlah_anggota')->sortable(),
                Tables\Columns\BadgeColumn::make('status_ekonomi')->label('Status Ekonomi'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $kecamatanId) => $query
                            ->whereHas('rumah.desa', fn (Builder $desa) => $desa->where('kecamatan_id', $kecamatanId)))),
                Tables\Filters\SelectFilter::make('desa_id')
                    ->label('Desa')
                    ->options(fn () => AdminKecamatan::desaOptions())
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $desaId) => $query
                            ->whereHas('rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId))
                        )),
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
                Tables\Filters\SelectFilter::make('status_ekonomi')->options(Keluarga::STATUS_EKONOMI),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-download')->url(route('laporan.keluarga.pdf'))->openUrlInNewTab(),
                Tables\Actions\Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table')->url(route('laporan.keluarga.excel'))->openUrlInNewTab(),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
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
            KeluargaResource\RelationManagers\PenduduksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeluargas::route('/'),
            'create' => Pages\CreateKeluarga::route('/create'),
            'view' => Pages\ViewKeluarga::route('/{record}'),
            'edit' => Pages\EditKeluarga::route('/{record}/edit'),
        ];
    }
}
