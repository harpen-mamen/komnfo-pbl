# SIPDT Mobile

Aplikasi mobile offline-first khusus `Admin Desa` untuk pendataan lapangan Kabupaten Kepulauan Sangihe.

## Scope aplikasi

- Rumah dan koordinat GPS.
- Kartu Keluarga yang terhubung ke Rumah.
- Penduduk yang terhubung ke Kartu Keluarga.
- Draft lokal yang dikirim sekaligus ketika jaringan tersedia.
- Peta Rumah dari data desa yang sudah tersimpan di server.

Data Kesehatan dan Pendidikan tidak berada di aplikasi ini. Keduanya tetap dikelola oleh Admin/Operator OPD melalui dashboard web.

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

Android emulator menggunakan `http://10.0.2.2:8000/api/v1`. Untuk perangkat fisik:

```powershell
flutter run --dart-define=SIPDT_API_BASE_URL=http://192.168.1.10:8000/api/v1
```

Ganti alamat IP sesuai komputer server. Akun harus memiliki role `admin_desa` dan `desa_id`.

## Alur kerja

1. Admin Desa login.
2. Bootstrap mengambil data Rumah, KK, Penduduk, Desa, dan Dusun milik desa tersebut.
3. Admin Desa membuat Rumah dan mengambil titik GPS, lalu membuat KK dan Penduduk melalui relasi yang tersedia.
4. Semua input masuk ke tab `Pengiriman Draft` dengan status menunggu.
5. Saat jaringan tersedia, tekan tombol kirim. Server memproses Rumah → KK → Penduduk secara berurutan ke tabel domain web.
