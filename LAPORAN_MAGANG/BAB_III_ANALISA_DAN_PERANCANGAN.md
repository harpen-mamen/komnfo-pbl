# BAB III
# ANALISA DAN PERANCANGAN SISTEM

## 3.1 Analisis Sistem Berjalan

### 3.1.1 Analisis Kebutuhan Fungsional

Berdasarkan hasil observasi dan wawancara, kebutuhan SIPANDU adalah:

**Kebutuhan Admin Kabupaten:**
1. Dashboard monitoring progress seluruh kecamatan
2. Verifikasi data dari kecamatan
3. Publikasi data terverifikasi
4. Manajemen user dan role
5. Laporan dan statistik

**Kebutuhan Admin Kecamatan:**
1. Monitoring progress wilayah
2. Verifikasi data dari Admin Lapangan
3. Pengiriman batch ke Admin Kabupaten

**Kebutuhan Admin Lapangan:**
1. Input data titik geografis
2. Pengaitan data dengan keluarga
3. Pengiriman batch

### 3.1.2 Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
|-------|-----------|
| Perfomance | Response time < 3 detik |
| Availability | Uptime 99% |
| Security | Role-based access |
| Scalability | 1000+ concurrent users |
| Usability | Mobile-friendly |

## 3.2 Sistem yang Diusulkan

### 3.2.1 Arsitektur Platform

SIPANDU dirancang dengan arsitektur:

```
+------------------------+
|       FRONTEND         |
| Admin Panel | Mobile    |
+-----------+------------+
            |
        API Gateway
            |
+-----------+------------+
|       BACKEND          |
|    Laravel API         |
| Auth | Logic | Report  |
+-----------+------------+
            |
     MySQL | Redis | File
```

### 3.2.2 Schema Database Utama

**Tabel Master Wilayah:**
- kecamatans: master datacamatan
- desas: master datadesa
- dusuns: master datadusun

**Tabel Data:**
- rumah: titik rumah dan koordinat
- keluarga: data kartu keluarga
- penduduk: data individu

**Tabel Workflow:**
- submission_batches: header batch
- submission_batch_items: item dalam batch
- approval_history: history persetujuan

## 3.3 ERD (Entity Relationship Diagram)

```
Kecamatan 1----* Desa 1----* Rumah 1----* Keluarga 1----* Penduduk
   |          |          |          |
   +----------+----------+----------+
         Submission_Batches --> Submission_Batch_Items
```

## 3.4 Perancangan UI

### 3.4.1 Wireframe Dashboard

```
+----------------------------------------------------------+
| Dashboard Kabupaten                            [User]       |
+----------------------------------------------------------+
| +------------+ +------------+ +------------+ +------------+  |
| | Rumah     | | Keluarga  | | Penduduk  | | Batch     |  |
| | 1,234    | | 1,567    | | 5,678    | | 234       |  |
| +------------+ +------------+ +------------+ +------------+  |
|                                                          |
| Monitoring Kecamatan:                                      |
| Tabuhu    [████████░░░░] 78%                            |
| Tahuna    [████████████░░] 95%                            |
+----------------------------------------------------------+
```

## 3.5 Perancangan Flowchart

### 3.5.1 Flowchart Submission

```
Mulai -> Pilih Jenis Data -> Input Data -> Validasi
                                    |
                          +--------+--------+
                          |Valid            |Invalid
                          v                 v
                    Simpan Draft      Tampilkan Error
                          |
                          v
                    Kirim Batch -> Status: Pending
                          |
                    +-----+-----+
                    |Disetujui  |Ditolak
                    v           v
                Publish      Feedback
                    |
                    v
                Selesai
```

## 3.6 Perancangan API

### 3.6.1 Endpoint Utama

| Method | Endpoint | Deskripsi |
|--------|----------|------------|
| GET | /api/dashboard | Data dashboard |
| GET | /api/batches | Daftar batch |
| POST | /api/batches | Buat batch baru |
| PUT | /api/batches/{id}/verify | Verifikasi batch |
| GET | /api/rumah | Data rumah |
| POST | /api/rumah | Tambah rumah |
| GET | /api/keluarga | Data keluarga |
| POST | /api/keluarga | Tambah keluarga |
| GET | /api/penduduk | Data penduduk |
| POST | /api/penduduk | Tambah penduduk |
