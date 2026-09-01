# DESAIN SISTEM INPUT DATA PUSKESMAS & SEKOLAH
## Supplement to ARCHITECTURE.md

- **Versi:** 1.0 | **Tanggal:** 8 Agustus 2026
- **Status:** Rincian Desain Operasional

---

## RINGKASAN PERUBAHAN

### Struktur Role Baru

| Role Baru | Deskripsi |
|-----------|-----------|
| `admin_puskes_[puskes_id]` | Admin satu puskesmas tertentu |
| `admin_sekolah_[kecamatan_id]` | Admin semua sekolah di satu kecamatan |
| `operator_puskes_[puskes_id]` | Operator satu puskesmas tertentu |

### Entity Baru yang Perlu Dibuat

| Entity | Deskripsi | Tabel |
|--------|-----------|-------|
| FasilitasKesehatan (Puskesmas) | Master data puskes, pustu, posyandu | `fasilitas_kesehatans` |
| RekamMedis | Rekam medis per kunjungan | `rekam_medis` |
| StokObat | Pasokan obat di puskes | `stok_obats` |
| Sekolah | Master data sekolah | `sekolahs` |
| DataGuru | Detail guru (relasi penduduk) | `data_gurus` |
| DataSiswa | Detail siswa (relasi penduduk) | `data_siswas` |
| DataPrasaranaSekolah | Prasarana sekolah | `data_prasaranas` |

---

## BAGIAN 1: SISTEM ADMIN PUSKESMAS

### 1.1 Struktur Pemerintahan Kesehatan di Sangihe

```
DINAS KESEHATAN (DINKES)
  +-- ADMIN DINKES
  |     +-- view all puskes data
  |     +-- verify aggregate health data
  |     +-- approve batch submission
  |
  +-- 15 KECAMATAN
        +-- ADMIN KECAMATAN
        |     +-- view all puskes in kecamatannya
        |     +-- aggregate for submission
        |     +-- forward to Kominfo
        |
        +-- N PUSKESMAS per Kecamatan
              +-- ADMIN PUSKESMAS (admin_puskes_[id])
              |     +-- input all health data for this puskes
              |     +-- submit batch to kecamatan
              +-- OPERATOR PUSKESMAS
                    +-- input data (tanpa approve)
```

### 1.2 Master Data: Fasilitas Kesehatan (Puskesmas)

**Tabel: `fasilitas_kesehatans`**

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| kecamatan_id | bigint | FK ke kecamatan |
| desa_id | bigint | FK ke desa |
| nama | varchar(255) | Nama fasilitas kesehatan |
| kode_faskes | varchar(50) | Kode faskes Kemenkes |
| jenis | enum | Puskesmas, Puskesmas Pembantu, Posyandu, Klinik |
| alamat | text | Alamat lengkap |
| telepon | varchar(20) | Nomor telepon |
| kepala_puskes | varchar(255) | Nama kepala puskes |
| nip_kepala | varchar(50) | NIP kepala puskes |
| tahun_berdiri | year | Tahun berdiri |
| status_operasional | boolean | Aktif/nonaktif |
| koordinat_lat | decimal(10,7) | Latitude |
| koordinat_lng | decimal(10,7) | Longitude |
| jumlah_posyandu | int | Jumlah posyandu di bawahnya |
| jumlah_pustu | int | Jumlah pustu di bawahnya |
| jumlah_kader | int | Jumlah kader kesehatan |
| created_by, updated_by | bigint | FK user |

**Estimasi:** ±15-20 puskesmas di 15 kecamatan Sangihe

### 1.3 Modul Input Data Puskesmas

Admin puskes menginput data melalui **7 modul utama** per bulan:

#### Modul 1: DATA PELAYANAN KESEHATAN

| Field | Deskripsi | Source |
|-------|-----------|--------|
| pasien_id | FK ke tabel penduduk | **Search autocomplete NIK/Nama** |
| nama_lengkap | Auto-fill dari penduduk | Display only |
| nik | Auto-fill dari penduduk | Display only |
| tanggal_lahir | Auto-fill dari penduduk | Display only |
| jenis_kelamin | Auto-fill dari penduduk | Display only |
| alamat | Auto-fill dari penduduk | Display only |
| jenis_pelayanan | Select | Input |
| poli | Select poli | Input |
| diagnosa_utama | ICD-10 | Input |
| diagnosa_sekunder | ICD-10 multiple | Input |
| tindakan | ICD-9 | Input |
| obat_diberikan | Select dari stok obat | Input |
| tenaga_kesehatan_id | FK ke nakes | Select |
| cara_bayar | Select: BPJS, Asuransi, Umum | Input |
| rujukan | Select | Input |
| catatan | text | Input |

#### Modul 2: DATA IBU HAMIL & IBU BERSALIN

| Field | Deskripsi | Source |
|-------|-----------|--------|
| penduduk_id | FK ke penduduk (ibu) | **Search NIK/Nama** |
| tanggal_hpl | Tanggal perkiraan lahir | Input |
| usia_kehamilan_minggu | Auto-calculate dari HPL | Display |
| K1 s/d K6 | boolean per masing-masing | Input |
| status_kehamilan | Hamil, Melahirkan, Nifas | Input |
| kategori_resiko | Resiko Rendah, Resiko Tinggi | Input |
| mendapat_fe | boolean | Input |
| mendapat_imunisasi_tt | boolean | Input |
| tempat_persalinan | Pusk, RS, Bidan, Rumah | Input |
| berat_bayi_lahir | gram | Input |

#### Modul 3: DATA BAYI & BALITA

| Field | Deskripsi | Source |
|-------|-----------|--------|
| penduduk_id | FK ke penduduk (bayi) | **Search NIK/Nama** |
| ibu_id | FK ke Ibu Hamil | Select |
| berat_badan_lahir | gram | Input |
| berat_badan_sekarang | gram | Input |
| status_gizi | Baik, Kurang, Buruk, Obesitas | Input |
| kategori_balita | Normal, Stunting, Wasting | Input |
| imunisasi_hb0 s/d mmr | boolean per masing-masing | Input |
| vitamin_a | boolean + tanggal | Input |
| asi_eksklusif | boolean | Input |

#### Modul 4: DATA TENAGA KESEHATAN

| Field | Deskripsi | Source |
|-------|-----------|--------|
| penduduk_id | FK ke penduduk (nakes) | **Search NIK/Nama** |
| profesi | Select: Dokter, Perawat, Bidan, Apoteker, dll | Input |
| str | Text | Input |
| sip | Text | Input |
| masa_berlaku_str | date | Input |
| masa_berlaku_sip | date | Input |
| status_kepegawaian | PNS, PPPK, Honorer, Kontrak | Input |
| unit_kerja | poli/unit | Input |

#### Modul 5: DATA STOK OBAT & ALKES

| Field | Deskripsi | Source |
|-------|-----------|--------|
| kode_obat | varchar | Input |
| nama_obat | varchar | Input |
| satuan | Tablet, Kapsul, Sirup, Vial, dll | Input |
| kategori | Obat Keras, Obat Bebas, BMHP, dll | Input |
| stok_awal | integer | Input |
| terima_dari_gudang | integer | Input |
| jumlah_dipakai | integer | Input |
| stok_akhir | Auto-calculate | Display |
| jumlah_rusak | integer | Input |
| jumlah_kadaluarsa | integer | Input |
| stok_minimum | integer | Input |

#### Modul 6: DATA PENYAKIT & EPIDEMIOLOGI

| Field | Deskripsi | Source |
|-------|-----------|--------|
| penduduk_id | FK ke penduduk | **Search NIK/Nama** |
| jenis_penyakit | ISPA, DBD, Malaria, TBC, Hipertensi, dll | Input |
| kode_icd10 | Auto-fill | Display |
| tanggal_diagnosis | date | Input |
| status_kasus | Baru, Lama, Meninggal, Sembuh | Input |
| severity | Ringan, Sedang, Berat | Input |

#### Modul 7: DATA KUMULATIF & SARANA

| Field | Deskripsi | Source |
|-------|-----------|--------|
| jumlah_kunjungan_total | integer | Auto-sum |
| rawat_jalan, rawat_inap | integer | Auto-sum |
| jumlah_rujukan | integer | Auto-sum |
| jumlah_kematian | integer | Input manual |
| Jumlah_Stunting | integer | Auto-sum |
| memiliki_ambulans | boolean | Input |
| memiliki_laboratorium | boolean | Input |
| memiliki_listrik | boolean | Input |
| memiliki_internet | boolean | Input |
| kondisi_gedung | enum | Input |

---

## BAGIAN 2: SISTEM ADMIN SEKOLAH KECAMATAN

### 2.1 Prinsip Utama

> **1 Admin mengelola SEMUA sekolah di 1 kecamatan. Bukan 1 admin per sekolah.**

Contoh:
- Admin Sekolah Kecamatan Tahuna = input data ±25 sekolah di Tahuna
- Admin Sekolah Kecamatan Manganitu = input data ±18 sekolah di Manganitu
- Admin Sekolah Kecamatan Tabukan = input data ±20 sekolah di Tabukan

### 2.2 Master Data: Sekolah

**Tabel: `sekolahs`**

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| kecamatan_id | bigint | FK ke kecamatan |
| desa_id | bigint | FK ke desa |
| nama_sekolah | varchar(255) | Nama lengkap |
| npsn | varchar(20) | NPSN (unique) |
| jenjang | enum | SD, SMP, SMA, SMK, SLB |
| status | enum | Negeri, Swasta |
| alamat | text | Alamat lengkap |
| kepala_sekolah | varchar(255) | Nama kepala sekolah |
| akreditasi | enum | A, B, C, Belum Terakreditasi |
| kurikulum | varchar(50) | Kurikulum |
| koordinat_lat, koordinat_lng | decimal | Lokasi |

**Estimasi:** SD ±200, SMP ±60, SMA/SMK ±25. Total ±285 sekolah.

### 2.3 Modul Input Data Sekolah

5 modul per semester:

#### Modul 1: DATA SISWA

| Field | Deskripsi |
|-------|-----------|
| sekolah_id | Select dari daftar sekolah di kecamatan |
| tahun_ajaran | contoh: 2025/2026 |
| semester | Ganjil / Genap |
| jumlah_siswa_total, laki, perempuan | integer |
| siswa_diterima_baru, siswa_lulus | integer |
| putus_sekolah, naik_kelas, tidak_naik | integer |
| siswa_mutasi_keluar, siswa_mutasi_datang | integer |
| jumlah_rombel | integer |
| siswa_ABK, siswa_BOS, siswa_PIP, siswa_KIP | integer |

#### Modul 2: DATA GURU & TENAGA PENDIDIKAN

| Field | Deskripsi |
|-------|-----------|
| sekolah_id | FK |
| guru_total, guru_laki, guru_perempuan | integer |
| guru_PNS, guru_PPPK, guru_Honorer | integer |
| guru_S1_keatas, guru_S2_keatas | integer |
| guru_sertifikasi, guru_belum_sertifikasi | integer |
| GTK_Diklat_12_jam, GTK_Diklat_24_jam | integer |
| tenaga_administrasi, tenaga_perpustakaan | integer |

#### Modul 3: DATA PRASARANA & SARANA

| Field | Deskripsi |
|-------|-----------|
| sekolah_id | FK |
| jumlah_ruang_kelas | integer |
| kondisi_ruang_baik, rusak_ringan, rusak_berat | integer |
| laboratorium_ipa, laboratorium_komputer | boolean |
| memiliki_UKS, memiliki_musholla | boolean |
| sumber_listrik, sumber_internet | boolean |
| kondisi_gedung | enum |

#### Modul 4: DATA KEUANGAN & BOS

| Field | Deskripsi |
|-------|-----------|
| sekolah_id | FK |
| dana_BOS_terima, dana_BOS_digunakan | decimal |
| penggunaan_BOS_personel, non_personel | decimal |
| dana_zis, bantuan_pemerintah | decimal |

#### Modul 5: DATA PRESTASI & PROGRAM

| Field | Deskripsi |
|-------|-----------|
| sekolah_id | FK |
| prestasi_akademik, non_akademik | text |
| juara_1_lomba_kabupaten, provinsi, nasional | integer |
| program_literasi, program_numerasi | text |
| permasalahan | text |

---

## BAGIAN 3: MODEL BARU

### Checklist

| # | Item | Tabel |
|---|------|-------|
| 1 | FasilitasKesehatan Model & Migration | `fasilitas_kesehatans` |
| 2 | Sekolah Model & Migration | `sekolahs` |
| 3 | StokObat Model & Migration | `stok_obats` |
| 4 | RekamMedis Model & Migration | `rekam_medis` |
| 5 | DataGuru Model & Migration | `data_gurus` |
| 6 | DataSiswa Model & Migration | `data_siswas` |
| 7 | DataPrasaranaSekolah Model & Migration | `data_prasaranas` |
| 8 | Seeder: Data puskes Sangihe | - |
| 9 | Seeder: Data sekolah Sangihe | - |
| 10 | Update RolePermissionSeeder | - |

### Update Model Existing

Tambahkan field ke model:
- **IbuHamil, Bayi, TenagaKesehatan, PelayananKesehatan, KasusPenyakit:** `+fasilitas_kesehatan_id`
- **DataKesehatanBulanan:** `+fasilitas_kesehatan_id`
- **DataPendidikanBulanan:** `+sekolah_id`

---

## BAGIAN 4: PENDUDUDK SEARCH - AUTO-FILL

### Prinsip

> **Setiap input data kesehatan WAJIB memilih pasien dari tabel penduduk.**

Ini memastikan:
1. Data pasien konsisten dan tidak duplikat
2. Bisa dilakukan tracking history pelayanan
3. Bisa dilakukan analisis demografi pasien
4. Validasi NIK otomatis

### Component: PendudukSearch

**Lokasi:** `app/Livewire/Form/Components/PendudukSearch.php`

Fitur:
- Search by NIK atau Nama
- Autocomplete max 10 hasil
- On select: auto-fill semua field dari penduduk
- Fallback: pasien baru belum terdaftar

### UI Flow

```
1. Ketik NIK: "7201xxxx" -> dropdown muncul
2. Pilih pasien -> semua field auto-fill (nama, nik, alamat, ttl)
3. Admin input data pelayanan (diagnosa, tindakan, obat)
4. Save: penduduk_id tersimpan
```

### Fallback

Jika NIK tidak ditemukan:
1. Warning: "NIK tidak ditemukan di database penduduk"
2. Opsi:
   - "Cari ulang dengan nama" -> search by nama
   - "Daftarkan dulu ke menu Penduduk" -> redirect
   - "Input manual dengan warning" -> allow dengan catatan
3. Simpan dengan `penduduk_id = null`

---

## BAGIAN 5: KONFIGURASI ROLE & PERMISSION

### Role Definitions

```
admin_puskes_1 s/d admin_puskes_20  -> Admin per puskes
operator_puskes_1 s/d operator_puskes_20 -> Operator per puskes
admin_sekolah_kec_1 s/d admin_sekolah_kec_15 -> Admin sekolah per kecamatan
```

### Permission

**admin_puskes_[id]:**
- `input-pelayanan`, `input-ibu-hamil`, `input-bayi`, `input-nakes`
- `input-stok-obat`, `input-penyakit`, `input-kumulatif`
- `submit-batch-puskes`, `view-own-data`

**admin_sekolah_kec_[id]:**
- `input-sekolah` (semua sekolah di kec.), `input-guru`, `input-siswa`
- `input-prasaranas`, `input-keuangan`, `input-prestasi`
- `submit-batch-sekolah`, `view-own-kecamatan`

### User Model Update

Tambahkan field:
- `puskes_id` -> FK ke fasilitas_kesehatans
- `sekolah_id` -> NULL untuk admin sekolah kec (mengelola semua sekolah)

---

## BAGIAN 6: ALUR DATA LENGKAP

### Alur Admin Puskesmas

```
ADMIN PUSKESMAS
  Input: Pelayanan, Ibu Hamil, Bayi, Nakes, Stok Obat, Penyakit
    -> Kirim Batch ke Kecamatan
      -> Admin Kecamatan agregasi dari semua puskes
        -> Kirim ke DINKES
          -> DINKES verify & approve
            -> Kirim ke KOMINFO
              -> KOMINFO publish
```

### Alur Admin Sekolah Kecamatan

```
ADMIN SEKOLAH KECAMATAN
  Pilih Sekolah -> Input: Siswa, Guru, Prasarana, Keuangan, Prestasi
    -> Simpan per sekolah (draft)
    -> Pilih sekolah berikutnya
    -> Kirim Batch Semester ke Kecamatan
      -> Admin Kecamatan agregasi
        -> Kirim ke DISDIK
          -> DISDIK verify & approve
            -> Kirim ke KOMINFO
              -> KOMINFO publish
```

### Summary Tabel Role

| Role | Input | Scope | Kirim | View |
|------|-------|-------|-------|------|
| admin_puskes_[id] | Semua data kesehatan satu puskes | Satu puskes | Batch puskes | Satu puskes |
| operator_puskes_[id] | Semua data kesehatan satu puskes | Satu puskes | - | Satu puskes |
| admin_sekolah_kec_[id] | Semua sekolah satu kec | Satu kec | Batch kec | Satu kec |
| admin_dinkes | View all + verify | Semua kec | - | Semua |
| admin_dispend | View all + verify | Semua kec | - | Semua |
| admin_kecamatan | Wilayah + compile | Satu kec | Batch kec | Satu kec |

---

## BAGIAN 7: IMPLEMENTATION ROADMAP

### Tahap A: Fondasi (Minggu 1-2)
- [ ] Migration: fasilitas_kesehatans table
- [ ] Migration: sekolahs table
- [ ] Migration: stok_obats table
- [ ] Migration: rekam_medis table
- [ ] Migration: data_gurus table
- [ ] Migration: data_siswas table
- [ ] Migration: data_prasaranas table
- [ ] Model: semua model baru
- [ ] Seeder: data puskes Sangihe
- [ ] Seeder: data sekolah Sangihe
- [ ] Update RolePermissionSeeder

### Tahap B: Resource Filament (Minggu 3-4)
- [ ] Resource: FasilitasKesehatanResource
- [ ] Resource: SekolahResource
- [ ] Resource: StokObatResource
- [ ] Resource: RekamMedisResource
- [ ] Resource: DataGuruResource
- [ ] Resource: DataSiswaResource
- [ ] Resource: DataPrasaranaSekolahResource
- [ ] Component: PendudukSearch (autocomplete)
- [ ] Update: DataKesehatanBulananResource (+fasilitas_kesehatan_id)
- [ ] Update: DataPendidikanBulananResource (+sekolah_id)

### Tahap C: Integration & Workflow (Minggu 5-6)
- [ ] Update SubmissionBatchService (add module puskes & sekolah)
- [ ] Update BatchApprovalService (add puskes & sekolah workflow)
- [ ] Update PanelAccess (add new role checks)
- [ ] Update User model (+puskes_id, +sekolah_id)
- [ ] Page: KirimBatchPuskes ke Kecamatan
- [ ] Page: KirimBatchSekolah ke Kecamatan

### Tahap D: Dashboard & Reporting (Minggu 7-8)
- [ ] Widget: PuskesStatsWidget
- [ ] Widget: KecamatanHealthWidget
- [ ] Widget: DinasKesehatanWidget
- [ ] Widget: SekolahStatsWidget
- [ ] Widget: KecamatanPendidikanWidget
- [ ] Widget: DinasPendidikanWidget
- [ ] Map: Peta Sebaran Puskesmas
- [ ] Map: Peta Sebaran Sekolah

---

## BAGIAN 8: CONTOH KODE

### Model FasilitasKesehatan

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FasilitasKesehatan extends Model
{
    use HasFactory;

    protected $table = 'fasilitas_kesehatans';

    const JENIS_PUSKESMAS = 'Puskesmas';
    const JENIS_PUSTU = 'Puskesmas Pembantu';
    const JENIS_POSYANDU = 'Posyandu';
    const JENIS_KLINIK = 'Klinik';

    protected $fillable = [
        'kecamatan_id', 'desa_id', 'nama', 'kode_faskes', 'jenis',
        'alamat', 'telepon', 'email', 'kepala_puskes', 'nip_kepala',
        'tahun_berdiri', 'status_operasional',
        'koordinat_lat', 'koordinat_lng',
        'jumlah_posyandu', 'jumlah_pustu', 'jumlah_kader',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status_operasional' => 'boolean',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
        'jumlah_posyandu' => 'integer',
        'jumlah_pustu' => 'integer',
        'jumlah_kader' => 'integer',
    ];

    public function kecamatan(): BelongsTo { return $this->belongsTo(Kecamatan::class); }
    public function desa(): BelongsTo { return $this->belongsTo(Desa::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function dataKesehatanBulanans(): HasMany { return $this->hasMany(DataKesehatanBulanan::class); }
    public function tenagaKesehatans(): HasMany { return $this->hasMany(TenagaKesehatan::class); }
    public function pelayananKesehatans(): HasMany { return $this->hasMany(PelayananKesehatan::class); }
    public function stokObats(): HasMany { return $this->hasMany(StokObat::class); }
    public function rekamMedis(): HasMany { return $this->hasMany(RekamMedis::class); }

    public function scopeAktif($query) { return $query->where('status_operasional', true); }
    public function scopePuskes($query) { return $query->where('jenis', self::JENIS_PUSKESMAS); }
}
```

### Model Sekolah

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolahs';

    const JENJANG_SD = 'SD';
    const JENJANG_SMP = 'SMP';
    const JENJANG_SMA = 'SMA';
    const JENJANG_SMK = 'SMK';
    const JENJANG_SLB = 'SLB';

    protected $fillable = [
        'kecamatan_id', 'desa_id', 'nama_sekolah', 'npsn', 'nss',
        'jenjang', 'status', 'alamat', 'telepon', 'email', 'website',
        'kepala_sekolah', 'nip_kepala', 'tahun_berdiri',
        'akreditasi', 'kurikulum',
        'koordinat_lat', 'koordinat_lng',
        'created_by', 'updated_by',
    ];

    public function kecamatan(): BelongsTo { return $this->belongsTo(Kecamatan::class); }
    public function desa(): BelongsTo { return $this->belongsTo(Desa::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function dataPendidikanBulanans(): HasMany { return $this->hasMany(DataPendidikanBulanan::class); }
    public function dataGurus(): HasMany { return $this->hasMany(DataGuru::class); }
    public function dataSiswas(): HasMany { return $this->hasMany(DataSiswa::class); }
    public function dataPrasaranas(): HasMany { return $this->hasMany(DataPrasaranaSekolah::class); }

    public function scopeAktif($query) { return $query->where('status', '!=', 'Non-Aktif'); }
    public function scopeByJenjang($query, string $jenjang) { return $query->where('jenjang', $jenjang); }
    public function scopeByKecamatan($query, int $kecId) { return $query->where('kecamatan_id', $kecId); }
}
```

### PanelAccess Updates

```php
<?php
// Tambahan di app/Support/PanelAccess.php

public static function isAdminPuskes(): bool
{
    return (bool) self::user()?->hasAnyRole(['admin_puskes']);
}

public static function isOperatorPuskes(): bool
{
    return (bool) self::user()?->hasAnyRole(['operator_puskes']);
}

public static function isAdminSekolahKecamatan(): bool
{
    return (bool) self::user()?->hasAnyRole(['admin_sekolah']);
}

public static function canAccessPuskes(int $puskesId): bool
{
    $user = self::user();
    if (!$user) return false;
    if (self::isSuperAdmin() || self::isKominfoKabupaten()) return true;
    if (self::isAdminKesehatan()) return true;
    if (self::isAdminPuskes()) {
        return (int) $user->puskes_id === $puskesId;
    }
    return false;
}

public static function canAccessSekolah(int $sekolahId): bool
{
    $user = self::user();
    if (!$user) return false;
    if (self::isSuperAdmin() || self::isKominfoKabupaten()) return true;
    if (self::isAdminPendidikan()) return true;
    if (self::isAdminSekolahKecamatan()) {
        $sekolah = Sekolah::find($sekolahId);
        return $sekolah && (int) $sekolah->kecamatan_id === (int) $user->kecamatan_id;
    }
    return false;
}
```

---

## BAGIAN 9: KESIMPULAN

### Yang Dibuat Baru:

1. **Admin Puskesmas** — 7 modul input data kesehatan per bulan
2. **Admin Sekolah Kecamatan** — 5 modul input data sekolah per semester
3. **Master Data** — FasilitasKesehatan (puskes) dan Sekolah
4. **Autocomplete Penduduk** — Search NIK/Nama langsung dari tabel penduduk
5. **Stok Obat** — Management inventory obat di puskes
6. **Detail Rekam Medis** — ICD-10/ICD-9 untuk setiap kunjungan
7. **Detail Guru & Siswa** — Relasi ke penduduk per orang
8. **Role Baru** — `admin_puskes_[id]`, `admin_sekolah_kec_[id]`
9. **Workflow Baru** — Batch submission dari puskes → kecamatan → DINKES → Kominfo

### Poin Penting:

- **Admin Sekolah Kecamatan = 1 admin untuk SEMUA sekolah di 1 kecamatan** (bukan 1 admin per sekolah)
- Semua input pasien **WAJIB memilih dari tabel penduduk** (auto-fill)
- Workflow: Admin Puskesmas → Admin Kecamatan → DINKES → Kominfo
- Workflow: Admin Sekolah Kec → Admin Kecamatan → DISDIK → Kominfo
- Data dari admin puskes dan admin sekolah masuk ke dashboard DINKES & DISDIK
- Semua data terverifikasi melalui workflow Kominfo sebelum dipublikasikan

---

*Document: PUSKESMAS_SEKOLAH_DESIGN.md*
*Supplement to: ARCHITECTURE.md*
*Maintainer: Tim Developer*
*Last Updated: 8 Agustus 2026*
