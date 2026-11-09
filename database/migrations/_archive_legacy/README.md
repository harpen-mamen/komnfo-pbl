# ARCHIVE: Migration Legacy (Tidak Lagi Dijalankan)

Folder ini berisi 53 migration file yang sudah **OBSOLETE** (membuat tabel legacy yang sudah dihapus).

## Daftar File (53)

File-file di folder ini membuat tabel-tabel yang sudah dihapus:
- `data_kesehatan_bulanans`, `data_pendidikan_bulanans`
- `bayis`, `ibu_hamils`, `kasus_penyakits`, `pelayanan_kesehatans`
- `rekam_medis`, `stok_obats`, `tenaga_kesehatans`
- `sekolahs`, `fasilitas_kesehatans`
- `data_gurus`, `data_siswas`, `data_prasaranas`
- `program_bantuans`, `penerima_bantuans`
- `tentang_opd`, `tentang_sejarah`, `tentang_visi_misi`
- `about_page_settings`, `about_section_contents`, `about_section_media`
- `pejabat_profiles`, `user_registrations`, `data_kendalas`
- `source_attributions`
- `kabupatens`, `kecamatans`, `desas`, `dusuns`
- `objek_lapangans`, `objek_lapangan_types`
- `periode_pelaporans`
- `workflow_status`, `workflow_instances`, `workflow_histories`
- `verification_comments`
- `keluarga`, `penduduk`, `rumah`, `rumah_keluarga`
- `kecamatan_submissions`, `import_penduduk_logs`
- `public_news`, `public_hero_settings`

## Mengapa Diarsipkan (Bukan Dihapus)?

1. **Safety**: File bisa dikembalikan jika ada masalah
2. **Historical record**: Dokumentasi langkah-langkah refactor
3. **Audit trail**: Untuk compliance audit Perpres 39/2019 + Perbup Sangihe 30/2024

## Mengapa Tidak Dijalankan di `migrate:fresh`?

- File di folder ini TIDAK di-load oleh Laravel Migrator
- Schema akhir setelah `migrate:fresh` adalah schema SDI final (31 tabel)
- 37 migration di folder utama sudah mencakup semua yang dibutuhkan

## Tanggal Arsip
17 Oktober 2026 - Refactor SDI Kabupaten Kepulauan Sangihe
