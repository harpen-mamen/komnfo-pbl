================================================================================
                    DAFTAR FILE UNTUK DIHAPUS - REVIEW MANUAL
================================================================================

RINGKASAN:
- TABEL DATABASE: 27 tabel sudah dihapus (via migration 2026_10_13_000001)
- MODEL TERSISA:   26 model perlu dihapus dari codebase
- FILE LAIN:       Commands, Policies, Resources (_archived)

================================================================================
KATEGORI 1: MODELS (Tabel sudah dihapus - WAJIB DIHAPUS)
================================================================================

1.  app/Models/AboutPageSetting.php
2.  app/Models/AboutSectionContent.php
3.  app/Models/AboutSectionMedia.php
4.  app/Models/Bayi.php
5.  app/Models/DataGuru.php
6.  app/Models/DataSiswa.php
7.  app/Models/DataPrasarana.php
8.  app/Models/DataKendala.php
9.  app/Models/DataKesehatanBulanan.php
10. app/Models/DataPendidikanBulanan.php
11. app/Models/FasilitasKesehatan.php
12. app/Models/IbuHamil.php
13. app/Models/KasusPenyakit.php
14. app/Models/PelayananKesehatan.php
15. app/Models/RekamMedis.php
16. app/Models/Sekolah.php
17. app/Models/StokObat.php
18. app/Models/TenagaKesehatan.php
19. app/Models/PublicNews.php
20. app/Models/PublicHeroSetting.php
21. app/Models/PejabatProfile.php
22. app/Models/TentangOpdGallery.php
23. app/Models/ProgramBantuan.php
24. app/Models/PenerimaBantuan.php
25. app/Models/SourceAttribution.php
26. app/Models/UserRegistration.php

TOTAL: 26 files

================================================================================
KATEGORI 2: CONSOLE COMMANDS (Legasi - BISA DIHAPUS)
================================================================================

1. app/Console/Commands/BackfillSourceAttributions.php
2. app/Console/Commands/MigrateOldToNew.php
3. app/Console/Commands/MigrateRumahToObjekLapangan.php
4. app/Console/Commands/SeedDatasetsFromSectoral.php
5. app/Console/Commands/SeedObjekLapanganTypes.php
6. app/Console/Commands/CreateOpdUser.php
7. app/Console/Commands/FilamentCheck.php

TOTAL: 7 files

================================================================================
KATEGORI 3: POLICIES (Model dihapus - WAJIB DIHAPUS)
================================================================================

1.  app/Policies/BayiPolicy.php
2.  app/Policies/IbuHamilPolicy.php
3.  app/Policies/FasilitasKesehatanPolicy.php
4.  app/Policies/KasusPenyakitPolicy.php
5.  app/Policies/PelayananKesehatanPolicy.php
6.  app/Policies/RekamMedisPolicy.php
7.  app/Policies/StokObatPolicy.php
8.  app/Policies/TenagaKesehatanPolicy.php
9.  app/Policies/DataKesehatanBulananPolicy.php
10. app/Policies/DataPendidikanBulananPolicy.php
11. app/Policies/ImportPendudukLogPolicy.php

TOTAL: 11 files

================================================================================
KATEGORI 4: FILAMENT RESOURCES (_archived folder)
================================================================================

Path: app/Filament/Resources/_archived/
Folders & Files: 101 PHP files

Struktur:
- AboutPageSettingResource/      (3 files)
- AboutSectionContentResource/   (4 files)
- BayiResource/                 (4 files)
- DataGuruResource/             (5 files)
- DataKesehatanBulananResource/ (5 files)
- DataPendidikanBulananResource/(5 files)
- DataPrasaranaResource/        (5 files)
- DataSiswaResource/            (5 files)
- FasilitasKesehatanResource/   (5 files)
- IbuHamilResource/             (4 files)
- ImportPendudukLogResource/    (3 files)
- KasusPenyakitResource/        (4 files)
- PejabatProfileResource/       (4 files)
- PelayananKesehatanResource/    (4 files)
- PublicHeroSettingResource/    (4 files)
- PublicNewsResource/           (4 files)
- RekamMedisResource/           (5 files)
- RumahResource/                (6 files + RelationManager)
- SekolahResource/              (5 files)
- StokObatResource/            (5 files)
- TenagaKesehatanResource/     (4 files)

TOTAL: 101 files (seluruh folder _archived)

================================================================================
PERHATIAN: FILE YANG TIDAK BOLEH DIHAPUS
================================================================================

- app/Models/Rumah.php             (Tabel RUMAH masih ada)
- app/Models/Keluarga.php           (Tabel KELUARGA masih ada)
- app/Models/Penduduk.php           (Tabel PENDUDUK masih ada)
- app/Models/ObjekLapangan.php      (Tabel ada)
- app/Models/ObjekLapanganType.php  (Tabel ada)
- app/Models/Dataset.php            (Tabel DATASETS ada)
- app/Models/DatasetField.php       (Tabel DATASET_FIELDS ada)
- app/Models/DatasetValue.php       (Tabel DATASET_VALUES ada)
- app/Models/Opd.php                (Tabel OPDS ada)
- app/Models/Desa.php               (Tabel DESAS ada)
- app/Models/Kecamatan.php          (Tabel KECAMATANS ada)
- app/Models/Kabupaten.php          (Tabel KABUPATENS ada)
- app/Models/Dusun.php              (Tabel DUSUNS ada)
- app/Models/User.php               (Tabel USERS ada)
- app/Models/ImportLog.php          (Tabel IMPORT_LOGS ada)

================================================================================
TOTAL FILE UNTUK DIHAPUS: 145 items
================================================================================

  - Models:      26 files
  - Commands:     7 files
  - Policies:    11 files
  - Resources:  101 files (folder _archived)
  -------------------
  TOTAL:        145 items

================================================================================
AKSI SELANJUTNYA
================================================================================

1. Review daftar di atas
2. Pastikan backup database sudah dibuat
3. Jalankan command hapus atau buat script cleanup
4. Test aplikasi setelah cleanup

================================================================================
