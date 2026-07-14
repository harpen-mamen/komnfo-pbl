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
                    Forms\Components\Select::make('desa_id')
                        ->label('Desa')
                        ->options(fn (callable $get) => Desa::query()
                            ->when(PanelAccess::isOperatorPuskesmas(), fn (Builder $query) => $query->where('kecamatan_id', PanelAccess::user()?->kecamatan_id))
                            ->when(! PanelAccess::isOperatorPuskesmas() && $get('kecamatan_id'), fn (Builder $query, $kecamatanId) => $query->where('kecamatan_id', $kecamatanId))
                            ->orderBy('nama')
                            ->pluck('nama', 'id'))
                        ->searchable()
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

            Forms\Components\Section::make('Pelayanan')
                ->icon('heroicon-o-clipboard-list')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('kunjungan')->label('Kunjungan')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('rawat_jalan')->label('Rawat Jalan')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('rawat_inap')->label('Rawat Inap')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('rujukan')->label('Rujukan')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('persalinan')->label('Persalinan')->numeric()->minValue(0)->default(0),
                        ]),
                ]),

            Forms\Components\Section::make('Kesehatan Ibu & Anak')
                ->icon('heroicon-o-user-group')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('ibu_hamil')->label('Ibu Hamil')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('balita')->label('Balita')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('bayi')->label('Bayi')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('imunisasi')->label('Imunisasi')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('stunting')->label('Stunting')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('gizi_buruk')->label('Gizi Buruk')->numeric()->minValue(0)->default(0),
                        ]),
                ]),

            Forms\Components\Section::make('Kasus Penyakit')
                ->icon('heroicon-o-beaker')
                ->collapsible()
                ->schema([
                    Forms\Components\CheckboxList::make('kasus_penyakit')
                        ->label('Kasus Penyakit')
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
                ]),

            Forms\Components\Section::make('Tenaga Kesehatan')
                ->icon('heroicon-o-user')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('dokter')->label('Dokter')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('perawat')->label('Perawat')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('bidan')->label('Bidan')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('apoteker')->label('Apoteker')->numeric()->minValue(0)->default(0),
                            Forms\Components\TextInput::make('ahli_gizi')->label('Ahli Gizi')->numeric()->minValue(0)->default(0),
                        ]),
                ]),

            Forms\Components\Section::make('Sarana')
                ->icon('heroicon-o-office-building')
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Toggle::make('ambulans')->label('Ambulans')->dehydrated(false),
                            Forms\Components\Toggle::make('laboratorium')->label('Laboratorium')->dehydrated(false),
                            Forms\Components\Toggle::make('apotek')->label('Apotek')->dehydrated(false),
                            Forms\Components\Toggle::make('ruang_bersalin')->label('Ruang Bersalin')->dehydrated(false),
                            Forms\Components\Toggle::make('internet')->label('Internet')->dehydrated(false),
                            Forms\Components\Toggle::make('listrik')->label('Listrik')->dehydrated(false),
                            Forms\Components\Toggle::make('air_bersih')->label('Air Bersih')->dehydrated(false),
                        ]),
                ]),

            Forms\Components\Section::make('Obat')
                ->icon('heroicon-o-cube')
                ->collapsible()
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

            Forms\Components\Section::make('Permasalahan')
                ->icon('heroicon-o-exclamation-circle')
                ->collapsible()
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
                ->schema([
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Catatan')
                        ->rows(6)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Ringkasan Otomatis')
                ->icon('heroicon-o-chart-bar')
                ->description('Ringkasan cepat tidak tersimpan ke database.')
                ->collapsible()
                ->schema([
                    Forms\Components\Placeholder::make('ringkasan')
                        ->label('Ringkasan')
                        ->content(fn (callable $get) =>
                            '<div class="ringkasan-container">'
                            .'<div class="ringkasan-card"><strong>Puskesmas</strong><div>' . ($get('puskesmas') ?? '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Total Pelayanan</strong><div>' . ((int) ($get('kunjungan') ?? 0) + (int) ($get('rawat_jalan') ?? 0) + (int) ($get('rawat_inap') ?? 0)) . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Jumlah SDM</strong><div>' . ((int) ($get('dokter') ?? 0) + (int) ($get('perawat') ?? 0) + (int) ($get('bidan') ?? 0) + (int) ($get('apoteker') ?? 0) + (int) ($get('ahli_gizi') ?? 0)) . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Status Obat</strong><div>' . ($get('status_obat') ?? '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Status Sarana</strong><div>' . (implode(', ', array_filter([
                                    $get('ambulans') ? 'Ambulans' : null,
                                    $get('laboratorium') ? 'Laboratorium' : null,
                                    $get('apotek') ? 'Apotek' : null,
                                    $get('ruang_bersalin') ? 'Ruang Bersalin' : null,
                                    $get('internet') ? 'Internet' : null,
                                    $get('listrik') ? 'Listrik' : null,
                                    $get('air_bersih') ? 'Air Bersih' : null,
                                ])) ?: '-') . '</div></div>'
                            .'<div class="ringkasan-card"><strong>Jumlah Permasalahan</strong><div>' . count($get('permasalahan') ?? []) . '</div></div>'
                            .'</div>'
                        )
                        ->columnSpanFull(),
                ]),
        ])->columns(2);
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
                Tables\Columns\TextColumn::make('desa.nama')->label('Desa')->searchable(),
                Tables\Columns\TextColumn::make('jumlah_balita')->label('Balita')->sortable(),
                Tables\Columns\TextColumn::make('jumlah_ibu_hamil')->label('Ibu Hamil')->sortable(),
                Tables\Columns\TextColumn::make('stunting')->label('Stunting')->sortable(),
                Tables\Columns\TextColumn::make('gizi_buruk')->label('Gizi Buruk')->sortable(),
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
