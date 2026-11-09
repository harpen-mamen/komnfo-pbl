<?php

namespace App\Filament\Resources\KeluargaResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Models\Penduduk;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class PenduduksRelationManager extends RelationManager
{
    protected static string $relationship = 'penduduk';
    protected static ?string $recordTitleAttribute = 'nama_lengkap';
    protected static ?string $title = 'Penduduk';
    protected static ?string $modelLabel = 'Penduduk';
    protected static ?string $pluralModelLabel = 'Penduduk';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nik')->required()->maxLength(32),
            TextInput::make('nama_lengkap')->label('Nama Lengkap')->required()->maxLength(255),
            Select::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN)->required(),
            TextInput::make('tempat_lahir')->required()->maxLength(255),
            DatePicker::make('tanggal_lahir')->required(),
            Select::make('agama')->options(Penduduk::AGAMA)->required(),
            Select::make('status_perkawinan')->options(Penduduk::STATUS_PERKAWINAN)->required(),
            Select::make('pendidikan')->options(Penduduk::PENDIDIKAN)->required(),
            TextInput::make('pekerjaan')->maxLength(255),
            TextInput::make('penghasilan')->numeric()->minValue(0),
            Select::make('status_dalam_keluarga')->options(Penduduk::STATUS_DALAM_KELUARGA)->required(),
            TextInput::make('nomor_hp')->tel()->maxLength(32),
            Select::make('status_penduduk')->options(Penduduk::STATUS_PENDUDUK)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik')->searchable()->sortable(),
                TextColumn::make('nama_lengkap')->label('Nama')->searchable(),
                TextColumn::make('jenis_kelamin')->label('JK'),
                TextColumn::make('pendidikan')->label('Pendidikan'),
                TextColumn::make('status_dalam_keluarga')->label('Status'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Penduduk'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
