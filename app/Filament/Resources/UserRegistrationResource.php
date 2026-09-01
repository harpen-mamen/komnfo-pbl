<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRegistrationResource\Pages;
use App\Models\UserRegistration;
use App\Services\UserRegistrationApprovalService;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class UserRegistrationResource extends Resource
{
    protected static ?string $model = UserRegistration::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Verifikasi Registrasi';
    protected static ?string $pluralModelLabel = 'Verifikasi Registrasi';
    protected static ?string $modelLabel = 'Verifikasi Registrasi';
    protected static ?int $navigationSort = 9;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['kecamatan', 'desa', 'opd', 'verifier']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isKominfoKabupaten();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isKominfoKabupaten();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Akun')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nama Lengkap')->disabled(),
                    Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('nip')->label('NIP')->disabled(),
                    Forms\Components\TextInput::make('no_hp')->label('No. HP')->disabled(),
                    Forms\Components\TextInput::make('jabatan')->label('Jabatan')->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Akses yang Diminta')
                ->schema([
                    Forms\Components\TextInput::make('requested_role')->label('Role')->disabled(),
                ]),

            Forms\Components\Section::make('Lokasi Kerja')
                ->schema([
                    Forms\Components\TextInput::make('kecamatan.nama')->label('Kecamatan')->disabled(),
                    Forms\Components\TextInput::make('desa.nama')->label('Desa')->disabled(),
                    Forms\Components\TextInput::make('opd.nama')->label('OPD')->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Status Verifikasi')
                ->schema([
                        Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'revision' => 'Perlu Revisi',
                        ])
                        ->required()
                        ->disabled(),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Catatan Admin')
                        ->placeholder('Masukkan catatan jika diperlukan...'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('requested_role')->label('Role Diminta')->badge()->sortable(),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->placeholder('-')->sortable(),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->placeholder('-')->sortable(),
                Tables\Columns\TextColumn::make('opd.nama')->label('OPD')->placeholder('-')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'revision',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('verifier.name')->label('Verifier')->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision' => 'Perlu Revisi',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->requiresConfirmation()
                    ->action(function (UserRegistration $record) {
                        app(UserRegistrationApprovalService::class)->approve($record, auth()->user());
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (UserRegistration $record, array $data) {
                        app(UserRegistrationApprovalService::class)->reject($record, $data['admin_notes'], auth()->user());
                    }),
                Tables\Actions\Action::make('revision')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('warning')
                    ->visible(fn (UserRegistration $record) => in_array($record->status, ['pending', 'revision'], true))
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Revisi')
                            ->required(),
                    ])
                    ->action(function (UserRegistration $record, array $data) {
                        app(UserRegistrationApprovalService::class)->requestRevision($record, $data['admin_notes'], auth()->user());
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_selected')
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
            'index' => Pages\ListUserRegistrations::route('/'),
            'edit' => Pages\EditUserRegistration::route('/{record}/edit'),
        ];
    }
}
