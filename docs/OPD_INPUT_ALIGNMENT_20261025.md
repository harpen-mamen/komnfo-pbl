# Penyesuaian format input OPD

## Hasil dan lingkup

Diterapkan pada database utama `kominfo` setelah uji preview. Tidak ada penghapusan field/data historis, seeding ulang, perubahan akun, atau penimpaan angka sumber. Snapshot pra-perubahan metadata dan nilai: `storage/backups/form-alignment-kominfo-20261025-090904.json` (bukan backup seluruh tabel aplikasi).

Audit sebelumnya menemukan 80 dari 492 baris tidak cocok dengan validasi formulir. Setelah penyesuaian, seluruh 492 baris lulus kontrak input, dengan adaptasi eksplisit data jalan lama ke indikator terstruktur. Dataset aktif berjumlah 180 di 32 OPD; 54 dataset memiliki contoh data untuk tes simpan. Sebanyak 126 dataset tanpa contoh hanya diperiksa struktur/render, belum dapat dinyatakan cocok terhadap isian nyata atau semua format dokumen OPD.

## Perubahan

| Dataset | Penyesuaian |
| --- | --- |
| 3.16 Pendidikan | Kolom utama jenjang, kelompok usia, jumlah sekolah, penduduk usia sekolah dan rasio sumber. APM/APS/APK lama disembunyikan dan tidak diwajibkan pada dataset ketersediaan sekolah. Kolom lama tetap disimpan untuk histori. |
| 3.20 Kesehatan | Nama puskesmas tetap pilihan daftar; kode internal tidak diwajibkan. Angka sumber persentase dapat diisi, atau dikosongkan agar dihitung dari bahan yang lengkap. |
| 3.33 Pemuda/Olahraga; 3.34 Tenaga Kerja | Pilihan indikator diselaraskan dengan label yang sudah terdapat dalam data terbit, bukan menebak nilai/indikator baru. |
| 3.35 dan 3.36 Tenaga Kerja | Kecamatan menjadi referensi wilayah aktif bertipe kecamatan, bukan teks bebas. Periode diselaraskan ke tahunan sesuai data tabel yang dipakai. Metadata kecamatan juga diperbarui ketika draft diedit. |
| 4.47 Perikanan | Harga boleh kosong jika tidak dapat dipastikan dari sumber. Nilai produksi sumber tidak diganti hasil perkalian angka yang sudah dibulatkan. |
| 5.59 PUPR | Pilihan 13 indikator jalan/jembatan menggantikan penulisan kategori/uraian bebas. Satuan ditentukan sistem: km, unit, atau m. Jumlah jembatan wajib bilangan bulat. Uraian lama yang dikenal tetap dapat dibaca tanpa menulis ulang angka tersimpan. |
| 8.82 Satpol PP | Status kepegawaian dan jumlah personel menjadi kolom utama sesuai tabel sumber. Kolom jabatan lama dipertahankan untuk histori, tidak lagi diwajibkan. |
| 8.85 Lingkungan Hidup | Indikator dan persentase memakai field sumber; field persentase lama yang berbeda nama tidak diwajibkan. |
| 8.86 Persampahan | Jenis sarana dan jumlah sarana sesuai tabel sumber; kolom uraian/jumlah lama tidak diwajibkan atau dihapus. |

Perbaikan bersama:

- Tahun dipilih satu kali pada konteks formulir. Jika schema memiliki `values.tahun`, server menyinkronkannya dari tahun utama.
- Field hasil perhitungan konsisten dengan kalkulator (misalnya `total`, bukan `jumlah` untuk penduduk/KK). Angka sumber yang diberikan selalu dipertahankan; perhitungan hanya mengisi hasil kosong bila bahan lengkap. Jika ingin menghitung ulang, kosongkan hasil terlebih dahulu. Input parsial tidak diperlakukan sebagai nol.
- Field opsional yang tidak dikirim saat edit tidak menghapus data historis lain; null eksplisit tetap dapat mengosongkan field.
- Input desimal memakai step `any`, bukan hanya dua angka desimal. Latitude/longitude memiliki batas -90..90 dan -180..180. Batas numerik yang tersedia pada metadata dipakai di formulir dan validasi server.
- Input datetime menggunakan DateTimePicker. Konteks wilayah kecamatan/desa menyimpan tipe wilayahnya; referensi kecamatan memvalidasi wilayah aktif dengan tipe yang sesuai.
- Form edit memakai dataset milik record, termasuk pada request Livewire tanpa parameter `dataset` di URL.
- Infografis jalan/jembatan membaca pilihan indikator baru maupun uraian lama yang dikenal; satuan tidak lagi tergantung ketikan bebas petugas.

## Mekanisme penerapan

`php artisan app:align-opd-input-forms` default ke preview dan rollback. `--apply` menyimpan setelah membuat snapshot. `--database=mysql --apply` secara eksplisit menyimpan metadata ke `kominfo`. Perintah tidak mengubah `dataset_values`; field lama hanya menjadi opsional/disembunyikan, tidak dihapus. Tidak perlu menjalankan migrasi historis atau `migrate:fresh`.

## Verifikasi

- `php scripts/check_opd_form_alignment.php`: 492/492 contoh sumber berhasil divalidasi, dibuat sebagai draft dan diedit pada preview; angka sumber dibandingkan, seluruh baris pengujian di-rollback.
- `php scripts/check_opd_livewire_forms.php`: 54/54 formulir Filament/Livewire berhasil menyimpan contoh sumber menjadi draft; seluruh pengujian di-rollback.
- `php scripts/check_opd_livewire_forms.php --edit-smoke`: lima contoh create/edit melalui Livewire (3.13, 3.16, 3.35, 4.47, 5.59), termasuk URL edit tanpa parameter dataset, lulus.
- `php scripts/audit_dataset_pages.php --offset=0 --limit=180`: 540 respons daftar/formulir/infografis preview, nol kegagalan.
- Tujuh tes unit, 28 assertions: kalkulasi kosong/nol, menjaga angka sumber, pilihan/satuan jalan, format dan keterbacaan infografis.
- Workflow preview create/update/submit/verify/publish lulus dengan tiga log; perubahan pengujian dibatalkan.
- Pemeriksaan aktif `check_opd_form_alignment.php --active` membaca 180 dataset dan 492 baris, nol error tanpa melakukan tes insert/update di database utama.
- Pemeriksaan sumber aktif: 220 baris statistik sumber tetap cocok, total 492 nilai tidak berubah, empat titik peta dan 17 fasilitas tanpa koordinat tetap terjaga.

Tes Livewire memeriksa server dan state komponen; bukan pengganti uji browser interaktif pada semua ukuran layar, input tanggal, dropdown pencarian, dan gambar geometri Leaflet. Metadata asli dan data sumber historis tetap perlu dirawat bila OPD menerbitkan format baru.
