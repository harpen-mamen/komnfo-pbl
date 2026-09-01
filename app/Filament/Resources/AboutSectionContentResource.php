<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutSectionContentResource\Pages;
use App\Models\AboutSectionContent;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class AboutSectionContentResource extends Resource
{
    protected static ?string $model = AboutSectionContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Konten Section';
    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Section')
                ->schema([
                    Forms\Components\TextInput::make('section_key')
                        ->label('Key Section')
                        ->required()
                        ->maxLength(50)
                        ->helperText('Huruf kecil, underscore. Contoh: sambutan_bupati, visi_misi, geografi, sejarah, lambang_daerah')
                        ->placeholder('sambutan_bupati'),
                    Forms\Components\TextInput::make('judul')->required()->maxLength(200),
                    Forms\Components\TextInput::make('subjudul')->maxLength(255),
                    Forms\Components\TextInput::make('icon')->placeholder('heroicon-o-heart')->maxLength(50),
                    Forms\Components\ColorPicker::make('warna_aksen')->label('Warna Aksen'),
                ])->columns(2),

            Forms\Components\Section::make('Konten')
                ->schema([
                    Forms\Components\Textarea::make('ringkasan')->rows(2)->label('Ringkasan (preview di menu)'),
                    Forms\Components\RichEditor::make('konten')->required()->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Publikasi')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('tampil_di_menu')->label('Tampil di Menu')->default(true),
                    Forms\Components\Select::make('status_publikasi')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')->label('Tanggal Publish'),
                ])->columns(4),

            Forms\Components\Hidden::make('updated_by')->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('section_key')->label('Key')->searchable()->badge(),
                Tables\Columns\TextColumn::make('judul')->label('Judul')->searchable()->wrap(),
                Tables\Columns\IconColumn::make('tampil_di_menu')->label('Menu')->boolean(),
                Tables\Columns\BadgeColumn::make('status_publikasi')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'gray' => 'archived',
                    ]),
                Tables\Columns\TextColumn::make('updated_at')->label('Update')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutSectionContents::route('/'),
            'create' => Pages\CreateAboutSectionContent::route('/create'),
            'edit'   => Pages\EditAboutSectionContent::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
