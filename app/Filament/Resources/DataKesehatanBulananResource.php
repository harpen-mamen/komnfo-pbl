<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataKesehatanBulananResource\Pages;
use App\Models\DataKesehatanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PeriodePelaporan;
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

class DataKesehatanBulananResource extends Resource
{
    use HasPeriodOptions;
    protected static ?string $model = DataKesehatanBulanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Data Kesehatan Bulanan';
    protected static ?string $pluralModelLabel = 'Data Kesehatan Bulanan';
    protected static ?string $modelLabel = 'Data Kesehatan Bulanan';
    protected static ?string $recordTitleAttribute = 'puskesmas';
    protected static ?int $navigationSort = 16;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToCurrentUser()->with([
            'periodePelaporan',
            'kecamatan',
            'desa',
            'createdBy',
            'updatedBy',
            'latestSubmissionBatchItem.submissionBatch',
            'ibuHamils',
            'bayis',
            'tenagaKesehatans',
            'pelayananKesehatans',
            'kasusPenyakit'
        ]);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        // SPRINT 6: Data Kesehatan dihitung otomatis dari data penduduk
        // Tidak perlu input manual
        return false;
    }

    public static function canViewAny(): bool
    {
        // SPRINT 6: Tidak ada create manual - hanya lihat data hasil kalkulasi
        return false;
    }

    public static function canCreate(): bool
    {
        // SPRINT 6: Data Kesehatan dihitung otomatis
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        if (PanelAccess::isAdminKecamatan()) {
            return (int) $record->kecamatan_id === (int) PanelAccess::user()?->kecamatan_id
                && (bool) $record->periodePelaporan?->isOpenForInput();
        }

        if (PanelAccess::isOperatorPuskesmas()) {
            return (int) $record->created_by === (int) PanelAccess::user()?->id
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
                ->icon('heroicon-o-office-building')
                ->description('Identitas laporan kesehatan bulanan.')
                ->collapsible()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('periode_pelaporan_id')
                        ->label('Periode Pelaporan')
                        ->options(fn () => PeriodePelaporan::query()
                            ->when(PanelAccess::isOperatorPuskesmas(), fn (Builder $query) => $query->aktif())
                            ->orderByDesc('tahun')
                            ->orderByDesc('bulan')
                            ->pluck('nama_periode', 'id'))
                        ->searchable()
                        ->reactive()
                        ->required(),
                    Forms\Components\Select::make('periode_bulan')
                        ->label('Periode Bulan')
                        ->options(self::monthOptions())
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('puskesmas')
                        ->label('Nama Puskesmas')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('kecamatan_id')
                        ->label('Kecamatan')
                        ->options(fn () => Kecamatan::query()->orderBy('nama')->pluck('nama', 'id'))
                        ->default(fn () => PanelAccess::user()?->kecamatan_id)
                        ->disabled(fn (): bool => PanelAccess::isOperatorPuskesmas() || PanelAccess::isAdminKecamatan())
                        ->searchable()
                        ->reactive()
                        ->required(),
                    Forms\Components\Select::make('jenis')
                        ->label('Jenis')
                        ->options([
                            'Puskesmas' => 'Puskesmas',
                            'Pustu' => 'Pustu',
                            'Posyandu' => 'Posyandu',
                        ])
                        ->required(),
                ]),

            // Section Ringkasan Data - hanya menampilkan statistik
            Forms\Components\Section::make('Ringkasan Data Kesehatan')
                ->icon('heroicon-o-chart-bar')
                ->description('Ringkasan data yang telah diinput. Klik link untuk menambah data.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\Placeholder::make('ringkasan_data')
                        ->label('Ringkasan')
                        ->content(function (callable $get, $record) {
                            $html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';

                            // Stats dari relasi
                            $ibuHamilCount = $record?->ibuHamils()->count() ?? 0;
                            $bayiCount = $record?->bayis()->count() ?? 0;
                            $nakesCount = $record?->tenagaKesehatans()->count() ?? 0;
                            $pelayananCount = $record?->pelayananKesehatans()->count() ?? 0;
                            $kasusCount = $record?->kasusPenyakit()->count() ?? 0;

                            $html .= '<div class="bg-blue-50 p-4 rounded-lg"><div class="text-2xl font-bold text-blue-600">' . $ibuHamilCount . '</div><div class="text-sm text-gray-600">Ibu Hamil</div></div>';
                            $html .= '<div class="bg-green-50 p-4 rounded-lg"><div class="text-2xl font-bold text-green-600">' . $bayiCount . '</div><div class="text-sm text-gray-600">Bayi/Balita</div></div>';
                            $html .= '<div class="bg-purple-50 p-4 rounded-lg"><div class="text-2xl font-bold text-purple-600">' . $nakesCount . '</div><div class="text-sm text-gray-600">Tenaga Kesehatan</div></div>';
                            $html .= '<div class="bg-orange-50 p-4 rounded-lg"><div class="text-2xl font-bold text-orange-600">' . $pelayananCount . '</div><div class="text-sm text-gray-600">Pelayanan</div></div>';
                            $html .= '<div class="bg-red-50 p-4 rounded-lg"><div class="text-2xl font-bold text-red-600">' . $kasusCount . '</div><div class="text-sm text-gray-600">Kasus Penyakit</div></div>';

                            // Statistik tambahan
                            $ibuHamilAktif = $record?->ibuHamils()->where('status_kehamilan', 'Hamil')->count() ?? 0;
                            $ibuResikoTinggi = $record?->ibuHamils()->where('kategori_resiko', 'Resiko Tinggi')->count() ?? 0;
                            $bayiStunting = $record?->bayis()->where('kategori_balita', 'Stunting')->count() ?? 0;
                            $bayiGiziBuruk = $record?->bayis()->where('status_gizi', 'Buruk')->count() ?? 0;

                            $html .= '<div class="bg-pink-50 p-4 rounded-lg"><div class="text-2xl font-bold text-pink-600">' . $ibuHamilAktif . '</div><div class="text-sm text-gray-600">Ibu Hamil Aktif</div></div>';
                            $html .= '<div class="bg-yellow-50 p-4 rounded-lg"><div class="text-2xl font-bold text-yellow-600">' . $ibuResikoTinggi . '</div><div class="text-sm text-gray-600">Resiko Tinggi</div></div>';
                            $html .= '<div class="bg-indigo-50 p-4 rounded-lg"><div class="text-2xl font-bold text-indigo-600">' . $bayiStunting . '</div><div class="text-sm text-gray-600">Bayi Stunting</div></div>';
                            $html .= '<div class="bg-teal-50 p-4 rounded-lg"><div class="text-2xl font-bold text-teal-600">' . $bayiGiziBuruk . '</div><div class="text-sm text-gray-600">Gizi Buruk</div></div>';

                            $html .= '</div>';

                            $html .= '<div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">';
                            $html .= '<p class="font-semibold mb-2">Petunjuk Input Data:</p>';
                            $html .= '<ul class="list-disc list-inside space-y-1">';
                            $html .= '<li>Klik menu <strong>"Data Ibu Hamil"</strong> di sidebar untuk input data ibu hamil/bersalin</li>';
                            $html .= '<li>Klik menu <strong>"Data Bayi & Balita"</strong> untuk input data bayi dan balita</li>';
                            $html .= '<li>Klik menu <strong>"Data Tenaga Kesehatan"</strong> untuk input data tenaga kesehatan</li>';
                            $html .= '<li>Klik menu <strong>"Data Pelayanan Kesehatan"</strong> untuk input data pelayanan</li>';
                            $html .= '<li>Kasus penyakit sudah terintegrasi dengan data penduduk</li>';
                            $html .= '</ul>';
                            $html .= '</div>';

                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Kasus Penyakit')
                ->icon('heroicon-o-beaker')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\CheckboxList::make('kasus_penyakit')
                        ->label('Kasus Penyakit (ceklis jika ada)')
                        ->options([
                            'ISPA' => 'ISPA',
                            'DBD' => 'DBD',
                            'Malaria' => 'Malaria',
                            'TBC' => 'TBC',
                            'Hipertensi' => 'Hipertensi',
                            'Diabetes' => 'Diabetes',
                            'Covid' => 'Covid',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->columns(2),
                    Forms\Components\Placeholder::make('kasus_detail_info')
                        ->label('Input Detail Kasus')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<div class="text-sm text-gray-600">Klik menu <strong>"Data Kasus Penyakit"</strong> di sidebar untuk input detail kasus per penduduk.</div>'
                        ))
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Statistik KIA')
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('ibu_hamil')->label('Ibu Hamil')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('balita')->label('Balita')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('bayi')->label('Bayi (0-11 bulan)')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('stunting')->label('Stunting')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('gizi_buruk')->label('Gizi Buruk')->numeric()->minValue(0)->default(0),
                ]),

            Forms\Components\Section::make('Pelayanan Kesehatan')
                ->icon('heroicon-o-plus-circle')
                ->collapsible()
                ->collapsed()
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('kunjungan')->label('Total Kunjungan')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('rawat_jalan')->label('Rawat Jalan')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('rawat_inap')->label('Rawat Inap')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('rujukan')->label('Rujukan')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('persalinan')->label('Persalinan')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('imunisasi')->label('Imunisasi')->numeric()->minValue(0)->default(0),
                ]),

            Forms\Components\Section::make('Statistik Penyakit')
                ->icon('heroicon-o-beaker')
                ->collapsible()
                ->collapsed()
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('ispa')->label('ISPA')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('dbd')->label('DBD')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('malaria')->label('Malaria')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('tbc')->label('TBC')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('hipertensi')->label('Hipertensi')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('diabetes')->label('Diabetes')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('covid')->label('Covid')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('lainnya')->label('Lainnya')->numeric()->minValue(0)->default(0),
                ]),

            Forms\Components\Section::make('Statistik SDM')
                ->icon('heroicon-o-user-group')
                ->collapsible()
                ->collapsed()
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('dokter')->label('Dokter')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('perawat')->label('Perawat')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('bidan')->label('Bidan')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('apoteker')->label('Apoteker')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('ahli_gizi')->label('Ahli Gizi')->numeric()->minValue(0)->default(0),
                ]),

            Forms\Components\Section::make('Sarana')
                ->icon('heroicon-o-office-building')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\Checkbox::make('ambulans')->label('Ambulans'),
                            Forms\Components\Checkbox::make('laboratorium')->label('Laboratorium'),
                            Forms\Components\Checkbox::make('apotek')->label('Apotek'),
                            Forms\Components\Checkbox::make('ruang_bersalin')->label('Ruang Bersalin'),
                            Forms\Components\Checkbox::make('internet')->label('Internet'),
                            Forms\Components\Checkbox::make('listrik')->label('Listrik'),
                            Forms\Components\Checkbox::make('air_bersih')->label('Air Bersih'),
                        ]),
                ]),

            Forms\Components\Section::make('Obat')
                ->icon('heroicon-o-cube')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\Radio::make('status_obat')
                        ->label('Status Obat')
                        ->options([
                            'Lengkap' => 'Lengkap',
                            'Cukup' => 'Cukup',
                            'Kurang' => 'Kurang',
                            'Kosong' => 'Kosong',
                        ])
                        ->required(),
                ]),

            Forms\Components\Section::make('Data Tambahan')
                ->icon('heroicon-o-document-report')
                ->collapsible()
                ->collapsed()
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('kematian')->label('Kematian')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('kelahiran')->label('Kelahiran')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('disabilitas')->label('Disabilitas')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('bpjs_aktif')->label('BPJS Aktif')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('bpjs_tidak_aktif')->label('BPJS Tidak Aktif')->numeric()->minValue(0)->default(0),
                ]),

            Forms\Components\Section::make('Permasalahan')
                ->icon('heroicon-o-exclamation-circle')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\CheckboxList::make('permasalahan')
                        ->label('Permasalahan')
                        ->options([
                            'Kurang Dokter' => 'Kurang Dokter',
                            'Kurang Bidan' => 'Kurang Bidan',
                            'Kurang Obat' => 'Kurang Obat',
                            'Gedung Rusak' => 'Gedung Rusak',
                            'Ambulans Rusak' => 'Ambulans Rusak',
                            'Internet' => 'Internet',
                            'Listrik' => 'Listrik',
                            'Air' => 'Air',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->columns(2),
                    Forms\Components\Textarea::make('uraian_permasalahan')
                        ->label('Uraian Permasalahan')
                        ->rows(4)
                        ->columnSpanFull()
                        ->visible(fn (callable $get) => in_array('Lainnya', $get('permasalahan') ?? [], true)),
                ]),

            Forms\Components\Section::make('Catatan')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Catatan')
                        ->rows(6)
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
                Tables\Columns\TextColumn::make('puskesmas')->label('Puskesmas')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kecamatan.nama')->label('Kecamatan')->searchable(),
                Tables\Columns\TextColumn::make('ibu_hamil')->label('Ibu Hamil')->sortable(),
                Tables\Columns\TextColumn::make('balita')->label('Balita')->sortable(),
                Tables\Columns\TextColumn::make('bayi')->label('Bayi')->sortable(),
                Tables\Columns\TextColumn::make('stunting')->label('Stunting')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('gizi_buruk')->label('Gizi Buruk')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('dokter')->label('Dokter')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('perawat')->label('Perawat')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('bidan')->label('Bidan')->sortable()->toggleable(),
                Tables\Columns\BadgeColumn::make('status_obat')->label('Status Obat')->colors([
                    'success' => 'Lengkap',
                    'warning' => 'Cukup',
                    'danger' => 'Kurang',
                    'gray' => 'Kosong',
                ])->toggleable(),
                Tables\Columns\TextColumn::make('ibuHamils_count')->label('Detail IBU Hamil')->getStateUsing(fn ($record) => $record->ibuHamils()->count())->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('bayis_count')->label('Detail Bayi')->getStateUsing(fn ($record) => $record->bayis()->count())->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('tenagaKesehatans_count')->label('Detail Nakes')->getStateUsing(fn ($record) => $record->tenagaKesehatans()->count())->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('kasusPenyakit_count')->label('Detail Kasus')->getStateUsing(fn ($record) => $record->kasusPenyakit()->count())->sortable()->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('createdBy.name')->label('Created By')->toggleable(),
                Tables\Columns\TextColumn::make('updatedBy.name')->label('Updated By')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime('d M Y H:i')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters(self::tableFilters())
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')->label('Export PDF')->icon('heroicon-o-document-download')->url(route('laporan.kesehatan-bulanan.pdf'))->openUrlInNewTab(),
                Tables\Actions\Action::make('export_excel')->label('Export Excel')->icon('heroicon-o-table')->url(route('laporan.kesehatan-bulanan.excel'))->openUrlInNewTab(),
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

    public static function tableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('periode_pelaporan_id')->label('Periode Pelaporan')->options(fn () => PeriodePelaporan::query()->orderByDesc('tahun')->orderByDesc('bulan')->pluck('nama_periode', 'id')),
            Tables\Filters\SelectFilter::make('periode_bulan')->label('Periode Bulan')->options(DataPendidikanBulananResource::monthOptions()),
            Tables\Filters\SelectFilter::make('periode_tahun')->label('Periode Tahun')->options(DataPendidikanBulananResource::yearOptions()),
            Tables\Filters\SelectFilter::make('kecamatan_id')->label('Kecamatan')->options(fn () => Kecamatan::query()
                ->when(PanelAccess::isOperatorPuskesmas(), fn (Builder $query) => $query->whereKey(PanelAccess::user()?->kecamatan_id))
                ->orderBy('nama')
                ->pluck('nama', 'id')),
            Tables\Filters\SelectFilter::make('desa_id')->label('Desa')->options(fn () => Desa::query()
                ->when(PanelAccess::isOperatorPuskesmas(), fn (Builder $query) => $query->where('kecamatan_id', PanelAccess::user()?->kecamatan_id))
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
            Tables\Filters\Filter::make('puskesmas')
                ->form([Forms\Components\TextInput::make('puskesmas')->label('Puskesmas')])
                ->query(fn (Builder $query, array $data) => $query->when($data['puskesmas'] ?? null, fn (Builder $query, string $value) => $query->where('puskesmas', 'like', "%{$value}%"))),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['puskesmas', 'periodePelaporan.nama_periode', 'kecamatan.nama', 'desa.nama'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->visibleToCurrentUser()->with(['periodePelaporan', 'kecamatan', 'desa']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataKesehatanBulanans::route('/'),
            'create' => Pages\CreateDataKesehatanBulanan::route('/create'),
            'view' => Pages\ViewDataKesehatanBulanan::route('/{record}'),
            'edit' => Pages\EditDataKesehatanBulanan::route('/{record}/edit'),
        ];
    }
}
