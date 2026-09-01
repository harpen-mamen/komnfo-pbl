<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPendidikanBulananResource\Pages;
use App\Models\DataPendidikanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PeriodePelaporan;
use App\Models\DataPendidikanBulanan as DataPendidikanBulananModel;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use App\Filament\Resources\Concerns\HasPeriodOptions;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DataPendidikanBulananResource extends Resource
{
    use HasPeriodOptions;
    protected static ?string $model = DataPendidikanBulanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Data Pendidikan Semester';
    protected static ?string $pluralModelLabel = 'Data Pendidikan Semester';
    protected static ?string $modelLabel = 'Data Pendidikan Semester';
    protected static ?string $recordTitleAttribute = 'sekolah';
    protected static ?int $navigationSort = 15;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with(['periodePelaporan', 'kecamatan', 'desa', 'createdBy', 'updatedBy', 'latestSubmissionBatchItem.submissionBatch']);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan();
    }

    public static function canViewAny(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isAdminKecamatan();
    }

    public static function canCreate(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public static function canEdit(Model $record): bool
    {
        if (PanelAccess::isAdminKecamatan()) {
            return (int) $record->kecamatan_id === (int) PanelAccess::user()?->kecamatan_id
                && (bool) $record->periodePelaporan?->isOpenForInput();
        }

        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return self::canEdit($record);
    }

    public static function canDeleteAny(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Laporan')
                ->icon('heroicon-o-book-open')
                ->description('Lengkapi identitas laporan pendidikan semester dengan cepat.')
                ->collapsible()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('tahun_ajaran')
                        ->label('Tahun Ajaran')
                        ->placeholder('Contoh: 2026/2027')
                        ->helperText('Format: Tahun Awal/Tahun Akhir.')
                        ->required()
                        ->maxLength(9)
                        ->rules(['required', 'regex:/^\\d{4}\\/\\d{4}$/'])
                        ->reactive()
                        ->afterStateHydrated(function ($component, $state, ?DataPendidikanBulananModel $record): void {
                            if ($record?->periodePelaporan?->nama_periode) {
                                // try to extract YYYY/YYYY from existing nama_periode
                                if (preg_match('/(\\d{4}\\/\\d{4})/', $record->periodePelaporan->nama_periode, $m)) {
                                    $component->state($m[1]);
                                } elseif (preg_match('/(\\d{4}\\/\\d{4})/', $record->periodePelaporan->nama_periode)) {
                                    $component->state($record->periodePelaporan->nama_periode);
                                }
                            }
                        }),
                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options([
                            'ganjil' => 'Semester Ganjil',
                            'genap' => 'Semester Genap',
                        ])
                        ->default('ganjil')
                        ->reactive()
                        ->required(),
                    Forms\Components\Select::make('kecamatan_id')
                        ->label('Kecamatan')
                        ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                        ->default(fn () => PanelAccess::user()?->kecamatan_id)
                        ->disabled(fn (): bool => true)
                        ->dehydrated()
                        ->searchable()
                        ->reactive()
                        ->required(),
                    Forms\Components\Select::make('desa_id')
                        ->label('Desa')
                        ->options(fn (callable $get) => Desa::query()
                            ->when($get('kecamatan_id') ?? PanelAccess::user()?->kecamatan_id, fn (Builder $query, $kecamatanId) => $query->where('kecamatan_id', $kecamatanId))
                            ->orderBy('nama')
                            ->pluck('nama', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('sekolah')
                        ->label('Nama Sekolah')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: SD Negeri 1 Tahuna'),
                    Forms\Components\TextInput::make('npsn')
                        ->label('NPSN')
                        ->placeholder('Contoh: 12345678')
                        ->helperText('Opsional, hanya untuk mempercepat identifikasi sekolah.')
                        ->dehydrated(false),
                    Forms\Components\Select::make('status_sekolah')
                        ->label('Status Sekolah')
                        ->options([
                            'Negeri' => 'Negeri',
                            'Swasta' => 'Swasta',
                        ])
                        ->dehydrated(false)
                        ->required(),
                    Forms\Components\Select::make('jenjang')
                        ->label('Jenjang')
                        ->options([
                            'SD' => 'SD',
                            'SMP' => 'SMP',
                            'SMA' => 'SMA',
                            'SMK' => 'SMK',
                        ])
                        ->dehydrated(false)
                        ->required(),
                ]),

            Forms\Components\Section::make('Peserta Didik')
                ->icon('heroicon-o-academic-cap')
                ->description('Masukkan data peserta didik dengan total otomatis.')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('rombel')
                                ->label('Rombel')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('laki_laki')
                                ->label('Siswa Laki-laki')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Forms\Components\TextInput::make('perempuan')
                                ->label('Siswa Perempuan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Forms\Components\Placeholder::make('total_siswa')
                                ->label('Total Siswa')
                                ->content(fn (callable $get) => (int) $get('laki_laki') + (int) $get('perempuan'))
                                ->helperText('Jumlah siswa dihitung otomatis.'),
                            Forms\Components\TextInput::make('siswa_baru')
                                ->label('Siswa Baru')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('siswa_lulus')
                                ->label('Siswa Lulus')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('siswa_pindah')
                                ->label('Siswa Pindah')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('putus_sekolah')
                                ->label('Putus Sekolah')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Forms\Components\TextInput::make('abk')
                                ->label('ABK')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                        ]),
                ]),

            Forms\Components\Section::make('Tenaga Pendidik')
                ->icon('heroicon-o-user-group')
                ->description('Data tenaga pendidik dan staf sekolah.')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('guru_asn')
                                ->label('Guru ASN')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('guru_pppk')
                                ->label('Guru PPPK')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('guru_honorer')
                                ->label('Guru Honorer')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('tenaga_administrasi')
                                ->label('Tenaga Administrasi')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('kepala_sekolah')
                                ->label('Kepala Sekolah')
                                ->numeric()
                                ->minValue(0)
                                ->default(1)
                                ->dehydrated(false),
                            Forms\Components\Placeholder::make('total_guru')
                                ->label('Total Guru')
                                ->content(fn (callable $get) => (int) $get('guru_asn') + (int) $get('guru_pppk') + (int) $get('guru_honorer'))
                                ->helperText('Total guru dihitung otomatis.'),
                        ]),
                ]),

            Forms\Components\Section::make('Sarana Prasarana')
                ->icon('heroicon-o-office-building')
                ->description('Tandai infrastruktur sekolah yang tersedia.')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Toggle::make('internet')
                                ->label('Internet')
                                ->dehydrated(false),
                            Forms\Components\Toggle::make('listrik')
                                ->label('Listrik')
                                ->dehydrated(false),
                            Forms\Components\Toggle::make('air_bersih')
                                ->label('Air Bersih')
                                ->dehydrated(false),
                            Forms\Components\Toggle::make('perpustakaan')
                                ->label('Perpustakaan')
                                ->dehydrated(false),
                            Forms\Components\Toggle::make('laboratorium')
                                ->label('Laboratorium')
                                ->dehydrated(false),
                            Forms\Components\Toggle::make('uks')
                                ->label('UKS')
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('jumlah_ruang')
                                ->label('Jumlah Ruang')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('rusak_ringan')
                                ->label('Rusak Ringan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('rusak_berat')
                                ->label('Rusak Berat')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->dehydrated(false),
                        ]),
                ]),

            Forms\Components\Section::make('Program Pendidikan')
                ->description('Pilih program aktif yang berlaku di sekolah.')
                ->collapsible()
                ->schema([
                    Forms\Components\CheckboxList::make('program_pendidikan')
                        ->label('Program Terdaftar')
                        ->options([
                            'PIP' => 'PIP',
                            'KIP' => 'KIP',
                            'BOS' => 'BOS',
                            'Literasi' => 'Literasi',
                            'Digitalisasi' => 'Digitalisasi',
                            'Ekstrakurikuler' => 'Ekstrakurikuler',
                        ])
                        ->columns(2)
                        ->dehydrated(false),
                ]),

            Forms\Components\Section::make('Prestasi')
                ->icon('heroicon-o-check')
                ->description('Tandai capaian akademik dan non-akademik.')
                ->collapsible()
                ->schema([
                    Forms\Components\CheckboxList::make('prestasi')
                        ->label('Prestasi Sekolah')
                        ->options([
                            'Akademik' => 'Akademik',
                            'Non Akademik' => 'Non Akademik',
                            'Kabupaten' => 'Kabupaten',
                            'Provinsi' => 'Provinsi',
                            'Nasional' => 'Nasional',
                        ])
                        ->columns(2)
                        ->dehydrated(false),
                ]),

            Forms\Components\Section::make('Permasalahan')
                ->icon('heroicon-o-exclamation-circle')
                ->description('Tandai tantangan utama sekolah dan beri uraian jika perlu.')
                ->collapsible()
                ->schema([
                    Forms\Components\CheckboxList::make('permasalahan')
                        ->label('Permasalahan')
                        ->options([
                            'Kekurangan Guru' => 'Kekurangan Guru',
                            'Kekurangan Buku' => 'Kekurangan Buku',
                            'Internet' => 'Internet',
                            'Listrik' => 'Listrik',
                            'Bangunan Rusak' => 'Bangunan Rusak',
                            'Air Bersih' => 'Air Bersih',
                            'Komputer' => 'Komputer',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->columns(2)
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('uraian_permasalahan')
                        ->label('Uraian Permasalahan')
                        ->rows(3)
                        ->columnSpanFull()
                        ->visible(fn (callable $get) => in_array('Lainnya', $get('permasalahan') ?? [], true))
                        ->dehydrated(false),
                ]),

            Forms\Components\Section::make('Catatan')
                ->description('Catatan tambahan ditujukan untuk evaluasi dan tindak lanjut.')
                ->collapsible()
                ->schema([
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Catatan')
                        ->rows(6)
                        ->columnSpanFull()
                        ->placeholder('Tulis catatan penting yang perlu diketahui pemangku kepentingan.'),
                ]),

            Forms\Components\Section::make('Ringkasan Otomatis')
                ->icon('heroicon-o-chart-bar')
                ->description('Ringkasan cepat tidak tersimpan ke database.')
                ->collapsible()
                ->schema([
                    Forms\Components\Placeholder::make('ringkasan')
                        ->label('Ringkasan Sekolah')
                        ->content(fn (callable $get) => new \Illuminate\Support\HtmlString(
                            '<div class="ringkasan-container">'
                            .'<div class="ringkasan-card"><strong>Sekolah</strong><div>' . e($get('sekolah') ?? '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Semester</strong><div>' . e($get('semester') ?? '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Total Murid</strong><div>' . ((int) $get('laki_laki') + (int) $get('perempuan')) . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Total Guru</strong><div>' . ((int) $get('guru_asn') + (int) $get('guru_pppk') + (int) $get('guru_honorer')) . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Rasio Guru : Murid</strong><div>' . (((int) $get('laki_laki') + (int) $get('perempuan')) > 0 ? number_format(((int) $get('guru_asn') + (int) $get('guru_pppk') + (int) $get('guru_honorer')) / ((int) $get('laki_laki') + (int) $get('perempuan')), 2) : '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Status Sarana</strong><div>' . (implode(', ', array_filter([
                                    $get('internet') ? 'Internet' : null,
                                    $get('listrik') ? 'Listrik' : null,
                                    $get('air_bersih') ? 'Air Bersih' : null,
                                    $get('perpustakaan') ? 'Perpustakaan' : null,
                                    $get('laboratorium') ? 'Laboratorium' : null,
                                    $get('uks') ? 'UKS' : null,
                                ])) ?: '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Jumlah Permasalahan</strong><div>' . count($get('permasalahan') ?? []) . '</div></div>'
                            .'</div>'
                        ))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodePelaporan.nama_periode')->label('Periode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('periode_bulan')->label('Bulan')->formatStateUsing(fn ($state) => self::monthOptions()[(int) $state] ?? $state)->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('periode_tahun')->label('Tahun')->sortable()->toggleable(),
                Tables\Columns\BadgeColumn::make('latestSubmissionBatchItem.submissionBatch.status')->label('Batch')->formatStateUsing(fn ($state) => SubmissionBatch::statusLabel($state))->colors([
                    'gray' => SubmissionBatch::STATUS_DRAFT,
                    'warning' => SubmissionBatch::STATUS_PENDING,
                    'primary' => SubmissionBatch::STATUS_IN_REVIEW,
                    'success' => SubmissionBatch::STATUS_APPROVED,
                    'danger' => SubmissionBatch::STATUS_REJECTED,
                ]),
                Tables\Columns\TextColumn::make('sekolah')->label('Sekolah')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable(),
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->searchable(),
                Tables\Columns\TextColumn::make('jumlah_siswa')->label('Siswa')->sortable(),
                Tables\Columns\TextColumn::make('laki_laki')->label('L')->sortable(),
                Tables\Columns\TextColumn::make('perempuan')->label('P')->sortable(),
                Tables\Columns\TextColumn::make('putus_sekolah')->label('Putus')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('createdBy.name')->label('Created By')->toggleable(),
                Tables\Columns\TextColumn::make('updatedBy.name')->label('Updated By')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime('d M Y H:i')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters(self::tableFilters('sekolah'))
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-download')->url(route('laporan.pendidikan-bulanan.pdf'))->openUrlInNewTab(),
                Tables\Actions\Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table')->url(route('laporan.pendidikan-bulanan.excel'))->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function tableFilters(string $facilityField): array
    {
        return [
            Tables\Filters\SelectFilter::make('periode_pelaporan_id')->label('Periode Pelaporan')->options(fn () => PeriodePelaporan::query()->orderByDesc('tahun')->orderByDesc('bulan')->pluck('nama_periode', 'id')),
            Tables\Filters\SelectFilter::make('periode_bulan')->label('Periode Bulan')->options(self::monthOptions()),
            Tables\Filters\SelectFilter::make('semester')->label('Semester')->options([
                'ganjil' => 'Semester Ganjil',
                'genap' => 'Semester Genap',
            ]),
            Tables\Filters\SelectFilter::make('periode_tahun')->label('Periode Tahun')->options(self::yearOptions()),
            Tables\Filters\SelectFilter::make('kecamatan_id')->label('Kecamatan')->options(fn () => Kecamatan::query()
                ->when(PanelAccess::isOperatorSekolah(), fn (Builder $query) => $query->whereKey(PanelAccess::user()?->kecamatan_id))
                ->orderBy('nama')
                ->pluck('nama', 'id')),
            Tables\Filters\SelectFilter::make('desa_id')->label('Desa')->options(fn () => Desa::query()
                ->when(PanelAccess::isOperatorSekolah(), fn (Builder $query) => $query->where('kecamatan_id', PanelAccess::user()?->kecamatan_id))
                ->orderBy('nama')
                ->pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('batch_status')
                ->label('Status Batch')
                ->options(fn () => collect([
                    SubmissionBatch::STATUS_DRAFT => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_DRAFT),
                    SubmissionBatch::STATUS_PENDING => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                    SubmissionBatch::STATUS_IN_REVIEW => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_IN_REVIEW),
                    SubmissionBatch::STATUS_REVISION => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REVISION),
                    SubmissionBatch::STATUS_APPROVED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_APPROVED),
                    SubmissionBatch::STATUS_REJECTED => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_REJECTED),
                ])->all())
                ->query(fn (Builder $query, array $data) => $query
                    ->when($data['value'] ?? null, fn (Builder $query, string $value) => $query
                        ->withLatestSubmissionBatchStatus($value))),
            Tables\Filters\Filter::make($facilityField)
                ->form([Forms\Components\TextInput::make($facilityField)->label('Sekolah')])
                ->query(fn (Builder $query, array $data) => $query->when($data[$facilityField] ?? null, fn (Builder $query, string $value) => $query->where($facilityField, 'like', "%{$value}%"))),
        ];
    }


    

    public static function getGloballySearchableAttributes(): array
    {
        return ['sekolah', 'periodePelaporan.nama_periode', 'kecamatan.nama', 'desa.nama'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->visibleToCurrentUser()->with(['periodePelaporan', 'kecamatan', 'desa']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPendidikanBulanans::route('/'),
            'create' => Pages\CreateDataPendidikanBulanan::route('/create'),
            'view' => Pages\ViewDataPendidikanBulanan::route('/{record}'),
            'edit' => Pages\EditDataPendidikanBulanan::route('/{record}/edit'),
        ];
    }
}
