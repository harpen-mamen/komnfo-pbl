<?php

namespace App\Services\DataInput;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Component;
use Closure;
use Illuminate\Validation\Rule;
use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\KodeReferensi;
use App\Models\Opd;
use App\Models\Wilayah;
use App\Filament\Forms\Components\LeafletGeometryField;
use App\Services\GisGeometry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DatasetFieldResolver
{
    public static function buildFormSchema(Dataset $dataset): array
    {
        $fields = $dataset->fields()->orderBy('urutan')->get()->reject(fn ($field) => $field->nama === 'tahun' || in_array($field->nama, data_get($dataset->config,'input_hidden_fields',[]),true));
        $dimensionFields = $fields->where('is_dimension', true);
        $nonDimensionFields = $fields->where('is_dimension', false);

        // Pisahkan field auto-calc (untuk ditampilkan terpisah)
        $autoCalcFieldNames = FieldAutoCalculator::getAutoCalcFields($dataset->kode);
        $autoCalcFields = $nonDimensionFields->filter(fn ($f) => in_array($f->nama, $autoCalcFieldNames, true));
        $inputFields = $nonDimensionFields->filter(fn ($f) => !in_array($f->nama, $autoCalcFieldNames, true));

        $sections = [];
        $sections[] = Hidden::make('dataset_id')->default($dataset->id);
        $sections[] = self::buildMetadataHeader($dataset);
        $sections[] = self::buildContextSection($dataset);

        // Section Dimensi
        if ($dimensionFields->isNotEmpty()) {
            $sections[] = Section::make('Dimensi / Pengelompokan')
                ->description('Field yang menjadi dimensi pengelompokan data (mis. jenis, kategori, status, periode).')
                ->collapsible()
                ->schema(self::buildFieldsSchema($dimensionFields))
                ->columns(['default' => 1, 'md' => 2, 'xl' => 3]);
        }

        // Section Input - field yang harus diisi manual
        if ($inputFields->isNotEmpty()) {
            $inputCount = $inputFields->where('required', true)->count();
            $totalCount = $inputFields->count();
            $sections[] = Section::make('Data Input')
                ->description("Isi nilai data. {$inputCount} wajib diisi dari {$totalCount} field input.")
                ->schema(self::buildFieldsSchema($inputFields))
                ->columns(['default' => 1, 'md' => 2]);
        }

        // Section Auto-calc - field yang dihitung otomatis
        if ($autoCalcFields->isNotEmpty()) {
            $sections[] = Section::make('Angka sumber / hasil perhitungan')
                ->description('Isi angka yang dilaporkan sumber jika tersedia. Jika kosong dan seluruh bahan perhitungan tersedia, sistem menghitungnya saat disimpan. Angka sumber tidak ditimpa. Untuk menghitung ulang setelah mengubah bahan perhitungan, kosongkan angka hasil terlebih dahulu.')
                ->collapsible()
                ->collapsed()
                ->schema(collect(self::buildFieldsSchema($autoCalcFields))->map(function ($component) {
                    return $component->required(false);
                })->all())
                ->columns(['default' => 1, 'md' => 2]);
        }

        return array_map(fn (Component $section) => $section->columnSpanFull(), $sections);
    }

    public static function buildFieldsSchema($fields): array
    {
        $components = [];
        foreach ($fields as $field) {
            $components[] = self::buildField($field);
        }
        return $components;
    }

    public static function buildField(DatasetField $field): Component
    {
        $name = 'values.' . $field->nama;
        $label = $field->label ?? Str::headline($field->nama);
        $required = (bool) $field->required;
        $helpText = self::buildHelpText($field);

        switch ($field->tipe) {
            case 'text':
                $component = Textarea::make($name)->label($label)->rows(3)->required($required);
                break;
            case 'integer':
                $component = TextInput::make($name)->label($label)->numeric()->integer()->required($required)->step(1);
                break;
            case 'decimal':
                $component = TextInput::make($name)->label($label)->numeric()->required($required)->step('any')->inputMode('decimal');
                break;
            case 'boolean':
                $component = Toggle::make($name)->label($label)->required($required);
                break;
            case 'date':
                $component = DatePicker::make($name)->label($label)->required($required)->displayFormat('d/m/Y');
                break;
            case 'datetime':
                $component = DateTimePicker::make($name)->label($label)->required($required)->displayFormat('d/m/Y H:i');
                break;
            case 'json':
                if (str_starts_with((string) $field->lookup_source, 'geometry:')) {
                    $geometryType = substr($field->lookup_source, strlen('geometry:'));
                    $component = LeafletGeometryField::make($name)->label($label)->geometryType($geometryType)->required($required);
                } else {
                    $component = Textarea::make($name)->label($label)->rows(3)->required($required);
                }
                break;
            case 'enum':
                $options = $field->options ?? [];
                if (is_string($options)) {
                    $decoded = json_decode($options, true);
                    $options = is_array($decoded) ? $decoded : [];
                }
                if (array_is_list($options)) $options = array_combine($options, $options) ?: [];
                $component = Select::make($name)->label($label)->options($options)->required($required)->searchable();
                if ($field->nama === 'indikator_jalan') $component->reactive()->afterStateUpdated(function ($state, callable $set) {
                    $set('values.satuan', RoadInputDefinition::indicators()[$state][2]??null);
                });
                break;
            case 'reference':
                $component = self::buildReferenceField($field, $name, $label, $required);
                break;
            default:
                $component = TextInput::make($name)->label($label)->required($required)->maxLength(255);
                break;
        }

        if (!empty($field->satuan) && in_array($field->tipe, ['integer', 'decimal'], true)) {
            $component->suffix($field->satuan);
        }
        if (in_array($field->tipe, ['integer','decimal'], true)) {
            [$min,$max] = self::numericLimits($field);
            $component->minValue($min);
            if ($max !== null) $component->maxValue($max);
        }
        if ($field->nama === 'satuan' && $field->dataset?->kode === '5.59') $component->disabled()->dehydrated(false)->helperText('Satuan otomatis mengikuti indikator yang dipilih.');
        if ($helpText) {
            $component->helperText($helpText);
        }
        return $component;
    }

    protected static function buildReferenceField(DatasetField $field, string $name, string $label, bool $required): Component
    {
        $target = $field->reference_target;
        if (in_array($target, ['penduduks', 'penduduk'], true)) {
            return TextInput::make($name)->label($label . ' (NIK)')->required($required)
                ->maxLength(16)->minLength(16)->numeric()->placeholder('16 digit NIK');
        }
        if (in_array($target, ['wilayahs', 'wilayah'], true)) {
            return self::buildWilayahSelect($name, $label, $required, self::guessWilayahType($field));
        }
        if (in_array($target, ['opds', 'opd'], true)) {
            return Select::make($name)->label($label)
                ->options(Opd::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                ->required($required)->searchable()->preload();
        }
        if (in_array($target, ['kode_referensis', 'kode_referensi'], true)) {
            $jenis = $field->lookup_source ?? null;
            $query = KodeReferensi::where('aktif', true);
            if ($jenis) $query->where('jenis', $jenis);
            return Select::make($name)->label($label)
                ->options($query->orderBy('kode')->get()->pluck('nama', 'kode'))
                ->required($required)->searchable()->preload();
        }
        if (in_array($target, ['objek_lapangans', 'objek_lapangan'], true)) {
            return TextInput::make($name)->label($label)->required($required)
                ->maxLength(255)->placeholder('Nama atau kode objek');
        }
        if (in_array($target, ['keluargas', 'keluarga'], true)) {
            return TextInput::make($name)->label($label . ' (No KK)')->required($required)
                ->maxLength(16)->minLength(16)->numeric();
        }
        return TextInput::make($name)->label($label)->required($required)->maxLength(255);
    }

    protected static function buildWilayahSelect(string $name, string $label, bool $required, ?string $tipe = null): Select
    {
        $query = Wilayah::where('aktif', true)->orderBy('nama');
        if ($tipe) $query->where('tipe', $tipe);
        return Select::make($name)->label($label)
            ->options($query->get()->mapWithKeys(fn($w) => [$w->id => $w->kode_bps . ' - ' . $w->nama]))
            ->required($required)->searchable()->preload();
    }

    protected static function guessWilayahType(DatasetField $field): ?string
    {
        $nama = strtolower($field->nama);
        if (str_contains($nama, 'kabupaten')) return Wilayah::TIPE_KABUPATEN;
        if (str_contains($nama, 'kecamatan')) return Wilayah::TIPE_KECAMATAN;
        if (str_contains($nama, 'desa') || str_contains($nama, 'kelurahan')) return Wilayah::TIPE_DESA;
        if (str_contains($nama, 'dusun')) return Wilayah::TIPE_DUSUN;
        return null;
    }
    protected static function buildContextSection(Dataset $dataset): Section
    {
        $tahun = (int) date('Y');
        $tahunMin = $tahun - 10;
        $tahunMax = $tahun + 1;
        $fields = [];
        $fields[] = Select::make('tahun')->label('Tahun')
            ->options(array_combine(range($tahunMin, $tahunMax), range($tahunMin, $tahunMax)))
            ->default($tahun)->required();

        switch ($dataset->period_type) {
            case 'bulanan':
                $fields[] = Select::make('periode_label')->label('Bulan')
                    ->options(self::bulanOptions())
                    ->required()->default('Januari');
                break;
            case 'triwulanan':
                $fields[] = Select::make('periode_label')->label('Triwulan')
                    ->options(self::triwulanOptions())
                    ->required()->default('Q1');
                break;
            case 'semesteran':
                $fields[] = Select::make('periode_label')->label('Semester')
                    ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap'])
                    ->required()->default('Ganjil');
                break;
            case 'harian':
            case 'event':
                $fields[] = DatePicker::make('periode_label')->label('Tanggal')->required()->displayFormat('d/m/Y');
                break;
            case 'master':
                $fields[] = Hidden::make('periode_label')->default('Master');
                break;
            default:
                $fields[] = Hidden::make('periode_label');
                break;
        }

        if ($dataset->cakupan_wilayah === 'kecamatan') {
            $fields[] = Hidden::make('wilayah_type')->default('kecamatan');
            $fields[] = self::buildWilayahSelect('wilayah_id', 'Kecamatan', true, Wilayah::TIPE_KECAMATAN);
        } elseif ($dataset->cakupan_wilayah === 'desa') {
            $fields[] = Hidden::make('wilayah_type')->default('desa');
            $fields[] = self::buildWilayahSelect('wilayah_id', 'Desa/Kelurahan', true, Wilayah::TIPE_DESA);
        } else {
            $fields[] = Hidden::make('wilayah_type')->default('kabupaten');
            $fields[] = Hidden::make('wilayah_id');
        }

        $fields[] = Hidden::make('status')->default('draft');

        return Section::make('Periode & Wilayah')
            ->description('Tentukan waktu dan wilayah cakupan data.')
            ->collapsible()
            ->schema($fields)->columns(['default' => 1, 'md' => 2, 'xl' => 3]);
    }

    protected static function bulanOptions(): array
    {
        return [
            'Januari' => 'Januari', 'Februari' => 'Februari',
            'Maret' => 'Maret', 'April' => 'April',
            'Mei' => 'Mei', 'Juni' => 'Juni',
            'Juli' => 'Juli', 'Agustus' => 'Agustus',
            'September' => 'September', 'Oktober' => 'Oktober',
            'November' => 'November', 'Desember' => 'Desember',
        ];
    }

    protected static function triwulanOptions(): array
    {
        return [
            'Q1' => 'Triwulan I (Jan-Mar)',
            'Q2' => 'Triwulan II (Apr-Jun)',
            'Q3' => 'Triwulan III (Jul-Sep)',
            'Q4' => 'Triwulan IV (Okt-Des)',
        ];
    }

    protected static function buildMetadataHeader(Dataset $dataset): Section
    {
        $opd = $dataset->opd?->nama ?? '-';
        $category = $dataset->category?->nama ?? $dataset->kategori ?? '-';
        $sumber = $dataset->sumber_data ?? 'Administrasi';
        $lisensi = $dataset->lisensi ?? 'cc_by_4';

        $periodLabel = match ($dataset->period_type) {
            'tahunan' => 'Tahunan',
            'semesteran' => 'Semesteran',
            'triwulanan' => 'Triwulanan',
            'bulanan' => 'Bulanan',
            'harian' => 'Harian',
            'event' => 'Per Kejadian',
            'real_time' => 'Waktu Nyata',
            'master' => 'Master (Sekali Isi)',
            default => $dataset->period_type,
        };

        $cakupan = match ($dataset->cakupan_wilayah) {
            'kabupaten' => 'Kabupaten',
            'kecamatan' => 'Per Kecamatan',
            'desa' => 'Per Desa/Kelurahan',
            default => $dataset->cakupan_wilayah ?? '-',
        };

        $lisensiLabel = match ($lisensi) {
            'cc_by_4' => 'CC BY 4.0 (Terbuka)',
            'open_data' => 'Open Data',
            'terbatas' => 'Terbatas',
            'internal' => 'Internal',
            default => $lisensi,
        };

        $html = '<div class="space-y-2 text-sm">'
            . '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">'
            . '<div><span class="font-semibold">Kode SDI:</span><br>' . ($dataset->kode_sdi ?? '-') . '</div>'
            . '<div><span class="font-semibold">Kode Internal:</span><br>' . $dataset->kode . '</div>'
            . '<div><span class="font-semibold">OPD Produsen:</span><br>' . $opd . '</div>'
            . '<div><span class="font-semibold">Kategori:</span><br>' . $category . '</div>'
            . '<div><span class="font-semibold">Periode:</span><br>' . $periodLabel . '</div>'
            . '<div><span class="font-semibold">Cakupan Wilayah:</span><br>' . $cakupan . '</div>'
            . '<div><span class="font-semibold">Sumber Data:</span><br>' . $sumber . '</div>'
            . '<div><span class="font-semibold">Lisensi:</span><br>' . $lisensiLabel . '</div>'
            . '</div>'
            . '<p class="text-xs text-gray-500 mt-2">Standar: Perpres 39/2019 + Perbup Sangihe 30/2024</p>'
            . '</div>';

        return Section::make('Metadata Indikator (SDI)')
            ->description('Perpres 39/2019 + Perbup Sangihe 30/2024')
            ->collapsible()
            ->collapsed()
            ->schema([
                Placeholder::make('sdi_metadata')
                    ->label('')
                    ->content(new HtmlString($html)),
            ]);
    }

    protected static function buildHelpText(DatasetField $field): ?string
    {
        $parts = [];
        if (!empty($field->definisi)) $parts[] = 'Definisi: ' . Str::limit($field->definisi, 200);
        if (!empty($field->konsep)) $parts[] = 'Konsep: ' . Str::limit($field->konsep, 150);
        if (!empty($field->klasifikasi)) $parts[] = 'Klasifikasi: ' . $field->klasifikasi;
        if (!empty($field->ukuran)) $parts[] = 'Ukuran: ' . $field->ukuran;

        $rules = $field->validation_rules;
        if (is_string($rules)) $rules = json_decode($rules, true);
        if (is_array($rules)) {
            if (isset($rules['keterangan'])) {
                $parts[] = 'Catatan: ' . $rules['keterangan'];
            } elseif (isset($rules['min']) || isset($rules['max'])) {
                $min = $rules['min'] ?? '*';
                $max = $rules['max'] ?? '*';
                $parts[] = "Rentang: {$min} - {$max}";
            }
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    public static function buildValidationRules(Dataset $dataset): array
    {
        $rules = [
            'dataset_id' => 'required|exists:datasets,id',
            'tahun' => 'required|integer|min:1900|max:2100',
            'periode_label' => 'nullable|string|max:32',
            'wilayah_type' => 'nullable|in:kabupaten,kecamatan,desa',
            'wilayah_id' => in_array($dataset->cakupan_wilayah,['kecamatan','desa'],true) ? 'required|integer|exists:wilayahs,id' : 'nullable|integer|exists:wilayahs,id',
        ];

        foreach ($dataset->fields as $field) {
            $fieldRules = [];
            $name = 'values.' . $field->nama;
            $hidden = in_array($field->nama,data_get($dataset->config,'input_hidden_fields',[]),true);
            $auto = in_array($field->nama,FieldAutoCalculator::getAutoCalcFields($dataset->kode),true);
            $fieldRules[] = $field->required && !$hidden && !$auto ? 'required' : 'nullable';

            switch ($field->tipe) {
                case 'integer':
                    $fieldRules[] = 'integer';
                    [$min,$max] = self::numericLimits($field);
                    $fieldRules[] = 'min:'.$min;
                    if ($max !== null) $fieldRules[]='max:'.$max;
                    break;
                case 'decimal':
                    $fieldRules[] = 'numeric';
                    [$min,$max] = self::numericLimits($field);
                    $fieldRules[] = 'min:'.$min;
                    if ($max !== null) $fieldRules[]='max:'.$max;
                    break;
                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
                case 'date':
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'json':
                    if (str_starts_with((string) $field->lookup_source, 'geometry:')) {
                        $fieldRules[] = 'array:type,geometry,properties';
                        $fieldRules[] = 'required_array_keys:type,geometry';
                        $expectedType = substr((string) $field->lookup_source, strlen('geometry:'));
                        $fieldRules[] = function (string $attribute, mixed $value, Closure $fail) use ($expectedType): void {
                            if (! is_array($value) || ($value['type'] ?? null) !== 'Feature' || ! is_array($value['geometry'] ?? null)) {
                                $fail('Lokasi harus berupa GeoJSON Feature yang valid.');
                                return;
                            }
                            $geometry = $value['geometry'];
                            if (! GisGeometry::isValid($geometry, $expectedType ?: 'any')) {
                                $fail('Geometri tidak valid atau jenisnya tidak sesuai. Pastikan garis memiliki dua titik, poligon tertutup, dan koordinat berada dalam rentang WGS84.');
                            }
                        };
                    } else {
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:5000';
                    }
                    break;
                case 'enum':
                    $opts = $field->options ?? [];
                    if (is_string($opts)) $opts = json_decode($opts, true) ?: [];
                    if (array_is_list($opts)) $opts = array_combine($opts, $opts) ?: [];
                    if (!empty($opts)) $fieldRules[] = 'in:' . implode(',', array_keys($opts));
                    break;
                case 'reference':
                    if (in_array($field->reference_target, ['penduduks', 'penduduk'], true)) {
                        $fieldRules[] = 'digits:16';
                    } elseif (in_array($field->reference_target, ['keluargas', 'keluarga'], true)) {
                        $fieldRules[] = 'digits:16';
                    } elseif (in_array($field->reference_target, ['wilayahs', 'wilayah'], true)) {
                        $exists=Rule::exists('wilayahs','id')->where('aktif',true);
                        if ($type=self::guessWilayahType($field)) $exists->where('tipe',$type);
                        $fieldRules[] = $exists;
                    } elseif (in_array($field->reference_target, ['opds', 'opd'], true)) {
                        $fieldRules[] = 'exists:opds,id';
                    }
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:' . ($field->tipe === 'text' ? 5000 : 255);
                    break;
            }

            $rules[$name] = $fieldRules;
        }

        if ((string)$dataset->kode === '5.59') $rules['values.nilai'][] = Rule::when(
            fn ($data) => data_get($data,'values.indikator_jalan') === 'jumlah_jembatan', ['integer']
        );
        return $rules;
    }

    private static function numericLimits(DatasetField $field): array
    {
        if ($field->nama === 'latitude') return [-90,90];
        if ($field->nama === 'longitude') return [-180,180];
        $rules=$field->validation_rules??[];
        if (is_string($rules)) $rules=json_decode($rules,true)?:[];
        return [$rules['min']??0,$rules['max']??null];
    }

}
