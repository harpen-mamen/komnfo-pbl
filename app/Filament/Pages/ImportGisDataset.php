<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Group;
use App\Filament\Resources\DynamicDatasetResource;
use Illuminate\Validation\ValidationException;
use App\Filament\Forms\Components\GisGeoJsonUploadField;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Wilayah;
use App\Services\DataInput\DatasetFieldResolver;
use App\Services\DataInput\GisDatasetImporter;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ImportGisDataset extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'import-gis/{kode}';
    protected static ?string $title = 'Impor Data GIS';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.import-gis-dataset';

    public string $kode;
    public ?Dataset $dataset = null;
    public ?array $data = [];

    public function mount(string $kode): void
    {
        $this->kode = $kode;
        $this->dataset = Dataset::with(['fields', 'opd'])->where('kode', $kode)->where('is_active', true)->firstOrFail();
        $user = Auth::user();
        abort_unless($user && ($user->isSuperAdmin() || (int) $user->opd_id === (int) $this->dataset->opd_id), 403);
        abort_unless($this->dataset->fields->contains(fn ($field) => $field->tipe === 'json' && str_starts_with((string) $field->lookup_source, 'geometry:')), 404);
        abort_unless(auth()->user()->can('create', DatasetValue::class), 403);
        $this->form->fill(['tahun' => now()->year]);
    }

    protected function getFormStatePath(): string { return 'data'; }

    protected function getFormSchema(): array
    {
        $geometry = $this->dataset->fields->first(fn ($field) => $field->tipe === 'json' && str_starts_with((string) $field->lookup_source, 'geometry:'));
        $schema = [
            Section::make('Berkas geospasial')->description('Unggah GeoJSON atau ZIP shapefile. SHP harus menyertakan SHX, DBF, dan PRJ agar atribut terbaca dan koordinat dapat dikonversi. Impor disimpan sebagai draft untuk diperiksa OPD/Walidata.')->schema([
                GisGeoJsonUploadField::make('payload')->label('File GeoJSON / SHP ZIP')->reactive()->required(),
            ]),
            Section::make('Periode dan wilayah')->schema([
                TextInput::make('tahun')->label('Tahun')->numeric()->integer()->minValue(1900)->maxValue(2200)->required(),
                TextInput::make('periode_label')->label('Periode')->maxLength(100),
                Select::make('wilayah_id')->label('Wilayah')->options(fn () => Wilayah::query()->where('aktif', true)->whereIn('tipe', ['kabupaten','kecamatan'])->orderBy('nama')->pluck('nama','id'))->searchable()->nullable(),
            ])->columns(3),
            Placeholder::make('geometry_target')->label('Kolom geometri')->content($geometry->label . ' · ' . $geometry->lookup_source),
        ];
        $fields = $this->dataset->fields->filter(fn ($field) => $field->nama !== 'tahun' && $field->id !== $geometry->id && ! in_array($field->nama, data_get($this->dataset->config, 'input_hidden_fields', []), true));
        if ($fields->isNotEmpty()) {
            $schema[] = Section::make('Pemetaan atribut SHP')->description('Pilih kolom berkas yang cocok dengan field dataset. Field wajib yang tidak tersedia di setiap objek harus diisi sebagai nilai umum.')->schema(
                $fields->map(function ($field) {
                    $components = [Select::make('mapping.' . $field->nama)->label($field->label)->options(fn () => $this->propertyOptions($this->data['payload'] ?? null))->searchable()->nullable()];
                    if ($field->required) {
                        $default = DatasetFieldResolver::buildField($field)->required(false)->statePath('defaults.' . $field->nama)->helperText('Nilai umum opsional; digunakan bila atribut tidak tersedia.');
                        $components[] = $default;
                    }
                    return Group::make($components)->columns(2);
                })->all()
            )->columns(1);
        }
        return $schema;
    }

    private function propertyOptions(?string $payload): array
    {
        if (! $payload) return [];
        $data = json_decode($payload, true);
        $features = $data['features'] ?? [];
        $keys = [];
        foreach (array_slice(is_array($features) ? $features : [], 0, 500) as $feature) foreach (array_keys($feature['properties'] ?? []) as $key) $keys[$key] = $key;
        return array_slice($keys, 0, 100, true);
    }

    public function import(): void
    {
        try {
            $count = app(GisDatasetImporter::class)->import($this->dataset, $this->form->getState());
            Notification::make()->title("{$count} objek masuk sebagai draft")->body('Periksa isi data dan kirim untuk verifikasi sebelum dipublikasikan.')->success()->send();
            $this->redirect(DynamicDatasetResource::getUrl('index', ['dataset' => $this->dataset->kode]));
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) Notification::make()->title('Impor belum disimpan')->body(implode(' ', $messages))->danger()->persistent()->send();
        }
    }
}
