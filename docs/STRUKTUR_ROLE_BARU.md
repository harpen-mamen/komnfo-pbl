# Struktur Role & Alur Data Baru SIPANDU

## Perubahan Utama

### Role yang Dihapus
- `admin_puskes` / `operator_puskes` - Admin Puskesmas
- `admin_sekolah` / `operator_sekolah` - Admin Sekolah
- `admin_kesehatan` / `operator_kesehatan` - Generic Admin Kesehatan
- `admin_pendidikan` / `operator_pendidikan` - Generic Admin Pendidikan
- Generic roles (admin_pu, admin_sosial, admin_ekonomi, dll)

### Role Baru yang Ditambahkan
- `admin_bupati` - Admin Bupati
- `admin_wakil_bupati` - Admin Wakil Bupati
- `kepala_dinas` - Kepala Dinas (setiap OPD)
- `operator_opd` - Operator OPD generik
- `admin_berita` - Admin Berita & Halaman Publik

---

## Struktur Role Baru

### 1. KABUPATEN LEVEL
| Role | Keterangan |
|------|------------|
| `super_admin` | Super Administrator - akses penuh |
| `admin` | Administrator - akses penuh (terbatas) |
| `admin_kominfo` | Admin Kominfo - monitoring, validasi, export |
| `walidata` | Walidata - steward data kabupaten |
| `admin_bupati` | Admin Bupati - monitoring & pelaporan |
| `admin_wakil_bupati` | Admin Wakil Bupati - monitoring & pelaporan |

**Akses:**
- Monitoring seluruh data
- Validasi & approve data dari OPD
- Export data
- Manajemen konten publik

### 2. OPD LEVEL (DINAS/LEMBAGA)
| Role | Keterangan |
|------|------------|
| `kepala_dinas` | Kepala Dinas - penuh di OPD-nya |
| `admin_[nama_opd]` | Admin OPD - CRUD data OPD |
| `operator_[nama_opd]` | Operator OPD - input data OPD |
| `operator_opd` | Operator OPD generik |
| `admin_berita` | Admin Berita & Halaman Publik |

**32 OPD Kabupaten Kepulauan Sangihe:**
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

**Akses:**
- CRUD data OPD masing-masing
- Submission & workflow
- Reporting OPD

### 3. KECAMATAN LEVEL
| Role | Keterangan |
|------|------------|
| `admin_kecamatan` | Admin Kecamatan - monitoring & review |
| `admin_lapangan` | Admin Lapangan - input data lapangan |
| `admin_desa` | Admin Desa - input data rumah |

**Akses:**
- `admin_kecamatan`: Monitoring & review data kecamatan
- `admin_desa`: Hanya input data rumah
- `admin_lapangan`: Input data rumah & keluarga

### 4. PUBLIK
| Role | Keterangan |
|------|------------|
| `public` | Akses API read-only |

---

## Alur Data Baru

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
│  ✓ Reporting OPD                                               │
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

## Fokus Data

### Data Fokus di OPD (DATA SEKTORAL)
Semua data sektoral dikelola langsung oleh OPD terkait:
- **Dinkes**: Data kesehatan, rumah sakit, puskesmas, tenaga kesehatan
- **Dispendik**: Data pendidikan, sekolah, guru, siswa
- **DPUPR**: Data infrastruktur, jalan, jembatan
- **Dukcapil**: Data kependudukan, keluarga, penduduk
- Dst.

### Data dari Kecamatan/Desa (DATA LAPANGAN)
- **Rumah**: Input oleh Admin Desa atau Admin Lapangan
- **Keluarga & Penduduk**: Dari Dukcapil (data sektoral)

---

## Perubahan Permission

### modules dihapus:
- `puskes.*` (puskes.view, puskes.create, dll)
- `sekolah.*` (sekolah.view, sekolah.create, dll)

### modules ditambahkan:
- `berita.*` (berita.view, berita.create, berita.update, berita.delete, berita.publish)

---

## Checklist Migrasi

1. [ ] Jalankan migration: `php artisan migrate`
2. [ ] Jalankan seeder: `php artisan db:seed --class=RolePermissionSeeder`
3. [ ] Update user dengan role lama ke role baru:
   - `admin_puskes` → `admin_dinkes`
   - `operator_puskes` → `operator_dinkes`
   - `admin_sekolah` → `admin_dispend`
   - `operator_sekolah` → `operator_dispend`
4. [ ] Hapus user dengan role yang tidak diperlukan
5. [ ] Update frontend/mobile app jika ada referensi ke role lama

---

## Command untuk Migrasi Data

```bash
# Update user dengan role lama
php artisan tinker
>>> User::role('admin_puskes')->update(['role' => 'admin_dinkes']);
>>> User::role('operator_puskes')->update(['role' => 'operator_dinkes']);
>>> User::role('admin_sekolah')->update(['role' => 'admin_dispend']);
>>> User::role('operator_sekolah')->update(['role' => 'operator_dispend']);

# Hapus user dengan role yang dihapus
>>> User::role('admin_kesehatan')->delete();
>>> User::role('operator_kesehatan')->delete();
>>> User::role('admin_pendidikan')->delete();
>>> User::role('operator_pendidikan')->delete();
```
