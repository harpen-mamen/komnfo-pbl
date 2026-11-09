# Perbaikan keterbacaan infografis

## Masalah yang ditemukan

Pada dataset 5.59 (PUPR, OPD 17), generator lama mengelompokkan berdasarkan kategori umum lalu menjumlahkan field `nilai`. Akibatnya, kategori Jembatan berisi 170 unit + 1.714 meter = 1.884 tanpa satuan yang masuk akal. Rincian konstruksi dan kondisi jalan juga masing-masing menjadi satu batang, padahal keduanya klasifikasi dari panjang jalan kabupaten yang sama. Kartu berketinggian tetap memotong bagian bawah grafik.

Rujukan yang diperiksa: publikasi lokal Statistik Sektoral 2024, tabel 5.59, halaman tercetak 110. Data asli tidak diubah.

## Penyajian baru

- Lima kelompok: panjang jalan menurut kewenangan (km), permukaan jalan kabupaten (km), kondisi jalan kabupaten (km), jumlah jembatan (unit), panjang keseluruhan jembatan (m).
- Panjang jalan kabupaten tetap 379,97 km; jembatan tetap 170 unit dan 1.714 m, tidak digabung.
- Komponen kartu bersama pada infografis OPD dan infografis dataset: judul deskriptif, angka selalu terlihat, satuan, tahun, penjelasan, catatan, serta tabel angka yang dapat dibuka.
- Kartu indikator tunggal memakai angka utama; rincian memakai batang horizontal berbasis HTML/Tailwind, bukan canvas dan tooltip wajib. Tidak ada tinggi kartu tetap atau Chart.js/CDN untuk grafik ini. Library chart pada fitur lain tidak dihapus.
- Batang memakai skala pembanding terhadap angka terbesar, bukan persentase total. Angka negatif mempertahankan tandanya; bar menunjukkan besar absolut dan diberi keterangan.
- Daftar panjang menampilkan delapan rincian terlebih dahulu, dengan tombol membuka seluruh rincian. Tabel lengkap tetap tersedia.
- Pemilih tahun pada halaman OPD dan navigasi bagian per dataset. Data kategorikal dengan beberapa tahun dipisahkan per tahun; grafik tren tetap memakai periode.
- Tahun, sumber, nomor tabel/halaman bila tersedia, serta batasan data ditampilkan. Jumlah catatan dibedakan dari jumlah objek.
- Field referensi wilayah/OPD memakai nama, bukan ID database. Lookup label disimpan dalam memori service untuk menghindari query per batang.
- Singkatan AKI, AKB, AHH, SPBE, TPT dan TPAK diberi nama lengkap berdasarkan istilah dalam publikasi; definisi field yang tersedia ikut ditampilkan pada kartu.
- Satuan yang tidak tersedia diberi peringatan, bukan ditebak dari label tipe seperti Numerik/Teks. Satuan campuran dipisahkan. Angka 0,82034 tidak dibulatkan menjadi 0,82 saat pengelompokan tampilan.
- Popup peta meng-escape konten data; Leaflet dimuat hanya saat ada lokasi. Data yang tidak memiliki koordinat tetap tidak diberi titik palsu.

## File utama

- `app/Services/InfographicPresentationService.php`: konteks sumber, judul dan penjelasan, format angka, penyajian khusus data jalan/jembatan.
- `app/Services/DynamicChartService.php`: pemisahan tahun/satuan, presisi, nama referensi.
- `resources/views/components/infographic-chart.blade.php`: kartu dan tabel bersama.
- `resources/views/filament/pages/infografis-opd.blade.php`, `infografis-dataset.blade.php`: halaman infografis.
- `resources/views/filament/partials/infographic-maps.blade.php`: pemuatan peta dan popup aman.
- `tailwind.admin.config.js`: memasukkan folder komponen Blade ke pemindaian kelas.

## Verifikasi

`php vendor/bin/phpunit tests/Unit/InfographicPresentationTest.php`: tiga tes, 16 assertions, seluruhnya dalam memori tanpa database. Mencakup pemisahan jumlah/panjang jembatan, satuan/tahun, null, presisi, format angka dan satuan tidak diketahui.

`php scripts/check_infographic_redesign.php --all`: 212 respons infografis OPD/dataset pada database aktif, tanpa kegagalan. Memeriksa lima kelompok PUPR, angka sumber, tampilan nilai, dan filter tahun 2024. Ini pemeriksaan render server, bukan browser interaktif.

`php scripts/check_sangihe_enrichment.php --active`: 220 baris statistik sumber masih cocok; total 180 dataset dan 492 nilai tidak berubah; empat titik peta dan 17 fasilitas tanpa koordinat tetap terjaga.

Build Tailwind berhasil. Uji visual layar desktop/mobile, interaksi Alpine/Livewire, dan akses tile peta pada browser masih perlu pemeriksaan langsung. Tidak ada perubahan password, seeding ulang, atau migrasi database pada pekerjaan ini.
