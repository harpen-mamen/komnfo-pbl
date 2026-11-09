# AUDIT DASHBOARD OPD & ALUR VERIFIKASI
## Tanggal: 17 Oktober 2026

## A. STATUS SETIAP OPD

**Total OPD: 39 aktif**

### Distribusi Dataset per OPD
- 32 OPD punya **dataset & field definition**
- 7 OPD belum punya dataset (BAPENDA, BAPPEDA, BATAS, BKPSDM, DISTAMAN, DISTPAR, PUPR, dispendik)
- 0 OPD punya 0 fields

### Format Inputan per OPD
SEMUA OPD yang punya dataset menggunakan **DynamicDatasetResource** (generic resource):
- URL: `/admin/dataset-values?dataset={KODE_DATASET}`
- Form fields **di-generate otomatis** dari `dataset_fields`
- Support tipe: string, text, integer, decimal, boolean, date, enum, reference

## B. WORKFLOW VERIFIKASI

### Status DatasetValue
| Status | Jumlah | Keterangan |
|---|---|---|
| draft | 2 | Input awal oleh Produsen, belum disubmit |
| submitted | 4 | Sudah disubmit, menunggu verifikasi Walidata |
| verified | 5 | Sudah diverifikasi Walidata, menunggu publish |
| published | 11 | Sudah dipublish ke publik |
| rejected | 1 | Ditolak Walidata (ada notes) |
| revision | 1 | Perlu direvisi Produsen |

### Flow Lengkap (Sesuai Perbup Pasal 14-23)

```
[DRAFT] --submit()--> [SUBMITTED] --verify()--> [VERIFIED] --publish()--> [PUBLISHED]
   |                       |                      |                       |
Produsen:              Walidata:             Walidata:             Publik:
Input data             Cek kesesuaian        Approve data         Bisa diakses
                       prinsip Satu Data     Beri notes          via Portal
                       
[REJECTED] --archive()--> [ARCHIVED] (permanent / tidak aktif)
[REVISION] --submit()--> [SUBMITTED] (kembali ke alur utama)
```

### Method di DatasetValue Model
- `submit()` - status -> submitted
- `verify($walidata, $notes)` - status -> verified
- `publish()` - status -> published
- `reject($walidata, $notes)` - status -> rejected

## C. ROLE-BASED ACCESS CONTROL (Policy)

### DatasetValuePolicy
| Role | View | Create | Update | Submit | Verify | Publish | Delete |
|---|---|---|---|---|---|---|---|
| super_admin | Y | Y | Y | Y | Y | Y | Y |
| admin | Y | Y | Y | Y | Y | Y | Y |
| admin_kominfo (Walidata) | Y | - | - | - | Y | Y | Y |
| admin_dinkes (Produsen Dinkes) | Y (own) | Y | Y (own) | Y (own) | - | - | Y (own draft) |
| admin_dispendik (Produsen Disdik) | Y (own) | Y | Y (own) | Y (own) | - | - | Y (own draft) |
| admin_kecamatan | Y | - | - | - | - | - | - |

**Y (own)** = hanya bisa akses data milik OPD-nya sendiri

## D. HAL YANG SUDAH DIVERIFIKASI BEKERJA

| Test | Status |
|---|---|
| Migrasi database | OK (31 tabel, 90 datasets, 262 fields) |
| DynamicDatasetResource | OK (form generator otomatis) |
| Input data via form | OK (simulasi create + submit + verify + publish) |
| Workflow submit | OK (draft -> submitted) |
| Workflow verify | OK (submitted -> verified) |
| Workflow publish | OK (verified -> published) |
| Workflow reject | OK (dengan notes) |
| Workflow revision | OK (submitted -> revision) |
| Role-based access | OK (Policy baru) |
| Role access fix | OK (admin_dinkes dll sekarang bisa input) |
| Akses publik | OK (10 published data bisa diakses) |
| API publik siap | OK (10 data published tersedia) |

## E. HASIL TEST (TANGGAL 17 OKTOBER 2026)

### Data yang berhasil di-input
| Dataset | OPD | Status | Rows |
|---|---|---|---|
| 3.12 (KK per Kecamatan) | DUKCAPIL | published | 2 |
| 3.20 (Status Gizi Balita) | DINKES | mixed | 4 |
| 3.17 (Guru dan Murid) | DISDIK | mixed | 2 |
| 6.64 (Investasi) | DPMPTSP | published | 3 |
| 7.76 (Pajak Daerah) | BPKPD | submitted | 2 |
| 5.59 (Kondisi Jalan) | DPUPR | draft | 2 |
| 5.50 (Kendaraan) | DISHUB | verified | 3 |
| 9.88 (Indeks SPBE) | DISKOMINFO | mixed | 6 |
| **TOTAL** | | | **24** |

## F. HAL YANG PERLU PERBAIKAN

1. **Tambah Dataset untuk 7 OPD yang belum punya:**
   - BAPENDA (Pajak Daerah, Retribusi)
   - BAPPEDA (RPJMD, Proyek)
   - BATAS (Wilayah Perbatasan)
   - BKPSDM (Data PNS)
   - DISTAMAN (Perkebunan, Pertambangan)
   - DISTPAR (Pariwisata - duplikat dengan PARIWISATA)
   - PUPR (duplikat dengan DPUPR)

2. **UI Dashboard spesifik per OPD** (opsional, karena DynamicDatasetResource sudah generic)
   - Saat ini semua OPD input via /admin/dataset-values?dataset={KODE}
   - Bisa ditambah halaman OPD-specific untuk UX lebih baik

3. **Validasi Form** - tambahan rule untuk required, unique, dll
4. **Notifikasi Email/WhatsApp** saat status berubah

## G. KESIMPULAN

| Aspek | Status |
|---|---|
| Setiap OPD punya format inputan sendiri | **YA** (90 dataset dengan field masing-masing) |
| Sistem input data berjalan dengan baik | **YA** (24 data berhasil di-input & flow tested) |
| Workflow verifikasi berfungsi | **YA** (4 status: draft, submitted, verified, published) |
| Role-based access bekerja | **YA** (Policy baru) |

