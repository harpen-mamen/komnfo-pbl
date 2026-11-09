<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DeviceAssignmentResource\Pages\ListDeviceAssignments;
use App\Filament\Resources\DeviceAssignmentResource\Pages\CreateDeviceAssignment;
use App\Filament\Resources\DeviceAssignmentResource\Pages\EditDeviceAssignment;
use App\Filament\Resources\DeviceAssignmentResource\Pages;
use App\Models\DeviceAssignment;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DeviceAssignmentResource extends Resource
{
    protected static ?string $model = DeviceAssignment::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'Aktivasi Device';
    protected static ?string $pluralModelLabel = 'Aktivasi Device Mobile';
    protected static ?string $modelLabel = 'Aktivasi Device';
    protected static ?int $navigationSort = 15;

    /**
     * Hanya super_admin dan admin yang boleh akses.
     * (Admin OPD kecamatan bisa diizinkan via Gate)
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user) return false;
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Select::make('user_id')
                        ->label('Admin Lapangan')
                        ->options(function () {
                            return User::whereHas('roles', fn (Builder $query) => $query
                                ->where('guard_name', 'web')->whereIn('name', ['admin_lapangan', 'admin_desa']))
                                ->with(['desa', 'kecamatan'])
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($u) => [
                                    $u->id => $u->name.' ('.($u->desa?->nama ?? $u->kecamatan?->nama).')',
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->helperText('Pilih user admin lapangan/desa yang device-nya akan diaktifkan.'),

                    TextInput::make('device_id')
                        ->label('Device ID (UUID)')
                        ->required()
                        ->maxLength(120)
                        ->helperText('UUID device mobile. Bisa dilihat di info app mobile admin lapangan.'),

                    TextInput::make('device_label')
                        ->label('Label Device (opsional)')
                        ->maxLength(100)
                        ->placeholder('Contoh: HP Pak Yusuf'),

                    Select::make('platform')
                        ->label('Platform')
                        ->options([
                            'android' => 'Android',
                            'ios' => 'iOS',
                            'other' => 'Lainnya',
                        ])
                        ->default('android'),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Nonaktifkan untuk sementara tanpa menghapus data.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Admin Lapangan')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('user.roles.name')
                    ->label('Role'),

                TextColumn::make('device_label')
                    ->label('Label')
                    ->placeholder('-'),

                TextColumn::make('device_id')
                    ->label('Device ID')
                    ->copyable()
                    ->copyMessage('Device ID disalin')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->device_id),

                BadgeColumn::make('platform')
                    ->label('Platform')
                    ->color(fn ($state) => match ($state) {
                        'android' => 'success',
                        'ios' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('activated_at')
                    ->label('Aktivasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('last_seen_at')
                    ->label('Terakhir Akses')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options(['android' => 'Android', 'ios' => 'iOS']),
                TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('activated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceAssignments::route('/'),
            'create' => CreateDeviceAssignment::route('/create'),
            'edit' => EditDeviceAssignment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }
}
