<?php

namespace Tests\Feature;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetSubmission;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\DataInput\DatasetValueManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AllOpdDatasetLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_catalog_dataset_can_be_input_submitted_reviewed_published_and_exported(): void
    {
        Notification::fake();
        Role::findOrCreate('admin_opd', 'web');
        Role::findOrCreate('admin_kominfo', 'web');

        $blueprint = require database_path('seeders/data/SangiheOpdDatasetsBlueprint.php');
        foreach (glob(database_path('seeders/data/_93dataset_bab*.json')) as $file) {
            foreach (json_decode(file_get_contents($file), true) ?: [] as $code => [$categoryCode, $datasets]) {
                foreach ($datasets as $datasetCode => [$name, $periodType, $fields]) {
                    $normalizedFields = [];
                    foreach ($fields as $fieldName => $fieldDefinition) {
                        [$label, $type, , $extra] = $fieldDefinition;
                        $normalizedFields[$fieldName] = [
                            'label' => $label,
                            'tipe' => $type,
                            'options' => is_array($extra) ? $extra : null,
                            'lookup_source' => $fieldDefinition[4] ?? (is_string($extra) && $extra === 'wilayah' ? 'wilayah' : null),
                        ];
                    }
                    $blueprint[$code]['datasets'][$datasetCode] = [
                        'nama' => $name,
                        'period_type' => $periodType,
                        'cakupan_wilayah' => 'kabupaten',
                        'fields' => $normalizedFields,
                    ];
                }
            }
        }

        $opds = [];
        foreach ($blueprint as $code => $config) {
            $canonical = ['BKPSDMD' => 'BKPSDM', 'DPUPR' => 'PUPR', 'DISTAMAN' => 'DISTAN'][$code] ?? $code;
            $opds[$code] = Opd::firstOrCreate(['kode' => $canonical], ['nama' => $canonical, 'aktif' => true]);
        }

        $opdCodes = ['SETDA', 'SETWAN', 'BKPSDM', 'DUKCAPIL', 'DISDIK', 'DINKES', 'DINSOS', 'DPPKB', 'DP3A', 'DPMD', 'DISPORA', 'DISNAKER', 'KETPANGAN', 'DISTAN', 'PERIKANAN', 'DISHUB', 'PUPR', 'PERPUS', 'PERKIM', 'DPMPTSP', 'KOPERASI', 'DISPERINDAG', 'BPKPD', 'INSPEKTORAT', 'BAPPELITBANG', 'KESBANGPOL', 'BATAS', 'BPBD', 'SATPOLPP', 'DLH', 'DISKOMINFO', 'PARIWISATA'];
        $catalogOpds = [];
        foreach ($opdCodes as $code) {
            $catalogOpds[$code] = Opd::firstOrCreate(['kode' => $code], ['nama' => $code, 'aktif' => true]);
        }

        $kominfo = User::factory()->create();
        $kominfo->assignRole('admin_kominfo');
        $kabupaten = Wilayah::create(['nama' => 'Kabupaten Uji', 'tipe' => Wilayah::TIPE_KABUPATEN, 'aktif' => true]);
        $kecamatan = Wilayah::create(['parent_id' => $kabupaten->id, 'nama' => 'Kecamatan Uji', 'tipe' => Wilayah::TIPE_KECAMATAN, 'aktif' => true]);
        $desa = Wilayah::create(['parent_id' => $kecamatan->id, 'nama' => 'Desa Uji', 'tipe' => Wilayah::TIPE_DESA, 'aktif' => true]);
        $cases = [];

        foreach ($blueprint as $code => $config) {
            $canonical = ['BKPSDMD' => 'BKPSDM', 'DPUPR' => 'PUPR', 'DISTAMAN' => 'DISTAN'][$code] ?? $code;
            $opd = $opds[$code];
            $producer = User::factory()->create(['opd_id' => $opd->id]);
            $producer->assignRole('admin_opd');
            $this->actingAs($producer);

            foreach ($config['datasets'] as $datasetCode => $definition) {
                $dataset = Dataset::updateOrCreate(
                    ['opd_id' => $opd->id, 'kode' => $datasetCode],
                    ['nama' => $definition['nama'], 'period_type' => $definition['period_type'] ?? 'bulanan', 'cakupan_wilayah' => $definition['cakupan_wilayah'] ?? 'kabupaten', 'lisensi' => 'cc_by_4', 'is_active' => true]
                );
                $dataset->fields()->delete();
                foreach ($definition['fields'] ?? [] as $fieldName => $fieldDefinition) {
                    DatasetField::create([
                        'dataset_id' => $dataset->id,
                        'nama' => $fieldName,
                        'label' => $fieldDefinition['label'] ?? $fieldName,
                        'tipe' => $fieldDefinition['tipe'] ?? 'string',
                        'required' => false,
                        'options' => isset($fieldDefinition['options']) && array_is_list($fieldDefinition['options']) ? array_combine($fieldDefinition['options'], $fieldDefinition['options']) : ($fieldDefinition['options'] ?? null),
                        'reference_target' => $fieldDefinition['reference_target'] ?? null,
                        'lookup_source' => $fieldDefinition['lookup_source'] ?? null,
                        'urutan' => $dataset->fields()->count(),
                    ]);
                }

                $payload = [];
                foreach ($dataset->fields as $field) {
                    if ($field->tipe === 'reference' && in_array($field->reference_target, ['penduduk', 'penduduks'], true)) {
                        $payload[$field->nama] = '1234567890123456';
                        continue;
                    }
                    if ($field->tipe === 'reference' && in_array($field->reference_target, ['keluarga', 'keluargas'], true)) {
                        $payload[$field->nama] = '1234567890123456';
                        continue;
                    }
                    $payload[$field->nama] = match ($field->tipe) {
                        'integer' => 1,
                        'decimal' => 1.5,
                        'boolean' => true,
                        'date' => '2026-10-30',
                        'datetime' => '2026-10-30 12:00:00',
                        'json' => match (substr((string) $field->lookup_source, strlen('geometry:'))) {
                            'point' => ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [125.5, 3.5]], 'properties' => []],
                            'linestring' => ['type' => 'Feature', 'geometry' => ['type' => 'LineString', 'coordinates' => [[125.5, 3.5], [125.6, 3.6]]], 'properties' => []],
                            'polygon' => ['type' => 'Feature', 'geometry' => ['type' => 'Polygon', 'coordinates' => [[[125.5, 3.5], [125.6, 3.5], [125.6, 3.6], [125.5, 3.5]]]], 'properties' => []],
                            default => str_starts_with((string) $field->lookup_source, 'geometry:')
                                ? ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [125.5, 3.5]], 'properties' => []]
                                : 'uji',
                        },
                        'enum' => is_array($field->options) ? (array_key_first($field->options) ?? 'uji') : 'uji',
                        'reference' => '1',
                        default => 'uji',
                    };
                    if ($field->tipe === 'reference' && in_array($field->reference_target, ['wilayah', 'wilayahs'], true)) {
                        $payload[$field->nama] = str_contains($field->nama, 'desa') ? $desa->id : $kecamatan->id;
                    }
                }

                $value = app(DatasetValueManager::class)->createDraft($dataset, [
                    'tahun' => 2026,
                    'periode_label' => 'Oktober',
                    'wilayah_type' => $dataset->cakupan_wilayah === 'kecamatan' ? 'kecamatan' : ($dataset->cakupan_wilayah === 'desa' ? 'desa' : 'kabupaten'),
                    'wilayah_id' => $dataset->cakupan_wilayah === 'kecamatan' ? $kecamatan->id : ($dataset->cakupan_wilayah === 'desa' ? $desa->id : $kabupaten->id),
                    'values' => $payload,
                ]);
                $this->assertSame('draft', $value->status, "Draft gagal: {$canonical}/{$datasetCode}");

                $submission = app(DatasetValueManager::class)->submitMany([$value->id], $producer, 'Uji audit otomatis');
                $this->assertSame('submitted', $value->fresh()->status, "Submit gagal: {$canonical}/{$datasetCode}");

                $this->actingAs($kominfo);
                $response = $this->post(route('dataset-submissions.verify', $submission->id));
                $response->assertRedirect();
                $this->assertSame('published', $value->fresh()->status, "Penerbitan gagal: {$canonical}/{$datasetCode}");
                $this->assertSame('published', $submission->fresh()->status);

                $export = app(\App\Services\DataInput\DatasetExportService::class)->toJson($dataset, 'published');
                $this->assertNotEmpty($export, "Export kosong: {$canonical}/{$datasetCode}");

                $cases[] = ['opd' => $canonical, 'dataset' => $datasetCode, 'status' => 'published'];
                $this->actingAs($producer);
            }
        }

        $this->assertNotEmpty($cases);
        $this->assertSame(count($cases), DatasetValue::where('status', 'published')->count());
        $this->assertSame(count($cases), DatasetSubmission::where('status', 'published')->count());
        $this->assertSame(178, count($cases), 'Semua 178 dataset katalog OPD harus diuji.');
        $this->assertCount(count($cases), array_unique(array_map(fn (array $case) => $case['opd'].'|'.$case['dataset'], $cases)));
        $this->assertCount(32, array_unique(array_column($cases, 'opd')), 'Semua OPD resmi harus memiliki dataset teruji.');
        $this->assertNotEmpty($catalogOpds);
    }

    public function test_public_portal_endpoints_and_dataset_admin_export_survive_current_schema(): void
    {
        foreach ([
            '/up',
            '/api/v1/stats',
            '/api/v1/penduduk',
            '/api/v1/kecamatan',
            '/api/v1/desa',
            '/api/v1/public/hero',
            '/api/v1/public/home',
            '/api/v1/public/berita',
            '/api/v1/public/statistik',
            '/api/v1/public/peta',
            '/api/v1/public/peta/cari?q=jalan',
            '/api/v1/public/layanan',
            '/api/v1/public/infografis',
            '/api/v1/public/infografis/categories',
            '/api/v1/public/tentang-daerah',
            '/api/v1/public/tentang-daerah/opd',
        ] as $uri) {
            $response = $this->getJson($uri);
            $this->assertSame(200, $response->status(), $uri.' => '.$response->getContent());
        }

        $opd = Opd::create(['kode' => 'API-OPD', 'nama' => 'OPD API', 'aktif' => true]);
        $user = User::factory()->create(['opd_id' => $opd->id]);
        Role::findOrCreate('admin_opd', 'web');
        $user->assignRole('admin_opd');
        $dataset = Dataset::create(['opd_id' => $opd->id, 'kode' => 'API-DS', 'nama' => 'Dataset API', 'period_type' => 'tahunan', 'cakupan_wilayah' => 'kabupaten', 'is_active' => true]);
        $dataset->fields()->create(['nama' => 'jumlah', 'label' => 'Jumlah', 'tipe' => 'integer']);
        $dataset->values()->create(['tahun' => 2026, 'periode_label' => '2026', 'values' => ['jumlah' => 2], 'status' => 'draft']);
        $this->actingAs($user);
        $this->get('/admin/dataset-values?dataset=API-DS')->assertOk();
        $this->get('/admin/dataset-values/create?dataset=API-DS')->assertOk();
        $this->get('/admin/dataset/API-DS/stats')->assertOk();
        $this->get('/admin/dataset/API-DS/export?format=csv')->assertOk();
        $this->get('/admin/dataset/API-DS/import-template')->assertOk();
        $this->postJson('/admin/dataset/API-DS/import', [
            'file' => UploadedFile::fake()->createWithContent('audit.csv', "tahun,periode_label,values_jumlah\n2026,2026,17\n"),
        ])->assertOk()->assertJsonPath('data.success', 1);

        $kominfo = User::factory()->create();
        Role::findOrCreate('admin_kominfo', 'web');
        $kominfo->assignRole('admin_kominfo');
        $this->actingAs($kominfo)->get('/admin/verifikasi-data-sektoral')->assertOk();
    }

    public function test_mobile_reads_published_desa_family_and_submits_only_location_through_kecamatan_review(): void
    {
        Notification::fake();
        Role::findOrCreate('admin_opd', 'web');
        Role::findOrCreate('admin_desa', 'web');
        Role::findOrCreate('admin_kecamatan', 'web');
        Role::findOrCreate('admin_kominfo', 'web');

        $opd = Opd::firstOrCreate(['kode' => 'DUKCAPIL'], ['nama' => 'Dukcapil', 'aktif' => true]);
        $producer = User::factory()->create(['opd_id' => $opd->id]);
        $producer->assignRole('admin_opd');
        $dataset = Dataset::create([
            'opd_id' => $opd->id,
            'kode' => 'KELUARGA-DESA-UJI',
            'nama' => 'Keluarga per Desa Uji',
            'period_type' => 'tahunan',
            'cakupan_wilayah' => 'desa',
            'lisensi' => 'terbatas',
            'is_active' => true,
            'config' => ['mobile_family_source' => true],
        ]);
        $dataset->fields()->create(['nama' => 'nomor_kk', 'label' => 'Nomor KK', 'tipe' => 'string']);
        $dataset->fields()->create(['nama' => 'nama_kepala_keluarga', 'label' => 'Kepala Keluarga', 'tipe' => 'string']);
        $kabupaten = Wilayah::create(['nama' => 'Kabupaten Mobile', 'tipe' => 'kabupaten', 'aktif' => true]);
        $kecamatan = Wilayah::create(['nama' => 'Kecamatan Mobile', 'tipe' => 'kecamatan', 'parent_id' => $kabupaten->id, 'aktif' => true]);
        $desa = Wilayah::create(['nama' => 'Desa Mobile', 'tipe' => 'desa', 'parent_id' => $kecamatan->id, 'aktif' => true]);
        $family = DatasetValue::create([
            'dataset_id' => $dataset->id,
            'tahun' => 2026,
            'periode_label' => 'Master',
            'wilayah_type' => 'desa',
            'wilayah_id' => $desa->id,
            'values' => ['nomor_kk' => '1234567890123456', 'nama_kepala_keluarga' => 'Kepala Uji'],
            'status' => 'published',
            'status_verifikasi' => 'published',
            'published_at' => now(),
            'created_by' => $producer->id,
        ]);

        $fieldUser = User::factory()->create(['desa_id' => $desa->id, 'kecamatan_id' => $kecamatan->id]);
        $fieldUser->assignRole('admin_desa');
        $this->actingAs($fieldUser, 'sanctum')
            ->getJson('/api/v1/mobile/master/keluarga?desa_id='.$desa->id)
            ->assertOk()
            ->assertJsonPath('data.0.id', $family->id)
            ->assertJsonPath('data.0.nama_kepala_keluarga', 'Kepala Uji');

        $otherDesa = Wilayah::create(['nama' => 'Desa Lain', 'tipe' => 'desa', 'parent_id' => $kecamatan->id, 'aktif' => true]);
        $otherFamily = DatasetValue::create([
            'dataset_id' => $dataset->id,
            'tahun' => 2026,
            'periode_label' => 'Master',
            'wilayah_type' => 'desa',
            'wilayah_id' => $otherDesa->id,
            'values' => ['nomor_kk' => '9999999999999999', 'nama_kepala_keluarga' => 'Keluarga Lain'],
            'status' => 'published',
            'status_verifikasi' => 'published',
            'published_at' => now(),
            'created_by' => $producer->id,
        ]);
        $this->getJson('/api/v1/mobile/master/keluarga?desa_id='.$otherDesa->id)->assertForbidden();

        $operationId = (string) \Illuminate\Support\Str::uuid();
        $response = $this->postJson('/api/v1/mobile/field-points', ['operations' => [[
            'operation_id' => $operationId,
            'desa_id' => $desa->id,
            'jenis' => 'rumah',
            'nama' => 'Titik Rumah Uji',
            'latitude' => 3.5,
            'longitude' => 125.5,
            'keluarga_ids' => [$family->id],
        ]]])->assertCreated()->assertJsonPath('results.0.status', 'pending');
        $batchId = $response->json('results.0.batch_id');
        $point = \App\Models\MobileFieldPoint::firstOrFail();
        $this->assertSame([$family->id], $point->keluarga_dataset_value_ids);
        $this->assertNotContains($otherFamily->id, $point->keluarga_dataset_value_ids);
        $this->assertSame($kecamatan->id, $point->submissionBatch->kecamatan_id);

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');
        $this->actingAs($fieldUser, 'sanctum')
            ->getJson('/api/v1/mobile/field-points')->assertOk()->assertJsonPath('data.0.id', $point->id);
        $review = app(\App\Services\BatchApprovalService::class);
        $review->startReview($point->submissionBatch, $kecamatanUser);
        $review->forwardToKominfo($point->submissionBatch->fresh(), $kecamatanUser);
        $kominfo = User::factory()->create();
        $kominfo->assignRole('admin_kominfo');
        $review->approve($point->submissionBatch->fresh(), $kominfo);

        $this->assertSame(SubmissionBatch::STATUS_APPROVED, $point->submissionBatch->fresh()->status);
        $this->assertSame($batchId, $point->fresh()->submission_batch_id);
    }
}
