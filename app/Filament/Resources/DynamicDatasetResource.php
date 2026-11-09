<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\DynamicDatasetResource\Pages\ListDynamicDatasetValues;
use App\Filament\Resources\DynamicDatasetResource\Pages\CreateDynamicDatasetValue;
use App\Filament\Resources\DynamicDatasetResource\Pages\EditDynamicDatasetValue;
use App\Filament\Resources\DynamicDatasetResource\Pages\ViewDynamicDatasetValue;
use App\Filament\Resources\DynamicDatasetResource\Pages;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Services\DataInput\DatasetFieldResolver;
use App\Services\DataInput\DatasetValueManager;
use Filament\Resources\Resource;
use Filament\Resources;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables;
use Filament\Notifications\Notification as FilamentNotificationsNotification;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DynamicDatasetResource extends Resource
{
    protected static ?string $model = DatasetValue::class;
    protected static ?string $slug = 'dataset-values';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Sektoral';
    protected static ?string $navigationLabel = 'Nilai Data';
    protected static ?string $modelLabel = 'Nilai Data Sektoral';
    protected static ?string $pluralModelLabel = 'Data Sektoral';
    protected static ?int $navigationSort = 10;

    public static function getActiveDataset(): ?Dataset
    {
        $kode = request('dataset');
        if (! $kode) return null;
        $user = Auth::user();
        return Dataset::with(['fields', 'opd'])
            ->where('kode', $kode)
            ->when($user && ! ($user->isSuperAdmin() || $user->isWalidata()), fn ($query) => $query->where('opd_id', $user->opd_id ?? -1))
            ->first();
    }

    public static function form(Schema $schema): Schema
    {
        $dataset = self::getActiveDataset();
        if (! $dataset) {
            return $schema->components([
                Placeholder::make('no_dataset')
                    ->label('')
                    ->content('Silakan pilih dataset dari menu Input Data OPD untuk mulai input data.'),
            ]);
        }
        abort_unless($dataset->is_active, 404);
        $user = Auth::user();
        abort_unless($user && ($user->isSuperAdmin() || $user->opd_id === $dataset->opd_id || $user->isWalidata()), 403);
        return $schema->components(DatasetFieldResolver::buildFormSchema($dataset));
    }

    public static function table(Table $table): Table
    {
        $dataset = self::getActiveDataset();
        $columns = [];

        $columns[] = TextColumn::make('dataset.nama')
            ->label('Dataset')
            ->searchable()
            ->wrap()
            ->visible(fn () => $dataset === null);

        $columns[] = TextColumn::make('tahun')
            ->label('Tahun')
            ->sortable();

        $columns[] = BadgeColumn::make('periode_label')
            ->label('Periode')
            ->color('info')
            ->placeholder('-');

        $columns[] = TextColumn::make('wilayah.nama')
            ->label('Wilayah')
            ->placeholder('Kabupaten')
            ->searchable();

        $columns[] = BadgeColumn::make('status')
            ->label('Status')
            ->color(fn ($state) => self::statusColor($state))
            ->formatStateUsing(fn ($state) => self::statusLabel($state));

        $columns[] = TextColumn::make('created_at')
            ->label('Tgl Input')
            ->dateTime('d M Y H:i')
            ->sortable();

        if ($dataset) {
            foreach ($dataset->fields()->orderBy('urutan')->limit(3)->get() as $field) {
                $columns[] = TextColumn::make('values.' . $field->nama)
                    ->getStateUsing(fn ($record) => is_array($value = data_get($record->values, $field->nama)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)
                    ->label(Str::limit($field->label, 25))
                    ->wrap();
            }
        } else {
            $columns[] = TextColumn::make('values')->label('Nilai')->wrap()->limit(80)
                ->getStateUsing(fn ($record) => json_encode($record->values, JSON_UNESCAPED_UNICODE));
        }

        $columns[] = TextColumn::make('notes')->label('Sumber / Catatan')
            ->formatStateUsing(fn ($state) => preg_replace('/^\[SANGIHE-SOURCE:[^\]]+\]\s*/', '', (string)$state))
            ->limit(70)->wrap()->toggleable(isToggledHiddenByDefault: true);

        return $table->columns($columns)->filters([
            SelectFilter::make('status')->options([
                'draft' => 'Draft',
                'submitted' => 'Diajukan',
                'verified' => 'Diverifikasi',
                'published' => 'Dipublikasi',
                'rejected' => 'Ditolak',
                'revision' => 'Revisi',
            ])->multiple(),
            Filter::make('tahun')->schema([
                TextInput::make('tahun')->numeric()->placeholder('Tahun'),
            ])->query(fn ($query, $data) => $query->when($data['tahun'], fn ($q) => $q->where('tahun', $data['tahun']))),
        ])->recordActions([
            ViewAction::make(),
            EditAction::make()->visible(fn ($record) => in_array($record->status, ['draft', 'revision'], true)),
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'submitted' && Auth::user()?->isWalidata())
                ->action(function ($record) {
                    app(DatasetValueManager::class)->verify($record);
                    FilamentNotificationsNotification::make()->title('Data diverifikasi')->success()->send();
                }),
            Action::make('publish')
                ->label('Publikasikan')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'verified' && Auth::user()?->isWalidata())
                ->action(function ($record) {
                    app(DatasetValueManager::class)->publish($record);
                    FilamentNotificationsNotification::make()->title('Data dipublikasikan')->success()->send();
                }),
            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['submitted', 'verified'], true) && Auth::user()?->isWalidata())
                ->schema([Textarea::make('notes')->label('Catatan')->required()])
                ->action(function ($record, $data) {
                    app(DatasetValueManager::class)->reject($record, $data['notes']);
                    FilamentNotificationsNotification::make()->title('Data ditolak')->warning()->send();
                }),
            Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(fn ($record) => $record->status === 'submitted' && Auth::user()?->isWalidata())
                ->schema([Textarea::make('notes')->label('Catatan revisi')->required()])
                ->action(function ($record, $data) {
                    app(DatasetValueManager::class)->requestRevision($record, $data['notes']);
                    FilamentNotificationsNotification::make()->title('Revisi diminta')->warning()->send();
                }),
            DeleteAction::make()->visible(fn ($record) => $record->status === 'draft' && Auth::user() && (Auth::user()->isSuperAdmin() || Auth::user()->opd_id === $record->dataset?->opd_id)),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if (! $user) return $query->whereRaw('1=0');
        $dataset = self::getActiveDataset();
        if ($dataset) $query->where('dataset_id', $dataset->id);
        if ($user->isSuperAdmin() || $user->isWalidata()) return $query;
        if ($user->opd_id) {
            $query->whereHas('dataset', function ($q) use ($user) {
                $q->where('opd_id', $user->opd_id);
            });
        } else {
            $query->whereRaw('1=0');
        }
        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDynamicDatasetValues::route('/'),
            'create' => CreateDynamicDatasetValue::route('/create'),
            'edit' => EditDynamicDatasetValue::route('/{record}/edit'),
            'view' => ViewDynamicDatasetValue::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    public static function statusColor($state): string
    {
        return match ($state) {
            'draft' => 'gray',
            'submitted' => 'warning',
            'verified' => 'info',
            'published' => 'success',
            'rejected' => 'danger',
            'revision' => 'warning',
            default => 'gray',
        };
    }

    public static function statusLabel($state): string
    {
        return match ($state) {
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'verified' => 'Diverifikasi',
            'published' => 'Dipublikasi',
            'rejected' => 'Ditolak',
            'revision' => 'Revisi',
            default => $state ?? '-',
        };
    }
}
