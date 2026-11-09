# CLEANUP MIGRATION FILES - 17 OKTOBER 2026

## TUJUAN
Membersihkan migration file yang sudah obsolete (membuat tabel legacy yang sudah dihapus)
agar struktur project lebih rapi dan mudah dipelihara.

## HASIL

| Status | Jumlah |
|---|---|
| **Total migration files sebelumnya** | 89 |
| **Dipindahkan ke `_archive_legacy/`** | 53 |
| **Tetap di folder utama (`migrations/`)** | 37 |
| **Migrations table entries dihapus** | 52 |
| **Migrations table entries tersisa** | 37 |

## FOLDER STRUCTURE

```
database/
└── migrations/
    ├── 2014_..._create_users_table.php
    ├── 2014_..._create_password_reset_tokens_table.php
    ├── 2019_..._create_failed_jobs_table.php
    ├── 2019_..._create_personal_access_tokens_table.php
    ├── 2026_06_22_..._create_permission_tables.php
    ├── 2026_07_05_..._create_workflow_tables.php  (audit_trails, workflow_logs, dll)
    ├── 2026_07_10_..._create_submission_batches_and_items_table.php
    ├── 2026_07_11_..._create_notifications_table.php
    ├── 2026_07_13_..._add_payload_snapshot_to_submission_batches.php
    ├── 2026_07_13_..._add_package_metadata_to_submission_batches.php
    ├── 2026_07_14_..._add_module_and_period_fields_to_submission_batches.php
    ├── 2026_07_14_..._add_final_roles_and_organization_scope.php
    ├── 2026_08_01_..._create_jobs_table.php
    ├── 2026_08_02_..._create_ai_conversations_and_messages_tables.php
    ├── 2026_08_07_..._create_mobile_sync_operations_table.php
    ├── 2026_08_07_..._extend_opds_table_for_government.php
    ├── 2026_08_07_..._extend_users_table.php
    ├── 2026_08_07_..._update_opds_for_sangihe.php
    ├── 2026_08_08_..._add_server_reference_to_mobile_sync_operations.php
    ├── 2026_08_12_..._add_batch_id_to_mobile_sync_operations_table.php
    ├── 2026_08_16_..._add_forward_and_publish_flags_to_submission_batches.php
    ├── 2026_10_12_..._create_datasets_tables.php
    ├── 2026_10_12_..._seed_32_opds.php
    ├── 2026_10_12_..._remove_old_opd_roles_and_add_new_ones.php
    ├── 2026_10_13_..._drop_unused_sectoral_tables.php  (DROP migration, kept)
    ├── 2026_10_13_..._enhance_datasets_with_sdi_metadata.php
    ├── 2026_10_13_..._create_kode_referensis_table.php
    ├── 2026_10_13_..._create_forum_satu_data_tables.php
    ├── 2026_10_13_..._create_workflow_logs_table.php
    ├── 2026_10_13_..._create_import_logs_table.php
    ├── 2026_10_13_..._create_device_assignments_table.php
    ├── 2026_10_13_..._fix_enum_and_add_urutan.php
    ├── 2026_10_13_..._add_enum_to_dataset_fields_tipe.php
    ├── 2026_10_17_..._create_wilayahs_and_categories.php     (SDI)
    ├── 2026_10_17_..._add_sdi_5_elements_to_dataset_fields.php (SDI)
    ├── 2026_10_17_..._cleanup_legacy_tables.php              (SDI DROP)
    ├── 2026_10_17_..._drop_dummy_and_enum_tables.php          (SDI DROP)
    └── _archive_legacy/                                    (53 files obsolete)
        ├── 2026_06_23_..._create_wilayah_tables.php  (kab, kec, desa -> replaced)
        ├── 2026_06_23_..._create_admin_desa_tables.php (rumah, keluarga, penduduk)
        ├── 2026_07_31_..._create_kasus_penyakits_table.php
        ├── 2026_07_31_..._create_public_content_tables.php
        ├── 2026_08_04_..._create_ibu_hamils_table.php
        ├── 2026_08_04_..._create_bayis_table.php
        ├── 2026_08_10_..._create_fasilitas_kesehatans_table.php
        ├── 2026_08_10_..._create_sekolahs_table.php
        ├── ... (53 files total)
        └── README.md (penjelasan folder)
```

## TABEL YANG DIBUAT OLEH FILE OBSOLETE

Tabel-tabel ini dibuat oleh migration file obsolete dan sudah dihapus oleh:
- 2026_10_13_000001_drop_unused_sectoral_tables.php
- 2026_10_17_000003_cleanup_legacy_tables.php
- 2026_10_17_000004_drop_dummy_and_enum_tables.php

Tabel yang dihapus:
- `data_kesehatan_bulanans`, `data_pendidikan_bulanans`
- `bayis`, `ibu_hamils`, `kasus_penyakits`, `pelayanan_kesehatans`
- `rekam_medis`, `stok_obats`, `tenaga_kesehatans`
- `sekolahs`, `fasilitas_kesehatans`
- `data_gurus`, `data_siswas`, `data_prasaranas`
- `program_bantuans`, `penerima_bantuans`
- `tentang_opd`, `tentang_sejarah`, `tentang_visi_misi`
- `tentang_opd_galleries`
- `about_page_settings`, `about_section_contents`, `about_section_media`
- `pejabat_profiles`, `user_registrations`, `data_kendalas`
- `source_attributions`
- `kabupatens`, `kecamatans`, `desas`, `dusuns`
- `objek_lapangans`, `objek_lapangan_types`
- `periode_pelaporans`
- `workflow_status`, `workflow_instances`, `workflow_histories`
- `verification_comments`, `audit_trails` (?)
- `keluarga`, `penduduk`, `rumah`, `rumah_keluarga`
- `kecamatan_submissions`
- `import_penduduk_logs`
- `public_news`, `public_hero_settings`

## TESTING

Setelah cleanup:
- `php artisan migrate:status` -> SEMUA "Ran" ✓
- `composer dump-autoload` -> OK ✓
- Tabel DB: 31 (tetap) ✓
- Wilayah: 16 (Kab + 15 Kec) ✓
- 32 Urusan: 32 ✓
- OPD: 39 ✓
- Datasets: 90 ✓
- Fields: 262 ✓
- All 15 model load OK ✓

## MENGAPA FILE OBSOLETE TIDAK DIHAPUS?

Saya TIDAK menghapus file migration, hanya memindahkannya ke `_archive_legacy/`.
Tujuannya:
1. **Safety**: Jika ada masalah, file bisa dikembalikan
2. **Historical record**: Dokumentasi langkah-langkah refactor
3. **Audit trail**: Untuk compliance audit

## UNTUK MIGRATE FRESH DI MASA DEPAN

Jika dijalankan `php artisan migrate:fresh` di kemudian hari:
1. Migration file obsolete di `_archive_legacy/` TIDAK akan dijalankan
2. Hanya 37 migration di folder utama yang akan dijalankan
3. Tabel yang dibuat oleh file obsolete TIDAK akan dibuat (karena file tidak ada)
4. Urutan run akan menghasilkan schema SDI final yang sama

## BACKUP

- `backups/migrations_table_backup_20261017_132147.sql` - backup tabel migrations sebelum cleanup
- `backups/kominfo_full_backup_*.sql` - backup full DB sebelum rombak

