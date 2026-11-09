# SIPDT Mobile

Aplikasi offline-first untuk pendataan titik lapangan Kabupaten Kepulauan Sangihe.

## Cakupan

- Input titik Rumah dan Infrastruktur beserta koordinat GPS.
- Peta menampilkan titik lokal/offline dan semua titik lapangan server, termasuk yang menunggu verifikasi; tersedia layer peta jalan dan citra satelit.
- Pilih keluarga dari data OPD yang sudah terbit untuk desa petugas. Mobile menyimpan referensi ID keluarga, bukan membuat atau mengubah data KK/Penduduk.
- Draft tersimpan lokal saat offline. Server memvalidasi desa pada cakupan akun.
- Submission titik masuk ke alur verifikasi lapangan Kecamatan.

## Menjalankan

```powershell
cd C:\pbl
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

Di terminal lain:

```powershell
cd C:\pbl\mobile
flutter pub get
flutter run
```

Android emulator memakai `http://10.0.2.2:8000/api/v1` secara default. Untuk perangkat fisik:

```powershell
flutter run --dart-define=SIPDT_API_BASE_URL=http://192.168.1.10:8000/api/v1
```

Ganti IP sesuai komputer server. HP dan komputer harus pada jaringan yang sama dan firewall perlu mengizinkan TCP port 8000. Akun `admin_lapangan` atau `admin_desa` harus terikat ke desa, atau ke kecamatan untuk memilih desa dalam wilayah tugasnya; perangkat harus diaktivasi lewat panel web. Server memvalidasi desa pada setiap lookup dan kiriman.

## Sumber keluarga OPD

OPD DUKCAPIL mengelola dataset `DATA_KELUARGA_DESA` (cakupan `desa`). Isi `nomor_kk`, `nama_kepala_keluarga`, dan opsional `jumlah_anggota` melalui Nilai Data OPD, lalu kirim, verifikasi, dan terbitkan. Lookup mobile hanya menampilkan keluarga terbit yang wilayahnya sama dengan desa rumah. Kiriman mobile menyimpan referensi ID dataset untuk menautkan titik; data KK dan Penduduk tidak disalin ke submission dan tetap dikelola OPD.

## Alur kerja

1. Petugas masuk dari perangkat yang sudah diaktivasi.
2. Petugas mencatat titik Rumah atau Infrastruktur dan mengambil GPS.
3. Untuk Rumah, petugas dapat memilih keluarga terbit dari OPD untuk desanya.
4. Draft dikirim sebagai submission lapangan untuk ditinjau Kecamatan.
