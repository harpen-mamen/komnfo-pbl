<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutPageSettingResource\Pages;
use App\Models\AboutPageSetting;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class AboutPageSettingResource extends Resource
{
    protected static ?string $model = AboutPageSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Tentang Daerah';
    protected static ?string $navigationLabel = 'Pengaturan Halaman';
    protected static ?string $modelLabel = 'Pengaturan Halaman';
    protected static ?string $pluralModelLabel = 'Pengaturan Halaman';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Halaman')
                ->schema([
                    Forms\Components\TextInput::make('judul_halaman')
                        ->label('Judul Halaman')
                        ->required()
                        ->maxLength(200),
                    Forms\Components\TextInput::make('tagline')
                        ->label('Tagline')
                        ->maxLength(255)
                        ->helperText('Kalimat singkat di bawah judul.'),
                ]),
            Forms\Components\Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('banner_image')
                        ->label('Banner Hero')
                        ->image()
                        ->directory('tentang-daerah/banner')
                        ->imageEditor(),
                    Forms\Components\FileUpload::make('og_image')
                        ->label('Open Graph Image (share preview)')
                        ->image()
                        ->directory('tentang-daerah/og')
                        ->helperText('1200x630 px ideal untuk share Facebook/WhatsApp.'),
                ]),
            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Maks 160 karakter. Muncul di hasil Google.'),
                    Forms\Components\TextInput::make('meta_keywords')
                        ->label('Meta Keywords')
                        ->maxLength(255)
                        ->helperText('Pisahkan dengan koma.'),
                ]),
            Forms\Components\Hidden::make('updated_by')->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul_halaman')->label('Judul'),
                Tables\Columns\TextColumn::make('tagline')->label('Tagline')->limit(40),
                Tables\Columns\TextColumn::make('updated_at')->label('Update Terakhir')->dateTime('d M Y H:i'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutPageSettings::route('/'),
            'edit'  => Pages\EditAboutPageSetting::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo']) ?? false;
    }
}
