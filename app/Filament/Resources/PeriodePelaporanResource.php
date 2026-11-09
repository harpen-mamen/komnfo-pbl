<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\PeriodePelaporanResource\Pages\ListPeriodePelaporans;
use App\Filament\Resources\PeriodePelaporanResource\Pages\CreatePeriodePelaporan;
use App\Filament\Resources\PeriodePelaporanResource\Pages\ViewPeriodePelaporan;
use App\Filament\Resources\PeriodePelaporanResource\Pages\EditPeriodePelaporan;
use App\Filament\Resources\PeriodePelaporanResource\Pages;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class PeriodePelaporanResource extends Resource
{
    protected static ?string $model = PeriodePelaporan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Konfigurasi Sistem';
    protected static ?string $pluralModelLabel = 'Konfigurasi Sistem';
    protected static ?string $modelLabel = 'Konfigurasi';
    protected static ?string $recordTitleAttribute = 'nama_periode';
    protected static ?int $navigationSort = 99;

    /**
     * Month options for dropdown
     */
    public static function monthOptions(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * Year options for dropdown
     */
    public static function yearOptions(): array
    {
        $years = [];
        $currentYear = (int) date('Y');
        for ($year = $currentYear - 5; $year <= $currentYear + 2; $year++) {
            $years[$year] = (string) $year;
        }
        return $years;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    // Periode pelaporan adalah OTOMATIS oleh sistem, BUKAN manual
    // Super Admin bisa melihat daftar periode
    // Periode baru dibuat oleh sistem berdasarkan scheduler/job
    public static function canCreate(): bool
    {
        return false; // Tidak ada yang bisa membuat periode manual
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Tidak ada yang bisa edit periode manual
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama_periode')
                ->label('Nama Periode')
                ->required()
                ->maxLength(255),
            Select::make('bulan')
                ->label('Bulan')
                ->options(self::monthOptions())
                ->required(),
            TextInput::make('tahun')
                ->label('Tahun')
                ->numeric()
                ->minValue(2020)
                ->maxValue(2100)
                ->default((int) now()->year)
                ->required(),
            DatePicker::make('tanggal_mulai')
                ->label('Tanggal Mulai')
                ->required(),
            DatePicker::make('tanggal_selesai')
                ->label('Tanggal Selesai')
                ->required()
                ->afterOrEqual('tanggal_mulai'),
            Select::make('status')
                ->label('Status')
                ->options(PeriodePelaporan::STATUS)
                ->default('Draft')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_periode')->label('Nama Periode')->searchable()->sortable(),
                TextColumn::make('bulan')->label('Bulan')->formatStateUsing(fn ($state) => self::monthOptions()[(int) $state] ?? $state)->sortable(),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('tanggal_mulai')->label('Mulai')->date('d M Y')->sortable(),
                TextColumn::make('tanggal_selesai')->label('Selesai')->date('d M Y')->sortable(),
                BadgeColumn::make('status')->label('Status'),
                TextColumn::make('created_at')->label('Created At')->dateTime('d M Y H:i')->toggleable(),
                TextColumn::make('updated_at')->label('Updated At')->dateTime('d M Y H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PeriodePelaporan::STATUS),
                SelectFilter::make('bulan')->options(self::monthOptions()),
                SelectFilter::make('tahun')->options(self::yearOptions()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('tahun', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeriodePelaporans::route('/'),
            'create' => CreatePeriodePelaporan::route('/create'),
            'view' => ViewPeriodePelaporan::route('/{record}'),
            'edit' => EditPeriodePelaporan::route('/{record}/edit'),
        ];
    }
}
