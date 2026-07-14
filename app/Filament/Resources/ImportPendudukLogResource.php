<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportPendudukLogResource\Pages;
use App\Models\ImportPendudukLog;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ImportPendudukLogResource extends Resource
{
    protected static ?string $model = ImportPendudukLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationLabel = 'Riwayat Import';
    protected static ?string $pluralModelLabel = 'Riwayat Import Gagal';
    protected static ?string $modelLabel = 'Import Gagal';
    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nomor_kk')->label('Nomor KK')->disabled(),
            Forms\Components\TextInput::make('nik')->label('NIK')->disabled(),
            Forms\Components\TextInput::make('nama')->label('Nama')->disabled(),
            Forms\Components\TextInput::make('baris')->label('Baris')->disabled(),
            Forms\Components\Textarea::make('alasan')->label('Alasan')->disabled()->columnSpanFull(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->visibleToCurrentUser()
            ->latest();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('baris')->label('Baris')->sortable(),
                Tables\Columns\TextColumn::make('nomor_kk')->label('Nomor KK')->searchable(),
                Tables\Columns\TextColumn::make('nik')->label('NIK')->searchable(),
                Tables\Columns\TextColumn::make('nama')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('alasan')->label('Alasan')->wrap()->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['sampai'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportPendudukLogs::route('/'),
            'view' => Pages\ViewImportPendudukLog::route('/{record}'),
        ];
    }
}
