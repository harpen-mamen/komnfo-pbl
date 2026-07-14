<?php

namespace App\Filament\Resources\KeluargaResource\RelationManagers;

use App\Models\Penduduk;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class PenduduksRelationManager extends RelationManager
{
    protected static string $relationship = 'penduduk';
    protected static ?string $recordTitleAttribute = 'nama_lengkap';
    protected static ?string $title = 'Penduduk';
    protected static ?string $modelLabel = 'Penduduk';
    protected static ?string $pluralModelLabel = 'Penduduk';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nik')->required()->maxLength(32),
            Forms\Components\TextInput::make('nama_lengkap')->label('Nama Lengkap')->required()->maxLength(255),
            Forms\Components\Select::make('jenis_kelamin')->options(Penduduk::JENIS_KELAMIN)->required(),
            Forms\Components\TextInput::make('tempat_lahir')->required()->maxLength(255),
            Forms\Components\DatePicker::make('tanggal_lahir')->required(),
            Forms\Components\Select::make('agama')->options(Penduduk::AGAMA)->required(),
            Forms\Components\Select::make('status_perkawinan')->options(Penduduk::STATUS_PERKAWINAN)->required(),
            Forms\Components\Select::make('pendidikan')->options(Penduduk::PENDIDIKAN)->required(),
            Forms\Components\TextInput::make('pekerjaan')->maxLength(255),
            Forms\Components\TextInput::make('penghasilan')->numeric()->minValue(0),
            Forms\Components\Select::make('status_dalam_keluarga')->options(Penduduk::STATUS_DALAM_KELUARGA)->required(),
            Forms\Components\TextInput::make('nomor_hp')->tel()->maxLength(32),
            Forms\Components\Select::make('status_penduduk')->options(Penduduk::STATUS_PENDUDUK)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')->label('JK'),
                Tables\Columns\TextColumn::make('pendidikan')->label('Pendidikan'),
                Tables\Columns\TextColumn::make('status_dalam_keluarga')->label('Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Penduduk'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
