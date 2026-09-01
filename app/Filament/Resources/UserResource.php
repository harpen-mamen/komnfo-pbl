<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Opd;
use App\Models\User;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
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

    protected static function shouldRegisterNavigation(): bool
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
                'super_admin', 'admin', 'admin_kominfo', 'admin_opd',
                'admin_kecamatan', 'admin_desa',
                // OPD Admin - Kabupaten Kepulauan Sangihe
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
                'admin_puskes', 'operator_puskes',
                'admin_sekolah', 'operator_sekolah',
                'public',
            ];
        }

        if (PanelAccess::isKominfoKabupaten()) {
            return [
                'admin_opd',
                'admin_kecamatan', 'admin_desa',
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->maxLength(255)
                ->dehydrated(fn ($state): bool => filled($state))
                ->required(fn ($livewire): bool => $livewire instanceof Pages\CreateUser),
            Forms\Components\Select::make('role')
                ->label('Role')
                ->options(fn () => Role::query()
                    ->whereIn('name', self::assignableRoles())
                    ->orderBy('name')
                    ->pluck('name', 'name'))
                ->required()
                ->reactive()
                ->afterStateHydrated(fn ($component, ?User $record): mixed => $component->state($record?->roles->first()?->name)),
            Forms\Components\Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => in_array($get('role'), ['admin_kecamatan', 'admin_desa'], true))
                ->visible(fn (callable $get): bool => in_array($get('role'), ['admin_kecamatan', 'admin_desa'], true))
                ->helperText('Wajib untuk Admin Kecamatan dan Admin Desa.'),
            Forms\Components\Select::make('desa_id')
                ->label('Desa')
                ->options(fn (callable $get) => Desa::query()->when($get('kecamatan_id'), fn (Builder $query, $id) => $query->where('kecamatan_id', $id))->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => $get('role') === 'admin_desa')
                ->visible(fn (callable $get): bool => $get('role') === 'admin_desa'),
            Forms\Components\Select::make('opd_id')
                ->label('OPD')
                ->options(fn () => Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->required(fn (callable $get): bool => self::isOpdRole($get('role')))
                ->visible(fn (callable $get): bool => self::isOpdRole($get('role'))),
            Forms\Components\TextInput::make('nip')
                ->label('NIP')
                ->maxLength(50)
                ->placeholder('Contoh: 198501012010121001'),
            Forms\Components\TextInput::make('jabatan')
                ->label('Jabatan')
                ->maxLength(100)
                ->placeholder('Contoh: Kabid Data dan Informasi'),
            Forms\Components\TextInput::make('no_hp')
                ->label('No. HP')
                ->tel()
                ->maxLength(20)
                ->placeholder('Contoh: 081234567890'),
            Forms\Components\FileUpload::make('foto')
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
            Forms\Components\Select::make('puskes_id')
                ->label('Puskesmas')
                ->options(fn () => \App\Models\FasilitasKesehatan::query()->where('status_operasional', true)->orderBy('nama')->pluck('nama', 'id'))
                ->searchable()
                ->visible(fn (callable $get): bool => in_array($get('role'), ['admin_puskes', 'operator_puskes'], true)),
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
            'admin_opd',
        ], true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->width(40)
                    ->height(40)
                    ->rounded(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->formatStateUsing(fn ($state) => User::find(1)?->getRoleLabelAttribute() ?? $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn () => Role::query()
                        ->whereIn('name', [
                            'super_admin', 'admin', 'admin_kominfo', 'admin_opd',
                            'admin_kecamatan', 'admin_desa',
                            'admin_kesehatan', 'operator_kesehatan',
                            'admin_pendidikan', 'operator_pendidikan',
                            'admin_pu', 'operator_pu',
                            'admin_sosial', 'operator_sosial',
                            'admin_ekonomi', 'operator_ekonomi',
                            'admin_kependudukan', 'operator_kependudukan',
                            'admin_lingkungan', 'operator_lingkungan',
                            'public',
                        ])
                        ->orderBy('name')
                        ->pluck('name', 'name'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, string $role) => $query->role($role))),
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('opd_id')
                    ->label('OPD')
                    ->options(fn () => Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
