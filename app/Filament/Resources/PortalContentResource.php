<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalContentResource\Pages\CreatePortalContent;
use App\Filament\Resources\PortalContentResource\Pages\EditPortalContent;
use App\Filament\Resources\PortalContentResource\Pages\ListPortalContents;
use App\Models\PortalContent;
use App\Support\PanelAccess;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PortalContentResource extends Resource
{
    protected static ?string $model = PortalContent::class;
    protected static ?string $slug = 'portal-contents';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-asia-australia';
    protected static string | \UnitEnum | null $navigationGroup = 'Portal Publik';
    protected static ?string $navigationLabel = 'Konten Website';
    protected static ?string $modelLabel = 'Konten Portal';
    protected static ?string $pluralModelLabel = 'Konten Website Publik';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        // Navigation is split into section-specific links in AdminPanelProvider.
        return false;
    }

    public static function canAccess(): bool
    {
        return PanelAccess::canManagePortal();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canManagePortal();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::canManagePortal();
    }

    public static function canEdit($record): bool
    {
        return PanelAccess::canManagePortal();
    }

    public static function canDelete($record): bool
    {
        return PanelAccess::canManagePortal();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Penempatan Konten')
                ->description('Konten hanya tampil bila status publikasi diaktifkan. Pilih tipe sesuai bagian website.')
                ->schema([
                    Select::make('type')->label('Bagian Website')->required()->default(fn () => request()->query('type'))->live()
                        ->disabled(fn ($record) => ! $record && request()->query('type') !== null)->options([
                        'hero' => 'Beranda: Banner utama',
                        'news' => 'Berita',
                        'service' => 'Layanan publik',
                        'page' => 'Halaman informasi',
                        'history' => 'Peristiwa sejarah',
                        'vision' => 'Visi, misi, tujuan, atau sasaran',
                        'official' => 'Profil pejabat',
                        'opd_profile' => 'Profil Dinas / OPD',
                    ]),
                TextInput::make('title')->label('Judul')->required()->maxLength(200)
                        ->default(fn () => match (request()->query('slug')) {
                            'tentang-daerah' => 'Tentang Daerah', 'sambutan-bupati' => 'Sambutan Bupati',
                            'geografi' => 'Geografi Sangihe', 'lambang-daerah' => 'Lambang Daerah', default => null,
                        })
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set, $record, callable $get) => ($record || $get('type') === 'page') ? null : $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')->label('Kode halaman / URL')->maxLength(220)->unique(ignoreRecord: true)->required()
                    ->visible(fn (callable $get) => $get('type') !== 'page')
                    ->helperText('URL berita dan layanan dibuat dari judul; kode lain dibuat otomatis dari judul.'),
                Select::make('slug')->label('Bagian Tentang Daerah')->required()->unique(ignoreRecord: true)
                    ->default(fn () => request()->query('slug'))
                    ->options([
                        'tentang-daerah' => 'Halaman utama Tentang Daerah',
                        'sambutan-bupati' => 'Sambutan Bupati',
                        'geografi' => 'Geografi',
                        'lambang-daerah' => 'Lambang Daerah',
                    ])->visible(fn (callable $get) => $get('type') === 'page')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $titles = ['tentang-daerah' => 'Tentang Daerah', 'sambutan-bupati' => 'Sambutan Bupati', 'geografi' => 'Geografi Sangihe', 'lambang-daerah' => 'Lambang Daerah'];
                        if ($state) $set('title', $titles[$state] ?? '');
                    }),
                Select::make('category')->label('Kategori / jenis')->options(fn (callable $get): array => match ($get('type')) {
                    'service' => ['document' => 'Dokumen', 'megaphone' => 'Pengaduan', 'database' => 'Data', 'chat' => 'Konsultasi'],
                    'vision' => ['visi' => 'Visi', 'misi' => 'Misi', 'tujuan' => 'Tujuan', 'sasaran' => 'Sasaran'],
                    'official' => ['bupati' => 'Bupati', 'wakil_bupati' => 'Wakil Bupati', 'sekda' => 'Sekretaris Daerah', 'asisten' => 'Asisten', 'staf_ahli' => 'Staf Ahli', 'kepala_dinas' => 'Kepala Dinas', 'lainnya' => 'Pejabat lainnya'],
                    'opd_profile' => ['dinas' => 'Dinas', 'badan' => 'Badan', 'kantor' => 'Kantor', 'sekretariat' => 'Sekretariat', 'rsud' => 'Rumah Sakit', 'lainnya' => 'Perangkat daerah lainnya'],
                    default => ['pemerintahan' => 'Pemerintahan', 'informasi' => 'Informasi', 'pengumuman' => 'Pengumuman', 'lainnya' => 'Lainnya'],
                })->searchable()->nullable()->required(fn (callable $get) => in_array($get('type'), ['vision', 'official', 'opd_profile'], true))
                    ->helperText('Pilih kategori dari daftar. Nilai ini digunakan untuk pengelompokan/ikon yang sudah disiapkan.'),
                    TextInput::make('year')->label(fn (callable $get) => $get('type') === 'vision' ? 'Periode Tahun' : 'Tahun Peristiwa')->numeric()->minValue(1800)->maxValue(2200)
                        ->visible(fn (callable $get) => in_array($get('type'), ['history', 'vision'], true)),
                    TextInput::make('sort_order')->label('Urutan tampil')->numeric()->default(0)->minValue(0),
                ])->columns(2),
            Section::make('Isi untuk Masyarakat')->schema([
                Textarea::make('summary')->label(fn (callable $get) => match ($get('type')) {
                    'news' => 'Ringkasan berita', 'hero' => 'Kalimat pengantar beranda', 'service' => 'Penjelasan layanan',
                    'official' => 'Jabatan', 'opd_profile' => 'Profil singkat perangkat daerah', default => 'Ringkasan halaman',
                })->rows(3)->maxLength(500)->visible(fn (callable $get) => ! in_array($get('type'), ['history', 'vision'], true))
                    ->helperText('Teks ini tampil sebagai pengantar atau ringkasan pada website publik.'),
                Textarea::make('body')->label(fn (callable $get) => match ($get('type')) {
                    'news' => 'Isi berita', 'page' => 'Isi halaman Tentang Daerah', 'history' => 'Kisah / keterangan peristiwa',
                    'vision' => 'Isi visi atau butir misi/tujuan/sasaran', 'hero' => 'Slogan / subjudul beranda',
                    'official' => 'Biografi singkat pejabat', 'opd_profile' => 'Tugas pokok dan fungsi', default => 'Isi',
                })->rows(12)->maxLength(60000)->required(fn (callable $get) => in_array($get('type'), ['news', 'page', 'history', 'vision', 'official', 'opd_profile'], true))
                    ->helperText('Teks biasa. Jangan masukkan NIK, data warga, catatan internal, atau HTML/kode.'),
                FileUpload::make('image_path')->label('Gambar')->image()->disk('public')->directory('portal')
                    ->imageEditor()->maxSize(5120)
                    ->helperText('Untuk banner, gambar ini menjadi poster dan tampilan cadangan jika video tidak tersedia.'),
                FileUpload::make('video_path')->label('Video banner')->disk('public')->directory('portal/videos')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(51200)
                    ->helperText('Opsional. MP4 atau WebM, maksimal 50 MB. Video akan diputar otomatis tanpa suara dan berulang di hero beranda.')
                    ->visible(fn (callable $get): bool => $get('type') === 'hero'),
            ]),
            Section::make('Informasi Kontak Perangkat Daerah')->schema([
                TextInput::make('abbreviation')->label('Singkatan')->maxLength(80),
                TextInput::make('address')->label('Alamat')->maxLength(500)->columnSpanFull(),
                TextInput::make('phone')->label('Telepon')->tel()->maxLength(80),
                TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                TextInput::make('website')->label('Website')->url()->maxLength(255),
            ])->columns(2)->visible(fn (callable $get) => $get('type') === 'opd_profile'),
            Section::make('Publikasi')->schema([
                Toggle::make('is_published')->label('Tampilkan di website')->default(false),
                DatePicker::make('published_at')->label('Tanggal terbit')->native(false)->visible(fn (callable $get) => (bool) $get('is_published')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->label('Bagian')->badge()->formatStateUsing(fn ($state) => match ($state) {
                'hero' => 'Banner Beranda', 'news' => 'Berita', 'service' => 'Layanan', 'page' => 'Halaman',
                'history' => 'Sejarah', 'vision' => 'Visi & Misi', 'official' => 'Profil Pejabat',
                'opd_profile' => 'Profil OPD', default => $state,
            }),
            TextColumn::make('title')->label('Judul')->searchable()->wrap(),
            TextColumn::make('category')->label('Kategori')->placeholder('-')->toggleable(),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
            TextColumn::make('published_at')->label('Terbit')->date('d M Y')->placeholder('Belum terbit')->sortable(),
            TextColumn::make('updated_at')->label('Diperbarui')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            SelectFilter::make('type')->label('Bagian Website')->options([
                'hero' => 'Banner utama', 'news' => 'Berita', 'service' => 'Layanan', 'page' => 'Halaman',
                'history' => 'Sejarah', 'vision' => 'Visi & Misi', 'official' => 'Profil Pejabat', 'opd_profile' => 'Profil OPD',
            ]),
            SelectFilter::make('slug')->label('Halaman Tentang')->options([
                'tentang-daerah' => 'Halaman utama Tentang Daerah',
                'sambutan-bupati' => 'Sambutan Bupati',
                'geografi' => 'Geografi',
                'lambang-daerah' => 'Lambang Daerah',
            ]),
            SelectFilter::make('is_published')->label('Status publikasi')->options([true => 'Tayang', false => 'Draft']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortalContents::route('/'),
            'create' => CreatePortalContent::route('/create'),
            'edit' => EditPortalContent::route('/{record}/edit'),
        ];
    }
}
