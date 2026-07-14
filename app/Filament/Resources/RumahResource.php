<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RumahResource\Pages;
use App\Models\Rumah;
use App\Models\Kecamatan;
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

class RumahResource extends Resource
{
    protected static ?string $model = Rumah::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Rumah';
    protected static ?string $pluralModelLabel = 'Data Rumah';
    protected static ?string $modelLabel = 'Rumah';
    protected static ?string $recordTitleAttribute = 'kode_rumah';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->withCount(['keluarga'])->with(['latestSubmissionBatchItem.submissionBatch']);
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
                ->label('Desa')
                ->options(fn () => AdminKecamatan::desaOptions())
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('kode_rumah')->label('Kode Rumah')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Textarea::make('alamat_lengkap')->label('Alamat Lengkap')->required()->rows(3)->columnSpanFull(),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('latitude')->required()->numeric()->reactive(),
                Forms\Components\TextInput::make('longitude')->required()->numeric()->reactive(),
            ]),
            Forms\Components\View::make('filament.forms.leaflet-picker')->columnSpanFull(),
            Forms\Components\Select::make('status_rumah')->label('Status Rumah')->options(Rumah::STATUS)->required(),
            Forms\Components\Textarea::make('keterangan')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_rumah')->label('Kode')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('alamat_lengkap')->label('Alamat')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('latitude')->label('Lat'),
                Tables\Columns\TextColumn::make('longitude')->label('Lng'),
                Tables\Columns\BadgeColumn::make('status_rumah')->label('Status'),
                Tables\Columns\TextColumn::make('keluarga_count')->label('Jumlah KK')->counts('keluarga'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $kecamatanId) => $query
                            ->whereHas('desa', fn (Builder $desa) => $desa->where('kecamatan_id', $kecamatanId)))),
                Tables\Filters\SelectFilter::make('desa_id')->label('Desa')->options(fn () => AdminKecamatan::desaOptions()),
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
                Tables\Filters\SelectFilter::make('status_rumah')->options(Rumah::STATUS),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-download')->url(route('laporan.rumah.pdf'))->openUrlInNewTab(),
                Tables\Actions\Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table')->url(route('laporan.rumah.excel'))->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RumahResource\RelationManagers\KeluargasRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_rumah', 'alamat_lengkap'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->visibleToCurrentUser()->withCount('keluarga');
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Rumah' => $record->kode_rumah,
            'Alamat' => $record->alamat_lengkap,
            'KK' => (string) $record->keluarga_count,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRumah::route('/'),
            'create' => Pages\CreateRumah::route('/create'),
            'view' => Pages\ViewRumah::route('/{record}'),
            'edit' => Pages\EditRumah::route('/{record}/edit'),
        ];
    }
}
