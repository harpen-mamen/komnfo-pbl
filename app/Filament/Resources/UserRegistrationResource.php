<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use App\Filament\Resources\UserRegistrationResource\Pages\ListUserRegistrations;
use App\Filament\Resources\UserRegistrationResource\Pages\EditUserRegistration;
use App\Filament\Resources\UserRegistrationResource\Pages;
use App\Models\UserRegistration;
use App\Services\UserRegistrationApprovalService;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class UserRegistrationResource extends Resource
{
    protected static ?string $model = UserRegistration::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Verifikasi Registrasi';
    protected static ?string $pluralModelLabel = 'Verifikasi Registrasi';
    protected static ?string $modelLabel = 'Verifikasi Registrasi';
    protected static ?int $navigationSort = 9;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['kecamatan', 'desa', 'opd', 'verifier']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Akun')
                ->schema([
                    TextInput::make('name')->label('Nama Lengkap')->disabled(),
                    TextInput::make('email')->label('Email')->disabled(),
                    TextInput::make('nip')->label('NIP')->disabled(),
                    TextInput::make('no_hp')->label('No. HP')->disabled(),
                    TextInput::make('jabatan')->label('Jabatan')->disabled(),
                ])->columns(2),

            Section::make('Akses yang Diminta')
                ->schema([
                    TextInput::make('requested_role')->label('Role')->disabled(),
                ]),

            Section::make('Lokasi Kerja')
                ->schema([
                    TextInput::make('kecamatan.nama')->label('Kecamatan')->disabled(),
                    TextInput::make('desa.nama')->label('Desa')->disabled(),
                    TextInput::make('opd.nama')->label('OPD')->disabled(),
                ])->columns(3),

            Section::make('Status Verifikasi')
                ->schema([
                        Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'revision' => 'Perlu Revisi',
                        ])
                        ->required()
                        ->disabled(),
                    Textarea::make('admin_notes')
                        ->label('Catatan Admin')
                        ->placeholder('Masukkan catatan jika diperlukan...'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                BadgeColumn::make('requested_role')->label('Role Diminta')->sortable(),
                TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->sortable(),
                TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->sortable(),
                TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'revision',
                    ])
                    ->sortable(),
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('verifier.name')->label('Verifier')->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision' => 'Perlu Revisi',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->requiresConfirmation()
                    ->action(function (UserRegistration $record) {
                        app(UserRegistrationApprovalService::class)->approve($record, auth()->user());
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (UserRegistration $record, array $data) {
                        app(UserRegistrationApprovalService::class)->reject($record, $data['admin_notes'], auth()->user());
                    }),
                Action::make('revision')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Catatan Revisi')
                            ->required(),
                    ])
                    ->action(function (UserRegistration $record, array $data) {
                        app(UserRegistrationApprovalService::class)->requestRevision($record, $data['admin_notes'], auth()->user());
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('approve_selected')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if (in_array($record->status, ['pending', 'revision'], true)) {
                                app(UserRegistrationApprovalService::class)->approve($record, auth()->user());
                            }
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserRegistrations::route('/'),
            'edit' => EditUserRegistration::route('/{record}/edit'),
        ];
    }
}
