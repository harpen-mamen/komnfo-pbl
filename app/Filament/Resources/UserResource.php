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
        return parent::getEloquentQuery()->with(['roles', 'kecamatan', 'desa', 'opd']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canManageUsers();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canManageUsers();
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
                    ->whereIn('name', ['super_admin', 'admin_kominfo', 'admin_opd', 'admin_kecamatan', 'admin_desa', 'public', 'admin'])
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
                ->required(fn (callable $get): bool => $get('role') === 'admin_opd')
                ->visible(fn (callable $get): bool => $get('role') === 'admin_opd'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('roles.name')->label('Role'),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn () => Role::query()->whereIn('name', ['super_admin', 'admin_kominfo', 'admin_opd', 'admin_kecamatan', 'admin_desa', 'public', 'admin'])->orderBy('name')->pluck('name', 'name'))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] ?? null, fn (Builder $query, string $role) => $query->role($role))),
                Tables\Filters\SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id')),
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
