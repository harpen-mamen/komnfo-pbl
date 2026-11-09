# Audit Dashboard Admin

Tanggal audit: 25 Oktober 2026

## Baseline

- Laravel 10.50.2, PHP 8.3.33, Filament 2.17.59.
- Panel utama tersedia pada `/admin` dan seluruh resource/page admin aktif didaftarkan melalui Filament.
- Livewire dan Alpine.js berasal dari stack Filament dan tetap dipertahankan.
- Chart.js dan Leaflet tetap dipertahankan karena dipakai oleh executive dashboard, infografis, dan peta pintar.
- Tidak ditemukan Bootstrap, AdminLTE, CoreUI, Bulma, Materialize, atau jQuery sebagai dependency aplikasi admin.

## Temuan dan tindakan

1. Panel sebelumnya memuat `filament-custom.css`, `sipandu-design-system.css`, dan `sangihe-ui.css` secara bersamaan. Ketiganya mengubah selector global Filament dan bergantung pada urutan asset.
2. Tema admin kini memiliki satu sumber Tailwind di `resources/css/filament/admin.css`, satu konfigurasi di `tailwind.admin.config.js`, dan satu output terkompilasi di `public/css/filament/admin.css`.
3. Dashboard OPD sebelumnya memakai blok CSS inline. Markup telah dipindahkan ke utility Tailwind, dibuat responsif, dan diberi empty state yang konsisten.
4. Sidebar desktop sekarang dapat diciutkan, memiliki lebar eksplisit, dan tetap memakai state Alpine bawaan Filament.
5. Label waktu dashboard diperbaiki dari WIB menjadi WITA agar sesuai dengan lokasi aplikasi.

## Kandidat pembersihan lanjutan

File berikut tidak lagi dimuat oleh panel, tetapi belum dihapus karena worktree mengandung perubahan pengguna dan beberapa class-nya dapat masih dipakai oleh halaman khusus:

- `public/css/filament-custom.css`
- `public/css/sipandu-design-system.css`
- `public/css/sangihe-ui.css`
- `public/css/sangihe-premium-part1.css` (kosong)
- `resources/css/dashboard.css`
- `resources/css/kominfo-theme.css`

Override di `resources/views/vendor/filament` juga belum dihapus. Folder tersebut berisi 83 file dan perlu dibandingkan per file dengan Filament 2.17.59 sebelum republish atau penghapusan.

## Build dan verifikasi

- Build tema: `cd frontend && npm run build:admin-css`
- Cache Blade berhasil dibuat.
- Seluruh 75 rute admin berhasil diregistrasi.
- Feature test saat audit: 2 lulus, 40 gagal. Semua kegagalan yang tampil berhenti pada migrasi lama `workflow_instances.kecamatan_id` yang masih merujuk tabel `kecamatans`; bukan pada tema, Blade, atau rute dashboard.

## Catatan risiko

Perbaikan foreign key migrasi tidak termasuk dalam refactor UI ini. Mengubahnya tanpa menyelaraskan migrasi arsip dan model wilayah dapat memengaruhi data/workflow, sehingga harus ditangani sebagai pekerjaan database terpisah.
