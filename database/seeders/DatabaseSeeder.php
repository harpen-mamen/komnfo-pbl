<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tahap 1: Role dan permission (existing)
        $this->call(RolePermissionSeeder::class);

        // Tahap 2: Master Wilayah Sangihe (NEW)
        $this->call(WilayahSangiheSeeder::class);
        $this->call(SangiheDesaSeeder::class);

        // Tahap 3: 32 Urusan Pemerintah (NEW)
        $this->call(DatasetCategorySangiheSeeder::class);

        // Tahap 4: 90 Dataset Sektoral Sangihe (NEW)
        $this->call(DatasetSektoralSangiheSeeder::class);

        // Dataset operasional OPD, termasuk ruas jalan, jembatan, dan sarana air.
        $this->call(OpdDatasetSangiheSeeder::class);

        // Field peta tidak menghapus definisi field maupun nilai dataset yang sudah ada.
        $this->call(DatasetSpatialFieldsSeeder::class);

        // Add the field definitions required by the official district statistics import.
        $this->call(DatasetFieldUpgradeSeeder::class);

        // APBD monitoring includes physical as well as financial progress.
        $this->call(ApbdPhysicalFieldsSeeder::class);

        // Angka resmi tingkat kecamatan dari Statistik Sektoral 2024.
        $this->call(OfficialDistrictStatisticsSeeder::class);

        // Tahap 5: Production User (existing)
        $this->call(ProductionUserSeeder::class);

        // Default public portal banner and service cards. firstOrCreate preserves admin edits.
        $this->call(PortalContentSeeder::class);

        // Berita kurasi dari situs resmi Pemkab Sangihe; upsert agar aman dijalankan ulang.
        $this->call(SangiheOfficialNewsSeeder::class);
    }
}
