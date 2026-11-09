<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Actions\ViewAction;
use App\Filament\Resources\SubmissionBatchResource\Pages\ListSubmissionBatches;
use App\Filament\Resources\SubmissionBatchResource\Pages\ViewSubmissionBatch;
use App\Filament\Resources\SubmissionBatchResource\Pages;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubmissionBatchResource extends Resource
{
    protected static ?string $model = SubmissionBatch::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';
    protected static ?int $navigationSort = 14;

    public static function getNavigationLabel(): string
    {
        if (PanelAccess::isAdminLapangan()) {
            return 'Kiriman Titik Lapangan';
        }

        return PanelAccess::isAdminKecamatan() ? 'Verifikasi Tingkat Kecamatan' : 'Kiriman Lapangan';
    }

    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleToCurrentUser()
            ->with(['kecamatan', 'createdBy', 'verifiedBy']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan() || PanelAccess::isAdminLapangan();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan() || PanelAccess::isAdminLapangan();
    }

    public static function canView(Model $record): bool
    {
        if (PanelAccess::isAdminKecamatan()) {
            return $record instanceof SubmissionBatch && $record->requiresKecamatanGate();
        }

        if (PanelAccess::isAdminLapangan()) {
            return $record instanceof SubmissionBatch
                && $record->module === SubmissionBatch::MODULE_LAPANGAN
                && (int) $record->created_by === (int) PanelAccess::user()?->id;
        }

        return PanelAccess::canAccessKabupatenPanel();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('submission_number')->label('Batch')->searchable()->sortable(),
                BadgeColumn::make('source')
                    ->label('Asal')
                    ->formatStateUsing(fn (SubmissionBatch $record): string => $record->isFromMobile() ? 'Mobile' : 'Web')
                    ->colors([
                        'info' => 'mobile',
                        'gray' => 'web',
                    ])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("JSON_EXTRACT(payload_snapshot, '\$.source') {$direction}");
                    }),
                TextColumn::make('module')
                    ->label('Modul')
                    ->formatStateUsing(fn ($state) => SubmissionBatch::moduleLabel($state))
                    ->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable()->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => SubmissionBatch::STATUS_DRAFT,
                        'warning' => SubmissionBatch::STATUS_PENDING,
                        'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                        'success' => SubmissionBatch::STATUS_APPROVED,
                        'danger' => SubmissionBatch::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))
                    ->sortable(),
                TextColumn::make('createdBy.name')->label('Pengirim')->sortable()->searchable(),
                TextColumn::make('submitted_at')->label('Tanggal Kirim')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        SubmissionBatch::STATUS_DRAFT => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_DRAFT),
                        SubmissionBatch::STATUS_PENDING => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                        SubmissionBatch::STATUS_IN_REVIEW => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_IN_REVIEW),
                        SubmissionBatch::STATUS_APPROVED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_APPROVED),
                        SubmissionBatch::STATUS_REJECTED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REJECTED),
                    ]),
                SelectFilter::make('module')
                    ->label('Modul')
                    ->options([
                        SubmissionBatch::MODULE_RUMAH => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_RUMAH),
                        SubmissionBatch::MODULE_KELUARGA => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_KELUARGA),
                        SubmissionBatch::MODULE_PENDUDUK => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_PENDUDUK),
                        SubmissionBatch::MODULE_KESEHATAN => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_KESEHATAN),
                        SubmissionBatch::MODULE_PENDIDIKAN => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_PENDIDIKAN),
                        SubmissionBatch::MODULE_LAPANGAN => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_LAPANGAN),
                    ]),
                Filter::make('source')
                    ->label('Asal')
                    ->schema([
                        Radio::make('source')
                            ->options([
                                'mobile' => 'Mobile',
                                'web' => 'Web',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $source = $data['value'];
                        if ($source === 'mobile') {
                            return $query->whereRaw("JSON_EXTRACT(payload_snapshot, '\$.source') = ?", ['"mobile"']);
                        }
                        // Web: source is absent (NULL) in payload_snapshot
                        return $query->whereRaw("JSON_EXTRACT(payload_snapshot, '\$.source') IS NULL");
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubmissionBatches::route('/'),
            'view' => ViewSubmissionBatch::route('/{record}'),
        ];
    }
}
