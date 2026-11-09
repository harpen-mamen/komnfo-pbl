<?php

namespace App\Filament\Resources\DATA_LAPANGAN;

use App\Policies\ObjekLapanganPolicy;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use App\Models\Unit;
use App\Support\UserScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages\ListObjekLapangan;
use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages\ViewObjekLapangan;
use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages\EditObjekLapangan;
use App\Models\DATA_LAPANGAN\ObjekLapangan;
use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages;
use Filament\Resources\Resource;

abstract class ObjekLapanganResource extends Resource
{
    protected static ?string $model = ObjekLapangan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Objek';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Lapangan';
    protected static ?string $policy = ObjekLapanganPolicy::class;

    /** Objek Lapangan - tampil untuk role PRODUSEN (admin_desa/kecamatan/walidata) + verification + OPD (lihat KANTOR saja). */
    public static function shouldRegisterNavigation(): bool
    {
        // This resource belongs to the removed 4-domain schema. Keep it hidden
        // on the legacy Filament panel, where its model and role API do not exist.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        // Scope unit options based on user role
        $unitQuery = Unit::query()->where('aktif', true);

        // Admin Desa: only their own unit
        // Admin Kecamatan: their unit + all villages under it
        // Walidata/Pembina: all units
        if (!$user->isWalidata() && !$user->isPembina()) {
            $visibleUnitIds = UserScope::getVisibleUnitIds($user);
            $unitQuery->whereIn('id', $visibleUnitIds);
        }

        return $schema->components([
            Select::make('jenis')->label('Jenis')
                ->options(['RUMAH' => 'Rumah','JALAN' => 'Jalan','JEMBATAN' => 'Jembatan',
                    'DRAINASE' => 'Drainase','SEKOLAH' => 'Sekolah','KANTOR' => 'Kantor'])->required(),
            Select::make('unit_id')->label('Desa')
                ->relationship('unit', 'nama', fn ($q) => $unitQuery)
                ->searchable()
                ->preload()
                ->required(),
            Select::make('kondisi')
                ->options(['baik' => 'Baik','sedang' => 'Sedang','rusak' => 'Rusak']),
            Select::make('status')
                ->options(['draft' => 'Draft','published' => 'Published']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            BadgeColumn::make('jenis'),
            TextColumn::make('unit.nama'),
            BadgeColumn::make('kondisi')
                ->colors(['baik' => 'success','sedang' => 'warning','rusak' => 'danger']),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObjekLapangan::route('/'),
            'view' => ViewObjekLapangan::route('/{record}'),
            'edit' => EditObjekLapangan::route('/{record}/edit'),
        ];
    }
}
