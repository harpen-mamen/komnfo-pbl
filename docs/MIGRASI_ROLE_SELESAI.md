# Status Migrasi - Struktur Role Baru

## ✓ Perubahan yang Sudah Dilakukan

### 1. Enums (AppRole.php)
- Menghapus role lama: `admin_puskes`, `operator_puskes`, `admin_sekolah`, `operator_sekolah`, dll
- Menambahkan role baru:
  - `admin_bupati` - Admin Bupati
  - `admin_wakil_bupati` - Admin Wakil Bupati
  - `kepala_dinas` - Kepala Dinas
  - `operator_opd` - Operator OPD generik
  - `admin_berita` - Admin Berita & Halaman Publik

### 2. Database Migration
- Migration `2026_10_12_140618_remove_old_opd_roles_and_add_new_ones` sudah dijalankan
- Role lama (puskes, sekolah, dll) sudah dihapus
- Role baru sudah dibuat

### 3. Seeder
- `RolePermissionSeeder` sudah dijalankan
- Total 80 role tersedia

---

## Struktur Role Akhir

### Level Kabupaten (7 Role)
| Role | Keterangan |
|------|------------|
| `super_admin` | Super Administrator |
| `admin` | Administrator |
| `admin_kominfo` | Admin Kominfo |
| `walidata` | Walidata |
| `admin_bupati` | Admin Bupati |
| `admin_wakil_bupati` | Admin Wakil Bupati |

### Level OPD (32 OPD + Generic)
| Role | Keterangan |
|------|------------|
| `kepala_dinas` | Kepala Dinas |
| `admin_[nama_opd]` | Admin OPD |
| `operator_[nama_opd]` | Operator OPD |
| `operator_opd` | Operator OPD generik |
| `admin_berita` | Admin Berita & Halaman |

### Level Kecamatan
| Role | Keterangan |
|------|------------|
| `admin_kecamatan` | Admin Kecamatan |
| `admin_lapangan` | Admin Lapangan |
| `admin_desa` | Admin Desa |

### Publik
| Role | Keterangan |
|------|------------|
| `public` | Akses read-only |

---

## 32 OPD Kabupaten Kepulauan Sangihe

1. Dinkes - Kesehatan
2. Dispendik - Pendidikan
3. DPUPR - PU
4. Dinsos - Sosial
5. Disperindag - Perdagangan
6. Dukcapil - Kependudukan
7. DLH - Lingkungan Hidup
8. Distan - Pertanian
9. Dishub -Hubungan
10. Distparbud - Pariwisata
11. Dispora - Pemuda & Olahraga
12. BKPSDMD - Kepegawaian
13. Bappeda - Perencanaan
14. Bapenda - Keuangan
15. BPBD - Bencana
16. Diskominfo - Kominfo
17. DPMTSP - Investasi
18. Distanak - Perkebunan
19. Setda - Sekretariat Daerah
20. Setwan - Sekretariat Dewan
21. DPPKB - Pengendalian Penduduk & KB
22. DP3A - Perempuan & Perlindungan Anak
23. DPMD - Pemberdayaan Masyarakat
24. Disnaker - Ketenagakerjaan
25. Ketapang - Ketahanan Pangan
26.Perikanan - Kelautan &Perikanan
27. Perpus - Perpustakaan
28. Perkim - Perumahan & Permukiman
29. Koprasi - Koprasi & UMKM
30. Inspektorat - Pengawasan
31. Kesbangpol - Kesatuan Bangsa & Politik
32. Satpol PP - Pamong Praja

---

## Alur Data

```
┌─────────────────────────────────────────────────────────────────┐
│                        KABUPATEN LEVEL                          │
│  Super Admin | Admin | Admin Kominfo | Walidata | Admin Bupati  │
│                                                                 │
│  ✓ Monitoring seluruh data                                      │
│  ✓ Validasi & approve data                                      │
│  ✓ Export data                                                  │
│  ✓ Manajemen konten publik                                       │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                │ Data dari OPD
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         OPD LEVEL                               │
│  Semua 32 OPD (Dinkes, Dispendik, DPUPR, Dinsos, dll)         │
│                                                                 │
│  ✓ Input data sektoral masing-masing OPD                        │
│  ✓ Submit data ke workflow                                      │
│  ✓ Reporting OPD                                                │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                │ Data rumah dari kecamatan
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      KECAMATAN LEVEL                            │
│  Admin Kecamatan | Admin Lapangan | Admin Desa                   │
│                                                                 │
│  ✓ Admin Kecamatan: Monitoring & review                          │
│  ✓ Admin Desa: Input data rumah saja                            │
│  ✓ Admin Lapangan: Input data rumah & keluarga                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Langkah Selanjutnya

### 1. Update User dengan Role Lama
Jika ada user yang masih menggunakan role lama, update ke role baru:

```php
// Admin Puskesmas -> Admin Dinkes
User::role('admin_puskes')->update(['role' => 'admin_dinkes']);

// Operator Puskesmas -> Operator Dinkes  
User::role('operator_puskes')->update(['role' => 'operator_dinkes']);

// Admin Sekolah -> Admin Dispendik
User::role('admin_sekolah')->update(['role' => 'admin_dispend']);

// Operator Sekolah -> Operator Dispendik
User::role('operator_sekolah')->update(['role' => 'operator_dispend']);
```

### 2. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 3. Verify Akses
-login sebagai user dengan role berbeda dan pastikan akses sudah benar.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Enums/AppRole.php` | Update role enum |
| `app/Models/User.php` | Update helper methods |
| `app/Support/PanelAccess.php` | Update access checks |
| `app/Services/Authorization/WewenangService.php` | Update authorization |
| `database/seeders/RolePermissionSeeder.php` | Update permissions |
| `database/migrations/2026_10_12_140618_*.php` | Hapus role lama |
| `app/Policies/*.php` | Update policy references |
| `app/Filament/Resources/UserResource.php` | Update form & filters |
