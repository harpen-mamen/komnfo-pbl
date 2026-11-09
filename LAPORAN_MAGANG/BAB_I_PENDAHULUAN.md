# BAB I
# PENDAHULUAN

## 1.1 Latar Belakang

### 1.1.1 Latar Belakang Umum

Indonesia sedang berada pada era transformasi digital pemerintahan yang masif, dimana seluruh instansi pemerintah dituntut untuk mengintegrasikan teknologi informasi dalam setiap aspek pelayanan publik. Berdasarkan Instruksi Presiden Nomor 3 Tahun 2023 tentang Program Accelerasi Transformasi Digital Pemerintahan, pemerintah daerah diharapkan dapat membangun sistem informasi yang terintegrasi dan berbasis data untuk meningkatkan efisiensi dan efektivitas pelayanan kepada masyarakat.

Kabupaten Kepulauan Sangihe sebagai salah satu daerah otonom di Provinsi Sulawesi Utara memiliki tantangan tersendiri dalam pengelolaan data pemerintahan. Dengan keterbatasan infrastruktur dan jarak antar pulau yang saling berjauhan, pengelolaan data secara manual menjadi tidak efisien dan rentan terjadi duplikasi data serta inkonsistensi informasi.

Berdasarkan pengalaman empiris selama kegiatan magang di Dinas Komunikasi dan Informatika (Kominfo) Kabupaten Kepulauan Sangihe, penulis mengidentifikasi beberapa permasalahan utama dalam pengelolaan data pemerintahan tingkat kabupaten:

1. **Tidak tersedianya platform terpusat** - Masing-masing OPD mengelola data secara terpisah dalam sistem yang berbeda-beda.

2. **Proses verifikasi data yang tidak terstandar** - Tidak ada workflow yang jelas untuk proses validasi data.

3. **Kesulitan monitoring progress pendataan** - Tanpa dashboard terpusat, sulit memantau progress di setiap wilayah.

4. **Keterbatasan akses data real-time** - Data seringkali hanya tersedia dalam bentuk laporan periodik.

### 1.1.2 Latar Belakang Teknologi

Pengembangan teknologi web modern telah memberikan solusi bagi permasalahan pengelolaan data pemerintahan. Framework Laravel yang berbasis PHP memungkinkan pengembangan aplikasi web yang robust. Filament PHP sebagai admin panel builder menyediakan komponen-komponen UI yang konsisten.

Pemilihan stack teknologi:

1. **Stabilitas** - Laravel proven sebagai framework yang stabil.
2. **Dukungan komunitas** - Dokumentasi lengkap.
3. **Kemampuan scaffolding** - Filament PHP memungkinkan pengembangan CRUD dengan cepat.
4. **Integrasi database** - Eloquent ORM menyediakan abstraction layer yang clean.

### 1.1.3 Relevansi dengan Kurikulum

Program magang MBKM ini sesuai dengan CPL Program Studi Sistem Informasi Politeknik Negeri Nusa Utara:

- **KK1**: Mampu bekerja sama dalam tim pembangunan perangkat lunak berbasis web.
- **KK2**: Mampu merancang dan membangun aplikasi dengan prinsip analisis data.
- **P1**: Menguasai konsep teoritis bidang Sistem Informasi.
- **KU4**: Mampu memecahkan masalah dengan pemikiran logis dan inovatif.

## 1.2 Tujuan dan Manfaat

### 1.2.1 Tujuan Magang

1. **Tujuan Umum**
   - Melakukan pengenalan dan pemahaman terhadap lingkungan kerja di instansi pemerintahan.

2. **Tujuan Khusus**
   - Merancang dan membangun SIPANDU berbasis web.
   - Mengimplementasikan workflow verifikasi data yang terstandar.
   - Membuat dashboard monitoring progress pendataan.
   - Mengintegrasikan data dari berbagai OPD dalam satu platform terpusat.

### 1.2.2 Manfaat Magang

**Bagi Penulis:**
1. Mendapatkan pengalaman langsung dalam pengembangan sistem informasi pemerintahan.
2. Memahami alur kerja dan regulasi pengelolaan data.
3. Meningkatkan kemampuan dalam development tim.
4. Menerapkan pengetahuan teoritis dalam konteks praktis.

**Bagi Dinas Kominfo:**
1. Tersedianya platform digital terpusat untuk pengelolaan data.
2. Adanya sistem verifikasi yang terstandar.
3. Kemudahan monitoring progress pendataan.
4. Pengurangan duplikasi data antar OPD.

**Bagi Pemerintah Kabupaten:**
1. Peningkatan efisiensi pengelolaan data pemerintahan.
2. Transparansi data yang lebih baik kepada publik.
3. Dasar pengambilan keputusan yang lebih terinformasi.

## 1.3 Metode Pelaksanaan

### 1.3.1 Metode Pengumpulan Data

1. **Observasi** - Mengamati proses kerja dan alur data existing.
2. **Wawancara** - Berdiskusi dengan stakeholder terkait kebutuhan sistem.
3. **Studi Dokumentasi** - Mempelajari regulasi dan struktur organisasi.
4. **Studi Pustaka** - Mempelajari best practices dalam development.

### 1.3.2 Metode Pengembangan Sistem

1. **Analisis Kebutuhan** - Identifikasi kebutuhan fungsional dan non-fungsional.
2. **Perancangan Sistem** - Arsitektur database dan UI/UX design.
3. **Implementasi** - Pengembangan menggunakan Laravel dan Filament PHP.
4. **Pengujian** - UAT dengan stakeholder dan bug fixing.
5. **Deploymen** - Konfigurasi server production.

### 1.3.3 Tools dan Teknologi

| Kategori | Tools | Keterangan |
|----------|-------|------------|
| Backend | Laravel 10 | PHP Framework |
| Admin Panel | Filament 2 | Admin Builder |
| Database | MySQL | Relational Database |
| Version Control | Git | Source Code Management |
| API | Laravel REST API | Mobile Integration |

## 1.4 Waktu dan Tempat Pelaksanaan

### 1.4.1 Waktu Pelaksanaan

| No | Kegiatan | Durasi | Periode |
|----|---------|--------|---------|
| 1 | Orientasi dan Pengenalan Lingkungan | 2 minggu | Minggu 1-2 |
| 2 | Analisis Kebutuhan dan Perancangan | 3 minggu | Minggu 3-5 |
| 3 | Pengembangan Core Module | 2 minggu | Minggu 6-7 |
| 4 | Pengembangan Data Master Module | 3 minggu | Minggu 8-10 |
| 5 | Pengembangan Workflow | 4 minggu | Minggu 11-14 |
| 6 | Pengembangan Dashboard | 2 minggu | Minggu 15-16 |
| 7 | Integrasi dan Pengujian | 2 minggu | Minggu 17-18 |
| 8 | Evaluasi dan Laporan | 2 minggu | Minggu 19-20 |

**Total Durasi**: 20 minggu (1 semester)

### 1.4.2 Tempat Pelaksanaan

| Tempat | Peran |
|--------|-------|
| Dinas Kominfo Kabupaten Kepulauan Sangihe | Tempat magang utama |
| Politeknik Negeri Nusa Utara | Konsultasi dengan dosen pembimbing |

## 1.5 Sistematika Laporan

**BAGIAN AWAL**
1. Halaman Judul
2. Halaman Pengesahan
3. Kata Pengantar
4. Daftar Isi
5. Daftar Tabel
6. Daftar Gambar

**BAGIAN ISI**
- BAB I : PENDAHULUAN
- BAB II : GAMBARAN UMUM INSTANSI
- BAB III : ANALISA DAN PERANCANGAN SISTEM
- BAB IV : IMPLEMENTASI DAN HASIL
- BAB V : KESIMPULAN DAN SARAN

**BAGIAN AKHIR**
- Daftar Pustaka
- Lampiran
