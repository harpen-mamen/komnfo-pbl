<?php

namespace App\Filament\Resources\RumahResource\RelationManagers;

use App\Models\Keluarga;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class KeluargasRelationManager extends RelationManager
{
    protected static string $relationship = 'keluarga';
    protected static ?string $recordTitleAttribute = 'nomor_kk';
    protected static ?string $title = 'Keluarga';
    protected static ?string $modelLabel = 'Keluarga';
    protected static ?string $pluralModelLabel = 'Keluarga';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nomor_kk')->label('Nomor KK')->required()->maxLength(32),
            Forms\Components\TextInput::make('nama_kepala_keluarga')->label('Nama Kepala Keluarga')->required()->maxLength(255),
            Forms\Components\TextInput::make('jumlah_anggota')->numeric()->default(1)->required(),
            Forms\Components\Select::make('status_ekonomi')->options(Keluarga::STATUS_EKONOMI)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_kk')->label('Nomor KK')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_kepala_keluarga')->label('Kepala Keluarga')->searchable(),
                Tables\Columns\TextColumn::make('jumlah_anggota')->label('Anggota'),
                Tables\Columns\BadgeColumn::make('status_ekonomi')->label('Status Ekonomi'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah KK'),
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
