<?php

namespace App\Filament\Resources\WORKFLOW;

use App\Policies\SubmissionBatchPolicy;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use App\Models\Unit;
use App\Support\UserScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages\ListBatch;
use App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages\ViewBatch;
use App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages\EditBatch;
use App\Models\WORKFLOW\SubmissionBatch;
use App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages;
use Filament\Resources\Resource;

abstract class SubmissionBatchResource extends Resource
{
    protected static ?string $model = SubmissionBatch::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Batch';
    protected static string | \UnitEnum | null $navigationGroup = 'Workflow';
    protected static ?string $policy = SubmissionBatchPolicy::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Residual resource from the removed 4-domain schema; the legacy app
        // has its own SubmissionBatch resource at the root namespace.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        // Scope unit options based on user role
        $unitQuery = Unit::query()->where('aktif', true);
        if (!$user->isWalidata() && !$user->isPembina()) {
            $visibleUnitIds = UserScope::getVisibleUnitIds($user);
            $unitQuery->whereIn('id', $visibleUnitIds);
        }

        return $schema->components([
            Select::make('jenis')
                ->options(['sektoral' => 'Sektoral','lapangan' => 'Lapangan']),
            Select::make('unit_id')->label('Unit')
                ->relationship('unit', 'nama', fn ($q) => $unitQuery)
                ->searchable()
                ->preload(),
            TextInput::make('periode')->label('Periode'),
            Select::make('status')
                ->options(['draft' => 'Draft','published' => 'Published']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            BadgeColumn::make('jenis'),
            TextColumn::make('unit.nama'),
            TextColumn::make('periode'),
            BadgeColumn::make('status'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatch::route('/'),
            'view' => ViewBatch::route('/{record}'),
            'edit' => EditBatch::route('/{record}/edit'),
        ];
    }
}
