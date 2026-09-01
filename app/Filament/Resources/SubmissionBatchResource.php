<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmissionBatchResource\Pages;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubmissionBatchResource extends Resource
{
    protected static ?string $model = SubmissionBatch::class;
    protected static ?string $navigationIcon = 'heroicon-o-badge-check';
    protected static ?int $navigationSort = 14;

    public static function getNavigationLabel(): string
    {
        return PanelAccess::isAdminKecamatan()
            ? 'Verifikasi Tingkat Kecamatan'
            : 'Verifikasi Kominfo';
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

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan();
    }

    public static function canView(Model $record): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan();
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

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('submission_number')->label('Batch')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('source')
                    ->label('Asal')
                    ->formatStateUsing(fn (SubmissionBatch $record): string => $record->isFromMobile() ? 'Mobile' : 'Web')
                    ->colors([
                        'info' => 'mobile',
                        'gray' => 'web',
                    ])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("JSON_EXTRACT(payload_snapshot, '\$.source') {$direction}");
                    }),
                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->formatStateUsing(fn ($state) => SubmissionBatch::moduleLabel($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
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
                Tables\Columns\TextColumn::make('createdBy.name')->label('Pengirim')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('submitted_at')->label('Tanggal Kirim')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        SubmissionBatch::STATUS_DRAFT => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_DRAFT),
                        SubmissionBatch::STATUS_PENDING => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                        SubmissionBatch::STATUS_IN_REVIEW => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_IN_REVIEW),
                        SubmissionBatch::STATUS_APPROVED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_APPROVED),
                        SubmissionBatch::STATUS_REJECTED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REJECTED),
                    ]),
                Tables\Filters\SelectFilter::make('module')
                    ->label('Modul')
                    ->options([
                        SubmissionBatch::MODULE_RUMAH => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_RUMAH),
                        SubmissionBatch::MODULE_KELUARGA => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_KELUARGA),
                        SubmissionBatch::MODULE_PENDUDUK => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_PENDUDUK),
                        SubmissionBatch::MODULE_KESEHATAN => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_KESEHATAN),
                        SubmissionBatch::MODULE_PENDIDIKAN => SubmissionBatch::moduleLabel(SubmissionBatch::MODULE_PENDIDIKAN),
                    ]),
                Tables\Filters\Filter::make('source')
                    ->label('Asal')
                    ->form([
                        \Filament\Forms\Components\Radio::make('source')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissionBatches::route('/'),
            'view' => Pages\ViewSubmissionBatch::route('/{record}'),
        ];
    }
}
