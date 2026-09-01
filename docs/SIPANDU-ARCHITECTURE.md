# Arsitektur SIPANDU

## Tujuan

SIPANDU (Sistem Informasi Pangkalan Data Terpadu) adalah modular monolith Laravel untuk pengumpulan, validasi, publikasi, dan analitik data Kabupaten Kepulauan Sangihe.

## Batas kewenangan

| Role | Membuat/mengubah data | Mengirim batch | Memverifikasi | Melihat cakupan |
|---|---|---|---|---|
| Super Admin | Administrasi sistem | - | - | Seluruh kabupaten |
| Admin Kominfo | Monitoring dan koreksi terbatas | - | Ya | Seluruh kecamatan |
| Admin Kecamatan | Penduduk, keluarga, rumah, kesehatan, pendidikan | Ya | - | Kecamatan sendiri |
| Admin Desa | Data desa dan draft pengajuan | Ya | - | Desa sendiri |
| Operator OPD | Data sektoral OPD | Ya | - | OPD/area yang ditugaskan |

## Alur data resmi

1. Operator mengisi atau mengimpor data sebagai draft.
2. Data dikumpulkan ke satu `submission_batch` berdasarkan kecamatan, periode, dan modul.
3. Pengirim mengirim batch; status berubah menjadi `pending`.
4. Admin Kominfo memilih kecamatan dan meninjau seluruh item batch.
5. Kominfo menyetujui, meminta revisi, atau menolak batch.
6. Hanya batch disetujui yang dipublikasikan ke dashboard agregat dan analitik.
7. Setiap perpindahan status dicatat di workflow history dan menghasilkan notifikasi penerima.

## Bounded context

- **Identity & Access**: users, roles, permissions, assignment wilayah/OPD.
- **Reference Data**: kabupaten, kecamatan, desa, batas wilayah, periode.
- **Population**: penduduk, keluarga, rumah.
- **Sectoral Data**: kesehatan, pendidikan, dan data OPD.
- **Submission Workflow**: batch, item, snapshot, status, verifikasi, publikasi.
- **Analytics & Map**: agregasi terverifikasi, statistik, peta dan batas wilayah.
- **Audit & Notification**: audit trail, workflow history, notifikasi database.

## Aturan penting

- Data mentah/draft tidak boleh masuk statistik resmi.
- Query dashboard kabupaten hanya membaca data yang telah dipublikasikan.
- Query operator selalu dibatasi assignment wilayah atau OPD.
- Batch tidak boleh diverifikasi dua kali secara bersamaan; transisi menggunakan transaksi dan row lock.
- Penghapusan data operasional menggunakan audit trail; hindari hard delete.

## Jalur pengembangan

Fondasi yang diprioritaskan: RBAC dan scoping query, workflow batch per kecamatan, notifikasi topbar, audit trail, kemudian analitik dan peta. Modular monolith dipertahankan sampai beban dan kebutuhan integrasi benar-benar memerlukan pemisahan layanan.
