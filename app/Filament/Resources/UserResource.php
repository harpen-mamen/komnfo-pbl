<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use App\Models\DeviceAssignment;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Opd;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'kecamatan', 'desa', 'opd'])
            ->when(
                PanelAccess::isKominfoKabupaten() && ! PanelAccess::isSuperAdmin(),
                fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $roles) => $roles
                    ->whereIn('name', ['super_admin', 'admin', 'admin_kominfo']))
            );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canManageUsers();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canManageUsers();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::canManageUsers();
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof User && self::canManageUser($record);
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof User && self::canManageUser($record);
    }

    public static function canDeleteAny(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    /** @return list<string> */
    public static function assignableRoles(): array
    {
        if (PanelAccess::isSuperAdmin()) {
            return [
                'super_admin', 'admin', 'admin_kominfo', 'walidata',
                'admin_bupati', 'admin_wakil_bupati',
                'admin_opd', 'kepala_dinas', 'operator_opd',
                'admin_berita',
                'admin_kecamatan', 'admin_desa', 'admin_lapangan',
                // OPD Admin - Kabupaten Kepulauan Sangihe (32 OPD)
                'admin_dinkes', 'operator_dinkes',
                'admin_dispend', 'operator_dispend',
                'admin_dpu', 'operator_dpu',
                'admin_dinsos', 'operator_dinsos',
                'admin_disperindag', 'operator_disperindag',
                'admin_dukcapil', 'operator_dukcapil',
                'admin_dlh', 'operator_dlh',
                'admin_distan', 'operator_distan',
                'admin_dishub', 'operator_dishub',
                'admin_distpar', 'operator_distpar',
                'admin_dispora', 'operator_dispora',
                'admin_bkpsdmd', 'operator_bkpsdmd',
                'admin_bappeda', 'operator_bappeda',
                'admin_bapenda', 'operator_bapenda',
                'admin_bpbd', 'operator_bpbd',
                'admin_diskominfo', 'operator_diskominfo',
                'admin_dpmptsp', 'operator_dpmptsp',
                'admin_distaman', 'operator_distaman',
                // OPD Baru Fase 1
                'admin_setda', 'operator_setda',
                'admin_setwan', 'operator_setwan',
                'admin_dppkb', 'operator_dppkb',
                'admin_dp3a', 'operator_dp3a',
                'admin_dpmd', 'operator_dpmd',
                'admin_disnaker', 'operator_disnaker',
                'admin_ketapang', 'operator_ketapang',
                'admin_perikanan', 'operator_perikanan',
                'admin_perpus', 'operator_perpus',
                'admin_perkim', 'operator_perkim',
                'admin_koperasi', 'operator_koperasi',
                'admin_inspektorat', 'operator_inspektorat',
                'admin_kesbangpol', 'operator_kesbangpol',
                'admin_batas', 'operator_batas',
                'admin_satpolpp', 'operator_satpolpp',
                'public',
            ];
        }

        if (PanelAccess::isKominfoKabupaten()) {
            return [
                'admin_opd', 'kepala_dinas', 'operator_opd',
                'admin_berita',
                'admin_kecamatan', 'admin_desa', 'admin_lapangan',
                // OPD Admin (limited)
                'admin_dinkes', 'operator_dinkes',
                'admin_dispend', 'operator_dispend',
                'admin_dpu', 'operator_dpu',
                'admin_dinsos', 'operator_dinsos',
                'admin_disperindag', 'operator_disperindag',
                'admin_dukcapil', 'operator_dukcapil',
                'admin_dlh', 'operator_dlh',
                'admin_distan', 'operator_distan',
                'admin_dishub', 'operator_dishub',
                'admin_distpar', 'operator_distpar',
                'admin_dispora', 'operator_dispora',
                'admin_bkpsdmd', 'operator_bkpsdmd',
                'admin_bappeda', 'operator_bappeda',
                'admin_bapenda', 'operator_bapenda',
                'admin_bpbd', 'operator_bpbd',
                'admin_diskominfo', 'operator_diskominfo',
                'admin_dpmptsp', 'operator_dpmptsp',
                'admin_distaman', 'operator_distaman',
            ];
        }

        return [];
    }

    public static function canAssignRole(?string $role): bool
    {
        return $role !== null && in_array($role, self::assignableRoles(), true);
    }

    private static function canManageUser(User $record): bool
    {
        if (! PanelAccess::canManageUsers()) {
            return false;
        }

        return PanelAccess::isSuperAdmin()
            || ! $record->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->maxLength(255)
                ->dehydrated(fn ($state): bool => filled($state))
                ->required(fn ($livewire): bool => $livewire instanceof CreateUser),
            Select::make('role')
                ->label('Role')
                ->options(fn () => Role::query()
                    ->whereIn('name', self::assignableRoles())
                    ->orderBy('name')
                    ->pluck('name', 'name'))
                ->required()
                ->reactive()
                ->afterStateHydrated(fn ($component, ?User $record): mixed => $component->state($record?->roles->first()?->name)),
            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => in_array($get('role'), ['admin_kecamatan', 'admin_desa', 'admin_lapangan'], true))
                ->visible(fn (callable $get): bool => in_array($get('role'), ['admin_kecamatan', 'admin_desa', 'admin_lapangan'], true))
                ->helperText('Wajib untuk Admin Kecamatan, Admin Desa, dan Admin Lapangan.'),
            Select::make('desa_id')
                ->label('Desa')
                ->options(fn (callable $get) => Wilayah::query()->where('tipe', Wilayah::TIPE_DESA)->when($get('kecamatan_id'), fn (Builder $query, $id) => $query->where('parent_id', $id))->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => $get('role') === 'admin_desa')
                ->visible(fn (callable $get): bool => in_array($get('role'), ['admin_desa', 'admin_lapangan'], true))
                ->helperText('Opsional untuk Admin Lapangan; jika kosong, cakupan mengikuti Kecamatan.'),
            Select::make('opd_id')
                ->label('OPD')
                ->options(fn () => Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => self::isOpdRole($get('role')))
                ->visible(fn (callable $get): bool => self::isOpdRole($get('role'))),
            TextInput::make('nip')
                ->label('NIP')
                ->maxLength(50)
                ->placeholder('Contoh: 198501012010121001'),
            TextInput::make('jabatan')
                ->label('Jabatan')
                ->maxLength(100)
                ->placeholder('Contoh: Kabid Data dan Informasi'),
            TextInput::make('no_hp')
                ->label('No. HP')
                ->tel()
                ->maxLength(20)
                ->placeholder('Contoh: 081234567890'),
            Placeholder::make('device_info')
                ->label('Aktivasi Device Mobile')
                ->content(function (?User $record) {
                    if (! $record) return 'User akan dibuat setelah simpan';
                    $assignments = $record->hasMany(DeviceAssignment::class)->get();
                    if ($assignments->isEmpty()) {
                        return '⚠ Belum ada device diaktivasi. Buka menu "Aktivasi Device" untuk mendaftarkan device mobile.';
                    }
                    return $assignments->map(fn ($a) =>
                        "• {$a->device_label} ({$a->device_id}) - ".($a->is_active ? '✅ Aktif' : '❌ Nonaktif')
                    )->implode("\n");
                })
                ->visible(fn (callable $get): bool => $get('role') === 'admin_lapangan'),
            FileUpload::make('foto')
                ->label('Foto Profil')
                ->avatar()
                ->image()
                ->disk('public')
                ->directory('avatars')
                ->maxSize(2048)
                ->imageCropAspectRatio('1:1')
                ->imageResizeTargetWidth(400)
                ->imageResizeTargetHeight(400)
                ->helperText('Maks. 2MB. Format: JPG, PNG. Rasio 1:1.'),
        ]);
    }

    /**
     * Check if role is an OPD role
     */
    public static function isOpdRole(?string $role): bool
    {
        if (!$role) {
            return false;
        }

        return in_array($role, [
            'admin_dinkes', 'operator_dinkes',
            'admin_dispend', 'operator_dispend',
            'admin_dpu', 'operator_dpu',
            'admin_dinsos', 'operator_dinsos',
            'admin_disperindag', 'operator_disperindag',
            'admin_dukcapil', 'operator_dukcapil',
            'admin_dlh', 'operator_dlh',
            'admin_distan', 'operator_distan',
            'admin_dishub', 'operator_dishub',
            'admin_distpar', 'operator_distpar',
            'admin_dispora', 'operator_dispora',
            'admin_bkpsdmd', 'operator_bkpsdmd',
            'admin_bappeda', 'operator_bappeda',
            'admin_bapenda', 'operator_bapenda',
            'admin_bpbd', 'operator_bpbd',
            'admin_diskominfo', 'operator_diskominfo',
            'admin_dpmptsp', 'operator_dpmptsp',
            'admin_distaman', 'operator_distaman',
            'admin_opd', 'kepala_dinas', 'operator_opd',
            // OPD Baru Fase 1
            'admin_setda', 'operator_setda',
            'admin_setwan', 'operator_setwan',
            'admin_dppkb', 'operator_dppkb',
            'admin_dp3a', 'operator_dp3a',
            'admin_dpmd', 'operator_dpmd',
            'admin_disnaker', 'operator_disnaker',
            'admin_ketapang', 'operator_ketapang',
            'admin_perikanan', 'operator_perikanan',
            'admin_perpus', 'operator_perpus',
            'admin_perkim', 'operator_perkim',
            'admin_koperasi', 'operator_koperasi',
            'admin_inspektorat', 'operator_inspektorat',
            'admin_kesbangpol', 'operator_kesbangpol',
            'admin_batas', 'operator_batas',
            'admin_satpolpp', 'operator_satpolpp',
        ], true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->width(40)
                    ->height(40)
                    ->rounded(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->formatStateUsing(fn ($state) => User::find(1)?->getRoleLabelAttribute() ?? $state)
                    ->searchable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->searchable(),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->searchable(),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->searchable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn () => Role::query()
                        ->whereIn('name', [
                            'super_admin', 'admin', 'admin_kominfo', 'walidata',
                            'admin_bupati', 'admin_wakil_bupati',
                            'admin_opd', 'kepala_dinas', 'operator_opd',
                            'admin_berita',
                            'admin_kecamatan', 'admin_desa', 'admin_lapangan',
                            'admin_dinkes', 'operator_dinkes',
                            'admin_dispend', 'operator_dispend',
                            'admin_dpu', 'operator_dpu',
                            'admin_dinsos', 'operator_dinsos',
                            'admin_disperindag', 'operator_disperindag',
                            'admin_dukcapil', 'operator_dukcapil',
                            'admin_dlh', 'operator_dlh',
                            'admin_distan', 'operator_distan',
                            'admin_dishub', 'operator_dishub',
                            'admin_distpar', 'operator_distpar',
                            'admin_dispora', 'operator_dispora',
                            'admin_bkpsdmd', 'operator_bkpsdmd',
                            'admin_bappeda', 'operator_bappeda',
                            'admin_bapenda', 'operator_bapenda',
                            'admin_bpbd', 'operator_bpbd',
                            'admin_diskominfo', 'operator_diskominfo',
                            'admin_dpmptsp', 'operator_dpmptsp',
                            'admin_distaman', 'operator_distaman',
                            'public',
                        ])
                        ->orderBy('name')
                        ->pluck('name', 'name'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, string $role) => $query->role($role))),
                SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->orderBy('nama')->pluck('nama', 'id')),
                SelectFilter::make('opd_id')
                    ->label('OPD')
                    ->options(fn () => Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
