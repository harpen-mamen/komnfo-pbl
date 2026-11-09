<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use Illuminate\Database\Seeder;

/** Add the physical progress fields required by the BPKPD monitoring view. */
class ApbdPhysicalFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $dataset = Dataset::query()->where('kode', '7.73')->whereNull('deleted_at')->first();
        if (! $dataset) {
            $this->command?->warn('Dataset 7.73 belum tersedia; field realisasi fisik dilewati.');
            return;
        }

        $order = (int) $dataset->fields()->max('urutan') + 1;
        foreach ([
            'target_fisik' => ['Target Fisik', $order],
            'realisasi_fisik' => ['Realisasi Fisik', $order + 1],
        ] as $name => [$label, $position]) {
            DatasetField::query()->firstOrCreate(
                ['dataset_id' => $dataset->id, 'nama' => $name],
                ['label' => $label, 'tipe' => 'decimal', 'satuan' => 'Persen', 'required' => false, 'is_dimension' => false, 'urutan' => $position],
            );
        }
    }
}
