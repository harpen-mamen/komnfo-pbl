<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Seed clearly separated draft examples for the Bupati monitoring preview.
 * Never call this from DatabaseSeeder or a production deployment.
 */
class BupatiMonitoringDemoSeeder extends Seeder
{
    private const MARKER = '[DEMO-BUPATI]';

    public function run(): void
    {
        $datasets = Dataset::query()->whereIn('kode', ['IKU_DAERAH', '7.73'])->get()->keyBy('kode');
        $iku = $datasets->get('IKU_DAERAH');
        $apbd = $datasets->get('7.73');
        $kominfo = Opd::query()->where('kode', 'DISKOMINFO')->first();
        $userId = User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin_bupati'))->value('id');
        if (! $iku || ! $apbd || ! $kominfo || ! $userId) {
            throw new RuntimeException('Dataset IKU/APBD, OPD Diskominfo, atau akun Admin Bupati belum tersedia; tidak ada data demo yang dibuat.');
        }

        DB::transaction(function () use ($iku, $apbd, $kominfo, $userId): void {
            DB::table('dataset_values')->where('notes', 'like', self::MARKER.'%')->delete();

            $this->insertDrafts($iku, $userId, [
                ['year'=>2023,'indicator'=>'SIMULASI · Cakupan layanan dasar','target'=>'78','actual'=>'64','unit'=>'persen','category'=>'Sosial'],
                ['year'=>2024,'indicator'=>'SIMULASI · Cakupan layanan dasar','target'=>'80','actual'=>'69','unit'=>'persen','category'=>'Sosial'],
                ['year'=>2025,'indicator'=>'SIMULASI · Cakupan layanan dasar','target'=>'82','actual'=>'74','unit'=>'persen','category'=>'Sosial'],
                ['year'=>2026,'indicator'=>'SIMULASI · Cakupan layanan dasar','target'=>'85','actual'=>'77','unit'=>'persen','category'=>'Sosial'],
                ['year'=>2024,'indicator'=>'SIMULASI · Akses layanan kesehatan dasar','target'=>'90','actual'=>'81','unit'=>'persen','category'=>'Kesehatan'],
                ['year'=>2025,'indicator'=>'SIMULASI · Akses layanan kesehatan dasar','target'=>'92','actual'=>'86','unit'=>'persen','category'=>'Kesehatan'],
                ['year'=>2026,'indicator'=>'SIMULASI · Akses layanan kesehatan dasar','target'=>'94','actual'=>'89','unit'=>'persen','category'=>'Kesehatan'],
            ], fn (array $row): array => [
                'tahun'=>$row['year'], 'indikator'=>$row['indicator'], 'target'=>$row['target'],
                'realisasi'=>$row['actual'], 'satuan'=>$row['unit'], 'kategori'=>$row['category'],
            ]);

            $this->insertDrafts($apbd, $userId, [
                ['year'=>2024,'opd'=>'Dinas Kesehatan (SIMULASI)','pagu'=>80000000000,'actual'=>66400000000,'fisik'=>81],
                ['year'=>2024,'opd'=>'Dinas PUPR (SIMULASI)','pagu'=>120000000000,'actual'=>90000000000,'fisik'=>74],
                ['year'=>2025,'opd'=>'Dinas Kesehatan (SIMULASI)','pagu'=>85000000000,'actual'=>73100000000,'fisik'=>86],
                ['year'=>2025,'opd'=>'Dinas PUPR (SIMULASI)','pagu'=>128000000000,'actual'=>108800000000,'fisik'=>83],
                ['year'=>2026,'opd'=>'Dinas Kesehatan (SIMULASI)','pagu'=>90000000000,'actual'=>63000000000,'fisik'=>68],
                ['year'=>2026,'opd'=>'Dinas PUPR (SIMULASI)','pagu'=>135000000000,'actual'=>94500000000,'fisik'=>61],
            ], fn (array $row): array => [
                'tahun'=>$row['year'], 'perangkat_daerah'=>$row['opd'], 'pagu_anggaran'=>$row['pagu'],
                'realisasi_anggaran'=>$row['actual'], 'persen_realisasi'=>round($row['actual'] / $row['pagu'] * 100, 1),
                'target_fisik'=>100, 'realisasi_fisik'=>$row['fisik'],
            ]);

            $demoDataset = Dataset::query()->firstOrCreate(
                ['kode'=>'DEMO_INFRA_DIGITAL_BUPATI'],
                [
                    'opd_id'=>$kominfo->id,
                    'category_id'=>DatasetCategory::query()->value('id'),
                    'kode_sdi'=>'DEMO_INFRA_DIGITAL_BUPATI',
                    'nama'=>'SIMULASI Infrastruktur Digital untuk Pratinjau Bupati',
                    'deskripsi'=>'Data fiktif untuk pratinjau antarmuka. Bukan inventaris maupun laporan kondisi jaringan Sangihe.',
                    'kategori'=>'Komunikasi dan Informatika',
                    'period_type'=>'master',
                    'geometry_type'=>'point',
                    'is_active'=>false,
                    'requires_verification'=>true,
                    'sumber_data'=>'SIMULASI · Bukan data resmi',
                    'lisensi'=>Dataset::LISENSI_INTERNAL,
                    'cakupan_wilayah'=>'kabupaten',
                ],
            );

            $fieldDefinitions = [
                'jenis' => ['label'=>'Jenis infrastruktur','tipe'=>'string','is_dimension'=>true],
                'nama' => ['label'=>'Nama contoh','tipe'=>'string'],
                'provider' => ['label'=>'Penyedia contoh','tipe'=>'string'],
                'status' => ['label'=>'Status contoh','tipe'=>'string'],
                'kecamatan' => ['label'=>'Kecamatan contoh','tipe'=>'string'],
                'geometri_peta' => ['label'=>'Titik contoh pada peta','tipe'=>'json','lookup_source'=>'geometry:point'],
            ];
            foreach ($fieldDefinitions as $index => $definition) {
                DatasetField::query()->firstOrCreate(
                    ['dataset_id'=>$demoDataset->id,'nama'=>$index],
                    $definition + ['required'=>false,'urutan'=>count(DatasetField::query()->where('dataset_id',$demoDataset->id)->get())],
                );
            }

            $points = [
                ['BTS','SIMULASI · Menara 01','Penyedia A','Aktif (contoh)','Tahuna',3.608,125.495],
                ['BTS','SIMULASI · Menara 02','Penyedia B','Aktif (contoh)','Tabukan Utara',3.738,125.510],
                ['Blankspot','SIMULASI · Area 03',null,'Contoh area perlu verifikasi','Tamako',3.465,125.485],
                ['ISP','SIMULASI · Layanan 04','ISP contoh','Tersedia (contoh)','Manganitu',3.325,125.535],
                ['Internet BAKTI','SIMULASI · Titik 05','BAKTI (contoh)','Beroperasi (contoh)','Nusa Tabukan',3.820,125.420],
                ['Starlink','SIMULASI · Titik 06','Starlink (contoh)','Beroperasi (contoh)','Tahuna Timur',3.615,125.525],
                ['Fiber optik','SIMULASI · Jalur 07','Jaringan contoh','Rencana (contoh)','Tahuna Barat',3.620,125.470],
                ['Gangguan jaringan','SIMULASI · Insiden 08','Jaringan contoh','Tertangani (contoh)','Kendahe',3.775,125.570],
            ];
            foreach ($points as $index => [$kind,$name,$provider,$status,$district,$lat,$lng]) {
                $geometry = ['type'=>'Feature','geometry'=>['type'=>'Point','coordinates'=>[$lng,$lat]],'properties'=>['is_demo'=>true,'label'=>'SIMULASI · Bukan lokasi aktual']];
                $this->insertDraft($demoDataset, $userId, 2026, [
                    'jenis'=>$kind, 'nama'=>$name, 'provider'=>$provider, 'status'=>$status,
                    'kecamatan'=>$district, 'geometri_peta'=>$geometry,
                ], 'Contoh pratinjau antarmuka; koordinat fiktif, bukan lokasi aset atau jangkauan jaringan.');
            }
        });

        $this->command?->info('Contoh monitoring Bupati dibuat sebagai draft bertanda SIMULASI; tidak dihitung sebagai data resmi terbit.');
    }

    private function insertDrafts(Dataset $dataset, int $userId, array $rows, callable $map): void
    {
        foreach ($rows as $row) $this->insertDraft($dataset, $userId, $row['year'], $map($row), 'Contoh untuk menguji tampilan; bukan data resmi Kabupaten Kepulauan Sangihe.');
    }

    private function insertDraft(Dataset $dataset, int $userId, int $year, array $values, string $note): void
    {
        DatasetValue::query()->create([
            'dataset_id'=>$dataset->id,
            'tahun'=>$year,
            'periode_label'=>(string) $year,
            'wilayah_type'=>'kabupaten',
            'wilayah_id'=>null,
            'values'=>$values,
            'status'=>DatasetValue::STATUS_DRAFT,
            'status_verifikasi'=>DatasetValue::STATUS_DRAFT,
            'published_at'=>null,
            'notes'=>self::MARKER.' SIMULASI · '.$note,
            'created_by'=>$userId,
        ]);
    }
}
