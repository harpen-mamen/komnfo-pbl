# ARSITEKTUR PRODUKSI SIPANDU — Kabupaten Kepulauan Sangihe

> Dokumen ini adalah panduan arsitektur sistem jangka panjang untuk deployment
> production. Membahas semua aspek teknis dari infrastruktur hingga strategi
> skalabilitas. Target: production-ready pada tahun 2025-2026.
>
> **Catatan penting:** Dokumen ini adalah RANCANGAN dan PENJELASAN.
> Tidak ada modifikasi source code yang dilakukan.

---

## Daftar Isi

1. Ringkasan Eksekutif & Tujuan
2. Arsitektur Keseluruhan (Overview)
3. Komponen Utama Sistem
4. Hubungan Antar Komponen
5. Alur Data End-to-End
6. Request Lifecycle
7. Arsitektur Backend (Laravel)
8. Arsitektur Frontend & Mobile
9. Arsitektur Database
10. Arsitektur API
11. Authentication & Authorization
12. Security Architecture
13. Arsitektur Server & Infrastruktur
14. Docker & Container Architecture
15. Caching & Queue Strategy
16. Logging & Monitoring
17. Backup & Disaster Recovery
18. Strategi Skalabilitas
19. Development to Staging to Production
20. Rekomendasi Teknologi
21. Daftar Istilah

---

## 1. Ringkasan Eksekutif & Tujuan

### 1.1 Apa Itu SIPANDU

SIPANDU (Sistem Informasi Pangkalan Data Kabupaten Kepulauan Sangihe) adalah
platform digital pemerintahan terintegrasi yang mengentralisasi seluruh data
sektoral dari tingkat desa hingga kabupaten. Sistem ini berfungsi sebagai
Single Source of Truth (SSOT) untuk semua data pemerintahan daerah.

### 1.2 Tujuan Arsitektur

| Tujuan | Deskripsi |
|--------|-----------|
| **Reliabilitas** | Data yang tervalidasi hanya berasal dari batch berstatus Approved |
| **Skalabilitas** | Mampu melayani 50+ operator simultan dengan data terus bertumbuh |
| **Keamanan** | RBAC berlapis, audit trail lengkap, enkripsi data sensitif |
| **Performa** | Caching bertingkat, lazy loading, pagination everywhere |
| **Maintainability** | Modular monolith, service layer jelas, dokumentasi lengkap |
| **Observability** | Logging terpusat, monitoring real-time, alerting otomatis |

### 1.3 Prinsip Desain

1. **Single Source of Truth** — Hanya data dari SubmissionBatch berstatus approved
   yang masuk ke dashboard publik dan analytics.
2. **Write Model vs Read Model** — Operational data (write) dipisahkan dari data
   publik/analytics (read).
3. **Workflow First** — Setiap data melewati state machine sebelum dianggap valid.
4. **Defense in Depth** — Multiple layer keamanan di setiap level.
5. **Automate Everything** — CI/CD, testing, deployment, backup, monitoring.

### 1.4 Kondisi Eksisting vs Target

| Aspek | Eksisting | Target Production |
|-------|-----------|------------------|
| Queue Driver | sync (blocking) | redis + Laravel Horizon |
| Cache Driver | file | redis |
| Database | MySQL | PostgreSQL (direkomendasikan) |
| Docker | Tidak ada | Full containerization |
| Staging | Tidak ada | Separate staging environment |
| Monitoring | Tidak ada | Grafana + Prometheus |
| Backup | Manual | Otomatis harian + real-time |
| API Mobile | Basic outbox sync | Full offline-first |
| Session Driver | file | redis (shared session) |
| Queue Monitoring | Tidak ada | Laravel Horizon dashboard |
| Log Storage | File lokal | ELK Stack / Loki |
| Load Balancer | Tidak ada | Nginx + Keepalived |

---

## 2. Arsitektur Keseluruhan (Overview)

### 2.1 High-Level Architecture Diagram

```
                                    +-------------------------------------+
                                    |          INTERNET / WAN             |
                                    +--------------+----------------------+
                                                   |
                              +---------------------+--------------------+
                              |                     |                    |
                              v                     v                    v
                    +-----------------+  +-------------------+  +-------------------+
                    |  PUBLIC WEBSITE |  |   ADMIN PANEL    |  |    MOBILE APP     |
                    |   (Frontend)    |  |  (Filament 2.x) |  |   (Flutter)       |
                    |   Laravel Blade |  |  Laravel Blade   |  |   Offline-First   |
                    +--------+--------+  +--------+---------+  +--------+---------+
                             |                     |                     |
                             |   +-----------------+                     |
                             |   |                                       |
                             |   v                                       v
                    +--------------------+           +-----------------------------+
                    |   Laravel API      |<----------|      API Gateway / Nginx     |
                    |   Application      |           |   (Load Balancer + Reverse   |
                    |   (PHP 8.2+ FPM)  |           |    Proxy + SSL Termination) |
                    +--------+-----------+           +-----------------------------+
                             |
          +------------------+------------------+
          |                  |                  |
          v                  v                  v
  +---------------+  +---------------+  +---------------+
  |    Redis      |  |  PostgreSQL   |  |    MinIO /    |
  |  (Cache +     |  |   Primary     |  |   S3-Compa-  |
  |   Queue)     |  |   (Write)     |  |   tible Store |
  +---------------+  +-------+-------+  +---------------+
                              |
                              v (Replication)
                    +---------------------+
                    |  PostgreSQL Replica | (Read-only, for reporting)
                    +---------------------+
```

### 2.2 Component Layers

```
+-------------------------------------------------------------+
|  PRESENTATION LAYER                                         |
|  - Public Website (Laravel Blade)                          |
|  - Admin Panel (Filament 2.x)                               |
|  - Mobile App (Flutter -- offline-first)                    |
+-------------------------------------------------------------+
|  API GATEWAY LAYER                                         |
|  - Nginx (Reverse Proxy + SSL + Rate Limiting)              |
|  - Load Balancer (Round Robin / Least Connections)          |
|  - WAF (Web Application Firewall)                          |
+-------------------------------------------------------------+
|  APPLICATION LAYER                                          |
|  - Laravel 10.x (PHP 8.2+ FPM)                             |
|    - Controllers / Page Classes                             |
|    - Service Layer (Business Logic)                          |
|    - Models / Eloquent                                     |
|    - Jobs / Queues                                         |
|  - Laravel Horizon (Queue Monitor)                          |
+-------------------------------------------------------------+
|  DATA LAYER                                                |
|  - PostgreSQL 15+ (Primary DB)                             |
|  - Redis 7+ (Cache + Session + Queue)                     |
|  - MinIO / S3 (File Storage)                              |
|  - Elasticsearch (Full-text Search) [opsional]             |
+-------------------------------------------------------------+
|  INFRASTRUCTURE LAYER                                      |
|  - Docker + Docker Compose                                  |
|  - CI/CD (GitHub Actions / GitLab CI)                       |
|  - Monitoring (Prometheus + Grafana)                       |
|  - Backup (pgBackRest + Restic)                            |
+-------------------------------------------------------------+
```

---

## 3. Komponen Utama Sistem

### 3.1 Komponen Backend

| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| **Application Server** | PHP 8.2+ FPM (Laravel 10) | Menjalankan seluruh logika bisnis |
| **Queue Worker** | Laravel Horizon + Redis | Memproses job async (notifications, exports, sync) |
| **Scheduler** | Laravel Task Scheduling | Cron jobs untuk maintenance, report, cleanup |
| **API Server** | Laravel (REST + Sanctum) | Serving mobile app dan integrasi pihak ketiga |

### 3.2 Komponen Frontend

| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| **Admin Panel** | Filament 2.x | Panel administrasi untuk semua role |
| **Public Portal** | Laravel Blade + Tailwind | Website publik dengan statistik dan berita |
| **Mobile App** | Flutter 3.x | Input data offline-first untuk admin desa/puskesmas/sekolah |
| **Dashboard Analytics** | Filament + Chart.js | Visualisasi data approved untuk OPD dan Kominfo |

### 3.3 Komponen Data & Storage

| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| **Primary Database** | PostgreSQL 15+ | Menyimpan seluruh data operasional |
| **Read Replica** | PostgreSQL Streaming Replication | Menyalin data untuk read-only queries |
| **Cache Layer** | Redis 7+ | Cache query, session, rate limiting, queue |
| **File Storage** | MinIO / AWS S3 | Menyimpan foto, dokumen, export PDF/Excel |
| **Search Engine** | PostgreSQL Full-Text / Elasticsearch | Pencarian penduduk, keluarga, alamat |

### 3.4 Komponen Infrastruktur

| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| **Container** | Docker | Isolasi dan portabilitas aplikasi |
| **Orchestration** | Docker Compose / Kubernetes | Manajemen container lifecycle |
| **Reverse Proxy** | Nginx | SSL termination, load balancing, caching |
| **Monitoring** | Prometheus + Grafana | Metrik aplikasi, server, dan infrastruktur |
| **Log Aggregation** | ELK Stack / Loki | Pengumpulan dan analisis log terpusat |
| **CI/CD** | GitHub Actions / GitLab CI | Automasi build, test, dan deployment |

---

## 4. Hubungan Antar Komponen

### 4.1 Request Flow Diagram

```
Mobile App (Flutter)                         Browser (Admin)
     |                                            |
     |  POST /api/v1/mobile/login                |  GET /admin
     |  (Sanctum Token)                          |  (Session Cookie)
     v                                            v
+-----------------+                      +-----------------+
|  Nginx          |                      |  Nginx          |
|  :443 (SSL)     |                      |  :443 (SSL)     |
|  Rate Limit     |                      |  Rate Limit     |
+--------+--------+                      +--------+--------+
         |                                        |
         |  /api/* ------------------------------|--- /admin/*
         |                                        |
         v                                        v
+---------------------------------------------------------+
|                    Nginx Reverse Proxy                   |
|           (Load Balancer -- Round Robin)                  |
|                                                          |
|   +-------------+  +-------------+  +-------------+    |
|   |  App Server |  |  App Server |  |  App Server |    |
|   |  (PHP-FPM 1)|  |  (PHP-FPM 2)|  |  (PHP-FPM N)|    |
|   +------+------+  +------+------+  +------+------+    |
+----------+----+---------+----+---------+----+----------------+
           |                |                |
           +----------------+----------------+
                            |
              +-------------+-------------+
              |                           |
              v                           v                           v
       +-----------+             +-----------+             +-----------+
       | PostgreSQL|             |   Redis   |             |   MinIO   |
       | Primary  |             |  (Cache   |             |   (Files) |
       |          |             |  + Queue) |             |           |
       +-----+-----+             +-----------+             +-----------+
             |
             v (Streaming Replication)
       +-----------+
       | PostgreSQL |
       |  Replica  |
       +-----------+
```

### 4.2 Service-to-Service Communication

```
+------------------------------------------------------------+
|                    Laravel Application                       |
|                                                             |
|  +--------------+    +--------------+    +-------------+
|  | Controller/  |--->|   Service    |--->|   Model     |
|  | Page Class   |    |   Layer      |    |  (Eloquent) |
|  +--------------+    +------+-------+    +------+------+
|                              |                     |
|                              | calls               |
|                              v                     |
|                    +------------------+            |
|                    | SubmissionBatch   |<-----------+
|                    | ApprovalService   |
|                    +--------+---------+
|                             |
|           dispatch()        |        query()
|           +-----------------+------------------+
|           v                 v                  v
|  +-------------+  +----------------+  +--------------+
|  | Redis Queue |  |  PostgreSQL     |  |   MinIO/S3   |
|  |  (async)    |  |   (sync)       |  |   (files)    |
|  +-------------+  +----------------+  +--------------+
|                                                             |
|  +--------------+    +--------------+    +--------------+
|  |    Jobs      |<---|   Horizon    |--->|   Workers    |
|  | (Email, etc)|    |  (Dashboard) |    | (supervisord)|
|  +--------------+    +--------------+    +--------------+
+------------------------------------------------------------+
```

### 4.3 OPD Cross-Verification Communication

```
+-----------+       verifikasi NIK       +-----------+
| Admin    | -------------------------->| DUKCAPIL  |
| Kependa- |                            | (Dinas)   |
| dukan    |<---------------------------|           |
+-----------+   validasi NIK hasil       +-----------+

+-----------+       verifikasi NPSN      +-----------+
| Admin    | -------------------------->|  DISDIK   |
| Pendidi- |                            | (Dinas)   |
| kan      |<---------------------------|           |
+-----------+   validasi NPSN hasil     +-----------+

+-----------+       verifikasi faskes   +-----------+
| Admin    | -------------------------->| DINKES    |
| Puskemas |                            | (Dinas)   |
|          |<---------------------------|           |
+-----------+   validasi hasil faskes   +-----------+
```

---

## 5. Alur Data End-to-End

### 5.1 Complete Data Flow

```
DATA FLOW DIAGRAM
================================================================

  +-------------+
  | Admin Desa  |   Input data: Penduduk, Keluarga, Rumah
  |  (Mobile)   |   [Offline-first -> sync ketika online]
  +------+------+
         | create / update
         v
  +-----------------------------------------+
  |           OUTBOX TABLE                   |
  |  (mobile_outbox / sync_queue)            |
  |  Status: pending | synced | conflict     |
  +------+----------------------------------+
         |
         v
  +-----------------------------------------+
  |        SubmissionBatch (DRAFT)           |
  |  - module: penduduk/keluarga/rumah      |
  |  - status: draft                        |
  |  - kecamatan_id: [kecamatan_wilayah]    |
  |  - created_by: admin_desa               |
  +------+----------------------------------+
         | AdminDesa -> Kirim Batch
         | transition: draft -> pending
         v
  +-----------------------------------------+
  |       SubmissionBatch (PENDING)          |
  |  - submitted_at: timestamp              |
  |  - Notification -> AdminKecamatan       |
  +------+----------------------------------+
         | AdminKecamatan -> Review Batch
         | transition: pending -> in_review
         v
  +-----------------------------------------+
  |       SubmissionBatch (IN_REVIEW)        |
  |  AdminKecamatan memverifikasi data        |
  |  dari semua AdminDesa di wilayahnya       |
  +------+----------------------------------+
         | AdminKecamatan -> Forward ke Kominfo
         | transition: in_review -> in_review
         v
  +-----------------------------------------+
  |       Notification -> AdminKominfo       |
  |  Batch menunggu verifikasi akhir          |
  +------+----------------------------------+
         | AdminKominfo -> Verifikasi Final
         v
  +------------------+    +------------------+
  |   APPROVED       |    |   REJECTED       |
  |                   |    |                  |
  | payload_snapshot  |    | Catatan alasan   |
  | tersimpan lengkap |    | Batch dikembalikan|
  | data di-PUBLISH   |    | ke AdminKecamatan |
  +--------+---------+    +------------------+
           |
           | ONLY APPROVED DATA FLOWS HERE
           v
  +-----------------------------------------+
  |           WRITE MODEL (Private)           |
  |  Tabel: penduduk, keluarga, rumah        |
  |  dengan scope: inApprovedBatch()           |
  +------+----------------------------------+
         |
         | Read Replica (Streaming Replication)
         v
  +-----------------------------------------+
  |           READ MODEL (Public)            |
  |  - Dashboard Admin Kominfo               |
  |  - Dashboard OPD                         |
  |  - Public Website (statistik, peta)      |
  |  - API untuk mobile sync pull           |
  |  - Export PDF/Excel reports             |
  +-----------------------------------------+
```

### 5.2 Data Consistency Model

```
DATA CONSISTENCY GUARANTEES
================================================================

  1. WRITE SIDE (Primary DB -- PostgreSQL)
     - Setiap INSERT/UPDATE dalam transaksi DB
     - lockForUpdate() pada SubmissionBatch saat transisi
     - payload_snapshot dibuat SAAT approve (immutable)
     - version auto-increment per status change

  2. CONSISTENCY CHECKPOINT
     - Draft -> Pending: data masih bisa diedit
     - Pending -> In_Review: AdminKec mengunci & review
     - In_Review -> Approved: data menjadi READ-ONLY (snapshot)
     - Revision: batch kembali bisa diedit, versi baru dibuat

  3. READ SIDE (Replica DB -- Streaming Replication)
     - Lag: ~1-5 detik dari primary
     - Read-only queries: laporan, dashboard, export
     - Mobile sync pull: dari replica jika ada
     - Public API: dari replica atau cache

  4. CACHE CONSISTENCY
     - Cache invalidation on: approve, reject, archive
     - TTL-based: stats publik 2-60 menit
     - Event-driven: SubmissionBatchObserver
     - Manual flush: setelah batch approval
```

---

## 6. Request Lifecycle -- dari User ke Database

### 6.1 Admin Panel Request Flow (Filament)

```
Browser (User klik tombol)
        |
        v
1. HTTPS Request --> Nginx (SSL Termination)
        |
        v
2. Reverse Proxy --> Route /admin/* -> PHP-FPM Pool
        |
        v
3. Middleware Stack:
   - EncryptCookies
   - StartSession (Redis)
   - VerifyCsrfToken
   - SubstituteBindings
        |
        v
4. Authentication Check:
   - Session-based (cookie)
   - PanelAccess::user()
   - Spatie Role check
        |
        +-------+-------+
        |               |
        | DENIED         | ALLOWED
        v               v
   403 / Login    continue
        |               |
        +-------> authorization check
                 - canAccessFilament()
                 - Role-based resource access
                 - VisibleToCurrentUser scope
                          |
                          v
5. Controller / Page Class:
   - Filament Page Classes
   - Resource/ListPage
   - Resource/CreatePage
   - Resource/EditPage
   - Custom Actions
                          |
                          v
6. Service Layer (Business Logic):
   - SubmissionBatchService
   - BatchApprovalService
   - SubmissionPublishService
   - Domain Services
                          |
                          v
7. Eloquent Model -> Query Builder:
   - Scope: inApprovedBatch()
   - Scope: visibleToCurrentUser()
   - lockForUpdate() (for writes)
   - with() / load() (eager loading)
                          |
                          v
8. PostgreSQL Database:
   - Connection pooling (PgBouncer)
   - Prepared statements
   - Index scan (b-tree, GIN)
   - Transaction isolation: READ COMMITTED
                          |
                          v
9. Response (Laravel Blade):
   - Render view
   - Flash notifications
   - Cookie / Session update
```

### 6.2 Mobile API Request Flow (Flutter)

```
Flutter App (Offline-First Sync)
        |
        | WHEN ONLINE
        v
1. Login --> POST /api/v1/mobile/login
   - Sanctum Token (Bearer)
   - Request throttled (rate limit)
   - Token stored in Flutter SecureStorage
        |
        v
2. Bootstrap --> GET /api/v1/mobile/bootstrap
   - User profile & role
   - Kecamatan & Desa assignments
   - Available data modules
   - Latest approved versions
        |
        v
3. PULL Sync --> GET /api/v1/mobile/sync/pull
   - Params: last_sync_timestamp
   - Returns: approved data since timestamp
   - Reads from Read Replica if available
   - Cached with Redis
        |
        v
4. PUSH Sync --> POST /api/v1/mobile/sync/push
   - Body: array of data payloads
   - Outbox pattern (mobile)
   - Server validates & creates batch
   - Returns: accepted / rejected / conflicts
   - Queue notification job
        |
        v
5. Background Jobs (via Redis Queue):
   - Send notification emails
   - Process large exports
   - Data aggregation jobs
   - Cleanup jobs
        |
        v
6. Redis Queue --> Laravel Workers (via Horizon):
   - Job queue (redis)
   - Failed job table
   - Retry with exponential backoff
   - Monitor via Horizon dashboard
```

---

## 7. Arsitektur Backend (Laravel)

### 7.1 Directory Structure (Modular Monolith)

```
app/
+-- Console/                        # Kernel Artisan
+-- Enums/                          # PHP 8.1+ Enums
|   +-- BatchStatus.php
|   +-- BatchModule.php
|   +-- UserRole.php
+-- Events/                         # Domain Events
|   +-- BatchApproved.php
|   +-- BatchRejected.php
|   +-- DataSubmitted.php
+-- Exceptions/                     # Custom Exceptions
|   +-- WorkflowTransitionException.php
|   +-- UnauthorizedAccessException.php
+-- Http/
|   +-- Controllers/
|   |   +-- Api/                   # API Controllers
|   |   |   +-- MobileAuthController.php
|   |   |   +-- MobileBootstrapController.php
|   |   |   +-- MobileSyncController.php
|   |   +-- Auth/
|   |   +-- LaporanController.php
|   +-- Middleware/
|   |   +-- EnsureRole.php
|   |   +-- AuditRequest.php
|   |   +-- ValidateSubmissionBatch.php
|   +-- Kernel.php
+-- Jobs/                          # Queueable Jobs
|   +-- SendBatchNotificationJob.php
|   +-- ExportDataJob.php
|   +-- SyncToExternalApiJob.php
|   +-- CleanupOldBatchesJob.php
+-- Listeners/                     # Event Listeners
|   +-- InvalidateCacheOnApproval.php
|   +-- LogWorkflowTransition.php
+-- Mail/                          # Email Templates
|   +-- SubmissionSnapshotMail.php
+-- Models/
|   +-- Core/                      # Domain Models
|   |   +-- Kecamatan.php
|   |   +-- Desa.php
|   |   +-- User.php
|   +-- Population/               # Bounded Context: Kependudukan
|   |   +-- Penduduk.php
|   |   +-- Keluarga.php
|   |   +-- Rumah.php
|   +-- Health/                  # Bounded Context: Kesehatan
|   |   +-- DataKesehatanBulanan.php
|   |   +-- IbuHamil.php
|   |   +-- Bayi.php
|   |   +-- TenagaKesehatan.php
|   +-- Education/               # Bounded Context: Pendidikan
|   |   +-- DataPendidikanBulanan.php
|   |   +-- Sekolah.php
|   |   +-- FasilitasKesehatan.php
|   +-- Submission/              # Bounded Context: Workflow
|   |   +-- SubmissionBatch.php
|   |   +-- SubmissionBatchItem.php
|   |   +-- WorkflowInstance.php
|   +-- Traits/
|       +-- HasSubmissionBatchApproval.php
|       +-- HasWilayahScope.php
+-- Notifications/
|   +-- BatchNotification.php
|   +-- BatchApprovedNotification.php
+-- Observers/
|   +-- SubmissionBatchObserver.php
|   +-- PendudukObserver.php
+-- Policies/                     # Authorization Policies
|   +-- SubmissionBatchPolicy.php
|   +-- PendudukPolicy.php
|   +-- UserPolicy.php
+-- Providers/
|   +-- AppServiceProvider.php
|   +-- AuthServiceProvider.php
|   +-- EventServiceProvider.php
|   +-- FilamentServiceProvider.php
|   +-- RouteServiceProvider.php
+-- Rules/                        # Custom Validation Rules
|   +-- ValidNik.php
|   +-- BatchTransitionValidator.php
+-- Scopes/                       # Eloquent Global Scopes
|   +-- VisibleToCurrentUser.php
|   +-- InApprovedBatch.php
+-- Services/                     # Business Logic Layer
    +-- Batch/
    |   +-- SubmissionBatchService.php
    |   +-- BatchApprovalService.php
    |   +-- SubmissionPublishService.php
    +-- Import/
    |   +-- PendudukImportService.php
    |   +-- DataImportService.php
    +-- Sync/
    |   +-- MobileSyncService.php
    +-- Report/
        +-- StatisticsService.php
        +-- ExportService.php
```

### 7.2 Service Layer Design

```
SERVICE LAYER ARCHITECTURE
================================================================

  CONTROLLER LAYER
  - Filament Page Classes (Create, Edit, List, View)
  - Api Controllers
  - Console Commands
         |
         | calls
         v
  +-----------------------------------------------------+
  |                 SERVICE LAYER                         |
  |                                                       |
  |  SubmissionBatchService         <- Batch lifecycle     |
  |  BatchApprovalService           <- State transitions   |
  |  SubmissionPublishService       <- Publish approved    |
  |  PendudukImportService          <- Excel import        |
  |  MobileSyncService              <- Mobile sync        |
  |  StatisticsService              <- Dashboard stats    |
  |  ExportService                  <- PDF/Excel export   |
  +-----------------------------------------------------+
         |
         | calls
         v
  +-----------------------------------------------------+
  |               MODEL LAYER (Eloquent)                 |
  |                                                       |
  |  Penduduk -- HasSubmissionBatchApproval               |
  |  Keluarga -- HasSubmissionBatchApproval               |
  |  Rumah -- HasSubmissionBatchApproval                  |
  |  DataKesehatanBulanan -- HasSubmissionBatchApproval   |
  |  DataPendidikanBulanan -- HasSubmissionBatchApproval  |
  |  SubmissionBatch -- State Machine                    |
  +-----------------------------------------------------+
         |
         | queries / transactions
         v
  POSTGRESQL -- REDIS -- MINIO/S3
```

### 7.3 SubmissionBatch State Machine

```
    STATE MACHINE DIAGRAM
================================================================

  +---------+
  |  DRAFT  | <- AdminDesa/AdminKecamatan membuat batch
  +----+----+
       | kirim()
       v
  +---------+
  | PENDING | <- Menunggu review AdminKecamatan
  +----+----+
       | mulai_review()
       v
  +----------+
  |IN_REVIEW | <- AdminKecamatan memverifikasi
  +----+-----+
       |
  +----+--------------------+
  |                         |
  v                         v
+-----------+    +-----------+
| APPROVED |    | REJECTED |
+-----+-----+    +-----+-----+
      |                   |
      | approve()         | reject()
      | + publish()       | catatan alasan
      v                   v
+-----------+    +-----------+
| PUBLISHED|    | ARCHIVED |
+-----------+   +-----+-----+
                      |
                      | archive()
                      v
                +-----------+
                |  ARCHIVED |
                +-----------+

  +----------+
  | REVISION | <- minta perbaikan
  +----+-----+
       | perbaiki() + kirim()
       +---------------------+
```

---

## 8. Arsitektur Frontend & Mobile

### 8.1 Three-Frontend Architecture

```
FRONTEND ARCHITECTURE
================================================================

  +--------------------+   +--------------------+   +--------------+
  |   ADMIN PANEL     |   |   PUBLIC PORTAL    |   |  MOBILE APP |
  |   (Filament 2.x) |   |   (Laravel Blade) |   |  (Flutter)  |
  |                    |   |                    |   |             |
  | /admin/*          |   | /*                 |   | Android/iOS|
  | - Full CRUD       |   | - Read-only stats  |   | - Offline   |
  | - Workflow actions |   | - Peta publik      |   | - Sync-first|
  | - Multi-role panel |   | - Berita           |   | - Camera/GPS|
  | - Charts & reports |   | - Layanan          |   | - Push notif|
  +---------+----------+   +---------+----------+   +------+------+
            |                         |                      |
            | session/cookie          | public API           | bearer
            |                         |                      |
            v                         v                      v
      +-----------+             +-----------+          +-----------+
      |   Nginx   |             |   Nginx   |          |   Nginx   |
      |  :8443   |             |   :443   |          |   :443   |
      | (HTTPS)  |             |  (HTTPS) |          |  (HTTPS) |
      +-----------+             +-----------+          +-----------+
```

### 8.2 Admin Panel Switching Strategy

```
PANEL SWITCHING ARCHITECTURE
================================================================

  User: AdminKecamatan with role "admin_kecamatan"

  +---------------------------------------------------------+
  |              NAVIGATION PANEL                            |
  |  +---------+  +---------+  +---------+                  |
  |  | Kegiata |  | Pendudu |  | Keluar- |                  |
  |  |   an    |  |   k     |  |  gaan   |                  |
  |  +---------+  +---------+  +---------+                  |
  |                                                          |
  |  SIDEBAR (Filament Widgets)                              |
  |  - Dashboard Stats                                       |
  |  - Recent Batches                                       |
  |  - Pending Reviews                                      |
  |  - Quick Actions                                       |
  +---------------------------------------------------------+

  Panel Access Gate (PanelAccess.php):
  +---------------------------------------------------------+
  |  canAccessKabupatenPanel() -> SuperAdmin, Kominfo        |
  |  canAccessKecamatanPanel() -> AdminKecamatan, AdminDesa   |
  |  canAccessOpdPanel() -> OPD Admins                      |
  |  getUserOpdModule() -> kesehatan | pendidikan | ...     |
  |  canApproveOpdBatch(module) -> OPD-specific approval    |
  +---------------------------------------------------------+

  Resource-Level Scoping:
  +---------------------------------------------------------+
  |  scopeVisibleToCurrentUser(Builder $query)               |
  |    -> kecamatan_id based on user.role                    |
  |    -> desa_id based on user.role                        |
  |    -> opd_id based on user.role                         |
  |                                                          |
  |  scopeInApprovedBatch(Builder $query)                    |
  |    -> SubmissionBatch.status === approved                |
  |    -> submission_batch_id IN approved batches            |
  +---------------------------------------------------------+
```

### 8.3 Mobile App Offline-First Design

```
FLUTTER MOBILE -- OFFLINE-FIRST DESIGN
================================================================

  +------------------------------------------------------------+
  |                      MOBILE APP (Flutter)                    |
  |                                                             |
  |  +------------+  +------------+  +------------+             |
  |  |   Login    |  |  Dashboard |  |  Data Entry |             |
  |  |   Screen   |  |   Screen   |  |   Screens   |             |
  |  +-----+------+  +-----+------+  +-----+------+             |
  |        |               |               |                    |
  |        +---------------+---------------+                    |
  |                        v                                    |
  |              +----------------------+                        |
  |              |   Sync Repository    |                        |
  |              |  outboxQueue: List  |  <- Local SQLite     |
  |              |  localCache: Map     |  <- Hive/Isar        |
  |              |  syncState: SyncFlag|  <- last_sync_at     |
  |              +----------+-----------+                       |
  +--------------------------+----------------------------------+
                               |
           +-------------------+-------------------+
           v                   v                   v
  +-------------+     +-------------+     +-------------+
  | OFFLINE     |     |  SYNCING   |     |   ONLINE    |
  | MODE        |     |   MODE     |     |   MODE      |
  |             |     |            |     |             |
  | Data saved  |     | Push local |     | Full duplex |
  | to outbox   |---->| queue to   |---->| sync with   |
  | queue       |     | server     |     | server      |
  |             |     | Pull latest|     |             |
  | User can    |     | approved   |     | Real-time   |
  | continue    |     | data       |     | data entry  |
  | working     |     |            |     |             |
  +-------------+     +------+------+     +-------------+
                              |
                              v
                    +---------------------+
                    |  CONFLICT RESOLUTION|
                    |                      |
                    |  - Server wins (default)             |
                    |  - Local wins (configurable)         |
                    |  - Manual merge (critical data)     |
                    |  - Version vector for conflict detection|
                    +---------------------+
```

### 8.4 PendudukSearch Auto-Fill Component

```
PENDUDUK SEARCH AUTO-FILL FLOW
================================================================

  Input Field: "Nama / NIK Pasien"
  +--------------------------------------------------------+
  | [cari] Ketik nama atau NIK...                    [CLEAR]|
  +--------------------------------------------------------+
       | debounce 300ms
       v
  +------------------------------------------------------+
  |  API: GET /api/penduduk/search?q={term}              |
  |        Auth: Bearer Token (Sanctum)                  |
  |        Scope: inApprovedBatch()                      |
  |        Cache: Redis TTL 5min                         |
  +---------------------+--------------------------------+
                         v
  +------------------------------------------------------+
  |  RESULTS DROPDOWN (max 10)                          |
  |                                                     |
  |  +-----------------------------------------------+   |
  |  | 5301xxxx JAMES TUMATARANGI                     |   |
  |  |    Ds. Tahuna Barat, Kec. Tahuna -- L/P, 1985  |   |
  |  +-----------------------------------------------+   |
  |  +-----------------------------------------------+   |
  |  | 5301xxxx JEMI TUMATARANGI                      |   |
  |  |    Ds. Beha, Kec. Tabukan Utara -- P, 1992     |   |
  |  +-----------------------------------------------+   |
  +------------------------------------------------------+
       | user clicks
       v
  +------------------------------------------------------+
  |  AUTO-POPULATE FIELDS:                              |
  |                                                     |
  |  NIK          : 5301xxxx                           |
  |  Nama         : JAMES TUMATARANGI                  |
  |  Jenis Kelamin: Laki-laki                          |
  |  Tanggal Lahir: 15 Maret 1985                      |
  |  Alamat       : Ds. Tahuna Barat, Kec. Tahuna       |
  |  Desa         : Tahuna Barat                        |
  |  Kecamatan    : Tahuna                              |
  +------------------------------------------------------+
```

|-------|-------------|--------------|-------|
| kecamatan | id | - | 15 records (Kab. Sangihe) |
| desa | id | kecamatan_id | ~200 records |
| user | id | kecamatan_id, desa_id, opd_id | RBAC via Spatie |
| role | id | - | 30+ roles defined |
| submission_batch | id | kecamatan_id, created_by, verified_by | State machine |
| submission_batch_item | id | batch_id | Polymorphic |
| workflow_instance | id | batch_id | Workflow tracking |
| workflow_histories | id | instance_id | Audit trail |
| verification_comments | id | instance_id, pengguna_id | Discussion threads |
| penduduk | id | no_kk (FK keluarga), batch_id | Core population data |
| keluarga | id | batch_id, desa_id | |
| rumah | id | batch_id, created_by | |
| data_kesehatan_bulanan | id | kecamatan_id, batch_id | 50+ columns |
| data_pendidikan_bulanan | id | kecamatan_id, batch_id | |

### 9.2 PostgreSQL Optimizations

Key indexes untuk performa:

| Index | Table | Type | Purpose |
|-------|-------|------|---------|
| idx_penduduk_nik | penduduk | B-tree | NIK lookup |
| idx_penduduk_no_kk | penduduk | B-tree | KK lookup |
| idx_batch_status | submission_batch | B-tree | Status filtering |
| idx_batch_kec_status | submission_batch | Composite | Workflow queries |
| idx_batch_approved | submission_batch | Partial | Dashboard (WHERE status=approved) |
| idx_batch_payload_gin | submission_batch | GIN (JSONB) | Snapshot search |
| idx_penduduk_fulltext | penduduk | GIN (tsvector) | Indonesian FTS |
| idx_active_batch | submission_batch | Partial Unique | One active batch/module/kec |

PostgreSQL-specific features to use:
- **JSONB** untuk payload_snapshot (binary, indexed, fast)
- **PostGIS** extension untuk geolocation (rumah lat/lng)
- **Table partitioning** untuk data_kesehatan_bulanan per tahun
- **pgBouncer** connection pooling (transaction mode, 200 max connections)
- **Streaming replication** untuk read replica

### 9.3 MySQL to PostgreSQL Migration Plan

| Phase | Aktivitas | Effort | Risk |
|-------|-----------|--------|------|
| Phase 1 | Setup PostgreSQL sebagai read replica | 3-5 days | Low |
| Phase 2 | Test read-only queries against replica | 2-3 days | Low |
| Phase 3 | Dual-write (MySQL + PostgreSQL) | 1-2 weeks | Medium |
| Phase 4 | Switch read queries to replica | 2-3 days | Medium |
| Phase 5 | Full migration, drop MySQL | 1-2 weeks | High |

Total estimated: 4-6 weeks

---

## 9. Arsitektur Database

### 9.1 Entity Relationship Diagram (Simplified)

| Table | Primary Key | Foreign Keys | Notes |
|-------|-------------|--------------|-------|
| kecamatan | id | - | 15 records (Kab. Sangihe) |
| desa | id | kecamatan_id | ~200 records |
| user | id | kecamatan_id, desa_id, opd_id | RBAC via Spatie |
| role | id | - | 30+ roles defined |
| submission_batch | id | kecamatan_id, created_by, verified_by | State machine |
| submission_batch_item | id | batch_id | Polymorphic |
| workflow_instance | id | batch_id | Workflow tracking |
| workflow_histories | id | instance_id | Audit trail |
| verification_comments | id | instance_id, pengguna_id | Discussion threads |
| penduduk | id | no_kk (FK keluarga), batch_id | Core population data |
| keluarga | id | batch_id, desa_id | |
| rumah | id | batch_id, created_by | |
| data_kesehatan_bulanan | id | kecamatan_id, batch_id | 50+ columns |
| data_pendidikan_bulanan | id | kecamatan_id, batch_id | |

### 9.2 PostgreSQL Optimizations

Key indexes untuk performa:

| Index | Table | Type | Purpose |
|-------|-------|------|---------|
| idx_penduduk_nik | penduduk | B-tree | NIK lookup |
| idx_penduduk_no_kk | penduduk | B-tree | KK lookup |
| idx_batch_status | submission_batch | B-tree | Status filtering |
| idx_batch_kec_status | submission_batch | Composite | Workflow queries |
| idx_batch_approved | submission_batch | Partial | Dashboard (WHERE status=approved) |
| idx_batch_payload_gin | submission_batch | GIN (JSONB) | Snapshot search |
| idx_penduduk_fulltext | penduduk | GIN (tsvector) | Indonesian FTS |
| idx_active_batch | submission_batch | Partial Unique | One active batch/module/kec |

PostgreSQL-specific features to use:
- **JSONB** untuk payload_snapshot (binary, indexed, fast)
- **PostGIS** extension untuk geolocation (rumah lat/lng)
- **Table partitioning** untuk data_kesehatan_bulanan per tahun
- **pgBouncer** connection pooling (transaction mode, 200 max connections)
- **Streaming replication** untuk read replica

### 9.3 MySQL to PostgreSQL Migration Plan

| Phase | Aktivitas | Effort | Risk |
|-------|-----------|--------|------|
| Phase 1 | Setup PostgreSQL sebagai read replica | 3-5 days | Low |
| Phase 2 | Test read-only queries against replica | 2-3 days | Low |
| Phase 3 | Dual-write (MySQL + PostgreSQL) | 1-2 weeks | Medium |
| Phase 4 | Switch read queries to replica | 2-3 days | Medium |
| Phase 5 | Full migration, drop MySQL | 1-2 weeks | High |

Total estimated: 4-6 weeks

---

## 10. Arsitektur API

### 10.1 API Versioning Strategy

| Aspect | Decision |
|--------|----------|
| Strategy | URL Path Versioning |
| Format | /api/v{version}/{resource} |
| Current | v1 (Mobile App v1) |
| Future | v2 (breaking changes), v3 (major redesign) |
| Deprecation | Header: Deprecation: true, Sunset date |

**v1 Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/mobile/login | Authenticate |
| GET | /api/v1/mobile/me | User profile |
| GET | /api/v1/mobile/bootstrap | App config |
| GET | /api/v1/mobile/sync/pull | Pull approved data |
| POST | /api/v1/mobile/sync/push | Push local data |
| GET | /api/v1/penduduk/search | Search penduduk |
| GET | /api/v1/kecamatan | List kecamatan |
| GET | /api/v1/desa/by-kecamatan/{id} | List desa |
| GET | /api/v1/statistics/public | Public statistics |

**Response Format:**
```json
{
  "success": true,
  "data": { ... },
  "meta": { "total": 100, "page": 1, "per_page": 20 },
  "errors": []
}
```

### 10.2 Mobile Sync Protocol

**PULL Sync:**
- GET /api/v1/mobile/sync/pull?since={timestamp}
- Returns: all approved data since timestamp
- Cached at Redis for performance
- Reads from read replica when available

**PUSH Sync:**
- POST /api/v1/mobile/sync/push with batch + items array
- Server creates SubmissionBatch in DRAFT
- Returns: accepted / rejected / conflicts
- Only approved items become visible

**Conflict Resolution:**
- Default: server_wins (data approved by workflow is source of truth)
- Configurable: local_wins for specific modules
- Manual merge: for critical data requiring human decision
- Version vector: tracks local vs server version for conflict detection

---

## 11. Authentication & Authorization

### 11.1 Dual Auth Strategy

| Channel | Method | Guard | Storage |
|---------|--------|-------|---------|
| Admin Panel (Web) | Session-based | web | Redis (shared) |
| Mobile App (API) | Token-based | sanctum | FlutterSecureStorage |

### 11.2 Role Hierarchy Summary

| Level | Role | Scope |
|-------|------|-------|
| 1 | Super Admin | Full system access |
| 2 | Admin Kominfo | All batches, all OPDs, public portal |
| 3 | Admin OPD (per OPD) | OPD-specific data + approval |
| 4 | Admin Kecamatan | Own kecamatan batches, forwarded batches |
| 5 | Admin Desa / Operator | Own data entry + submission |

### 11.3 Data Isolation Mechanism

| Scope | Applied To | Method |
|-------|------------|--------|
| visibleToCurrentUser | All population models | Eloquent Global Scope |
| inApprovedBatch | All data models | Eloquent Global Scope |
| kecamatan_id filter | Kecamatan-scoped data | Policy + Scope |
| desa_id filter | Desa-scoped data | Policy + Scope |
| opd_id filter | OPD-scoped data | Policy + Scope |

---

## 12. Security Architecture

### 12.1 Security Layers Summary

| Layer | Components | Purpose |
|-------|------------|---------|
| Network | WAF, Nginx, VPN | Filter malicious traffic, SSL termination |
| Application | CSRF, XSS protection, Rate limiting | Prevent common web attacks |
| Data | Encryption, masking, hashing | Protect data at rest and in transit |
| Access | RBAC, sessions, 2FA | Ensure only authorized access |

### 12.2 Rate Limiting Configuration

| Endpoint | Limit | Window | Purpose |
|---------|-------|--------|---------|
| /api/v1/mobile/login | 5 requests | per minute | Brute force protection |
| /api/* | 60 requests | per minute | General API protection |
| /admin/* | 10 requests | per minute | Admin panel protection |
| Public / | 120 requests | per minute | Public website |

### 12.3 Audit Trail Summary

Every write operation produces:
1. workflow_histories record (status transition)
2. verification_comments (review discussion)
3. Laravel audit log (JSON format)
4. payload_snapshot (immutable archive at approve)

---

## 13. Arsitektur Server & Infrastruktur

### 13.1 Server Topology Summary

| Tier | Servers | Specs | Services |
|------|---------|-------|----------|
| Web | 2-3 | 4 vCPU, 8GB RAM | PHP-FPM, Nginx |
| Database | 2 | 8 vCPU, 16GB RAM, 500GB NVMe | PostgreSQL Primary + Replica |
| Cache/Queue | 2 | 2 vCPU, 4GB RAM | Redis Sentinel |
| Storage | 1 | 2 vCPU, 4GB RAM, 1TB | MinIO |
| Load Balancer | 2 | 2 vCPU, 2GB RAM | Nginx / HAProxy |
| Monitoring | 1 | 2 vCPU, 4GB RAM | Prometheus + Grafana |

### 13.2 Deployment Topology

| Environment | Branch | Auto-deploy | DB | Purpose |
|-------------|--------|-------------|-----|---------|
| Development | dev | Yes (on push) | SQLite/MySQL local | Local dev |
| Staging | staging | Yes (on merge) | PostgreSQL mirror | Integration testing |
| Production | main | Manual (git tag) | PostgreSQL HA | Live system |

---

## 14. Docker & Container Architecture

### 14.1 Container Summary

| Container | Image | Replicas | Purpose |
|-----------|-------|----------|---------|
| nginx | nginx:1.25-alpine | 1 | Reverse proxy + SSL |
| app | php:8.2-fpm-alpine (custom) | 1 | PHP-FPM application |
| queue | php:8.2-fpm-alpine (custom) | 2 | Laravel Horizon workers |
| scheduler | php:8.2-fpm-alpine (custom) | 1 | Cron scheduler |
| postgres_primary | postgis/postgis:15-3.3 | 1 | Primary database |
| postgres_replica | postgis/postgis:15-3.3 | 1 | Read replica |
| pgbouncer | edoburu/pgbouncer | 1 | Connection pooler |
| redis | redis:7-alpine | 1 | Cache + Queue |
| minio | minio/minio:latest | 1 | S3-compatible storage |
| prometheus | prom/prometheus:latest | 1 | Metrics |
| grafana | grafana/grafana:latest | 1 | Dashboards |

### 14.2 Container Networking

- **Bridge network**: 10.20.0.0/24
- **Internal only**: Database, Redis, MinIO not exposed to host
- **Exposed ports**: Nginx (80, 443), Grafana (3000), MinIO Console (9001)

### 14.3 Data Persistence

| Volume | Type | Purpose |
|--------|------|---------|
| postgres_primary_data | Named volume | PostgreSQL primary data |
| postgres_replica_data | Named volume | PostgreSQL replica data |
| redis_data | Named volume | Redis persistence (AOF) |
| minio_data | Named volume | File storage |
| prometheus_data | Named volume | Metrics history |
| grafana_data | Named volume | Dashboard configs |

---

## 15. Caching & Queue Strategy

### 15.1 Cache Categories

| Category | Keys | TTL | Invalidation |
|----------|------|-----|-------------|
| Public Stats | public.home.stats, public.map.* | 2-5 min | Event-driven on batch approval |
| Reference Data | kecamatans, desas, opd_modules | 60 min | On CRUD admin actions |
| Search Results | search.penduduk.*, search.rumah.* | 5 min | TTL only |
| User Sessions | laravel_session:* | 120 min | On logout |
| Rate Limits | throttle:api:*, throttle:login:* | 1-5 min | Auto-expire |

### 15.2 Queue Job Routing

| Queue | Priority | Workers | Memory | Timeout | Jobs |
|-------|----------|---------|--------|---------|------|
| default | Normal | 8 | 128MB | 60s | Sync, cleanup |
| notifications | High | 5 | 128MB | 90s | Email, alerts |
| exports | Low | 3 | 512MB | 600s | PDF, Excel |

### 15.3 Migration Steps: sync to Redis Queue

| Step | Task | Effort |
|------|------|--------|
| 1 | Install predis/predis via Composer | 5 min |
| 2 | Set REDIS_HOST, REDIS_PASSWORD in .env | 5 min |
| 3 | Change QUEUE_CONNECTION=sync to QUEUE_CONNECTION=redis | 1 min |
| 4 | Create job classes for notification emails | 2-3 hours |
| 5 | Install Laravel Horizon | 1 hour |
| 6 | Configure Horizon supervisors | 1 hour |
| 7 | Test in staging | 2 hours |
| 8 | Deploy to production | 1 hour |

Total: 1-2 days

---

## 16. Logging & Monitoring

### 16.1 Observability Architecture

Three Pillars of Observability:

| Pillar | Tool | Purpose |
|--------|------|---------|
| Metrics | Prometheus | HTTP requests, DB queries, queue depth, Redis memory, CPU/RAM/Disk |
| Logs | Loki / ELK Stack | Application logs, error stacks, audit events, access logs |
| Traces | OpenTelemetry | Request flow, service calls, slow query detection |

All three integrated into **Grafana** for unified dashboards.

### 16.2 Grafana Dashboards

| Dashboard | Metrics |
|-----------|---------|
| SIPANDU Application Overview | Request rate, error rate, latency |
| Database Performance | Query time, connections, slow queries |
| Queue Health | Horizon jobs, failed jobs, retry rate |
| Cache Hit Rate | Redis stats, hit/miss ratio |
| Mobile Sync Status | Push/pull volume, conflicts |
| Workflow Metrics | Batch submissions, approval rate, latency |
| Server Infrastructure | CPU, RAM, disk I/O, network |

### 16.3 Laravel Logging Configuration

```php
// config/logging.php -- Production channels

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
    ],

    'error' => [
        'driver' => 'single',
        'path' => storage_path('logs/error.log'),
        'level' => 'error',
    ],

    // JSON format for Loki/ELK ingestion
    'json' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.json'),
        'formatter' => Monolog\Formatter\JsonFormatter::class,
    ],

    // Dedicated audit log
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 90,
        'formatter' => Monolog\Formatter\JsonFormatter::class,
    ],
],
```

### 16.4 Prometheus Metrics Endpoint

Route for Prometheus scraping:
- Endpoint: GET /metrics
- Format: Prometheus text exposition
- Auth: Internal network only (nginx allow/deny)
- Scrape interval: 15 seconds

---

## 17. Backup & Disaster Recovery

### 17.1 Backup Strategy (3-2-1-1-0)

| Principle | Implementation |
|-----------|---------------|
| 3 copies | Primary DB + Local backup + Cloud backup |
| 2 media types | Disk + Cloud storage |
| 1 offsite | AWS S3 / Backblaze B2 |
| 1 immutable | WORM (Write Once Read Many) snapshot |
| 0 data loss | RPO = 0 (WAL continuous), RTO < 4 hours |

### 17.2 PostgreSQL Backup Schedule

| Type | Schedule | Tool | Retention |
|------|----------|------|----------|
| WAL Archive | Continuous | pgBackRest | 7 days |
| Daily Incremental | 23:00 daily | pgBackRest | 30 days |
| Weekly Full | Sunday 03:00 | pgBackRest | 12 weeks |
| Monthly Archive | 1st of month | pgBackRest | 12 months |
| Point-in-Time Recovery | Always enabled | pgBackRest | Up to any point |

### 17.3 Backup Commands

```bash
# pgBackRest (recommended over pg_dump for production)
pgbackrest --stanza=sipandu --type=full backup
pgbackrest --stanza=sipandu --type=incr backup

# Point-in-Time Recovery
pgbackrest --stanza=sipandu --type=time --set=2025-08-08-10:30:00 restore

# Restic (for files -- supports deduplication & encryption)
restic -r s3:s3://bucket/sipandu-backup backup /var/www/sipandu
restic -r s3:s3://bucket/sipandu-backup prune

# S3 Lifecycle
# Move to Glacier after 90 days, delete after 1 year
```

### 17.4 Disaster Recovery Plan

| Scenario | Detection | Response | Downtime |
|----------|----------|----------|----------|
| Database Failure | Prometheus alert + on-call | Promote replica, PITR if needed | 15-30 min |
| App Server Failure | Nginx health check | Auto-reroute to healthy servers | 0-2 min |
| Full DC Failure | Alert | Activate DR site, restore from S3 | 2-4 hours |
| Ransomware | Alert + integrity check | Isolate, verify backup, restore | 4-8 hours |

**RTO (Recovery Time Objective):** < 4 jam
**RPO (Recovery Point Objective):** < 1 jam (WAL continuous)

---

## 18. Strategi Skalabilitas

### 18.1 Scaling Phases

| Phase | Architecture | Capacity | Trigger |
|-------|-------------|----------|---------|
| **Phase 1: Current** | Single server (4 vCPU, 16GB) | ~50 concurrent users | Baseline |
| **Phase 2: Separate Tiers** | Web + DB + Redis separate | ~200 concurrent users | User growth |
| **Phase 3: Horizontal Scale** | Multiple app servers + LB | ~500-1000 users | Full OPD adoption |
| **Phase 4: CDN + Edge** | Cloudflare + CDN | ~5000+ users | Mobile app launch |

### 18.2 Database Optimization Checklist

```sql
-- Essential indexes
CREATE INDEX CONCURRENTLY idx_penduduk_nik ON penduduk(nik);
CREATE INDEX CONCURRENTLY idx_penduduk_no_kk ON penduduk(no_kk);
CREATE INDEX CONCURRENTLY idx_rumah_keluarga_id ON rumah(keluarga_id);
CREATE INDEX CONCURRENTLY idx_submission_batch_status ON submission_batch(status);
CREATE INDEX CONCURRENTLY idx_users_kecamatan ON users(kecamatan_id);
CREATE INDEX CONCURRENTLY idx_users_email ON users(email);

-- Partial unique: one active batch per module per kecamatan
CREATE UNIQUE INDEX idx_active_batch_per_module
    ON submission_batch(kecamatan_id, module)
    WHERE status IN ('draft', 'pending', 'in_review');

-- Monitoring slow queries
ALTER DATABASE sipandu SET log_min_duration_statement = 1000;
```

### 18.3 Performance Targets

| Metric | Target | Measurement |
|--------|--------|-------------|
| Page Load Time | < 2s (P95) | Grafana |
| API Response Time | < 500ms (P95) | Prometheus |
| Database Query Time | < 100ms (P95) | pg_stat_statements |
| Queue Job Processing | < 30s (P95) | Laravel Horizon |
| Uptime | > 99.5% | Prometheus |
| Cache Hit Rate | > 80% | Redis INFO |
| Error Rate | < 0.1% | Grafana |

---

## 19. Development to Staging to Production

### 19.1 CI/CD Pipeline (GitHub Actions)

```
PIPELINE FLOW:
================================================================

  Push to dev branch
        |
        v
  [1] Test Suite (PHPUnit, Dusk)
        |
        | on PR merge to staging
        v
  [2] Deploy to Staging (auto)
        |
        | on git tag to main
        v
  [3] Manual Approval (Required)
        |
        v
  [4] Deploy to Production
        |
        v
  [5] Smoke Tests
        |
        v
  [6] Notify (Slack/Email)
```

**Pipeline Jobs:**

| Job | Runner | Time | Trigger |
|-----|--------|------|---------|
| test | ubuntu-latest | ~10 min | Every push |
| deploy-staging | ubuntu-latest | ~5 min | Merge to staging |
| deploy-production | ubuntu-latest | ~10 min | Git tag on main |
| smoke-test | ubuntu-latest | ~2 min | After production deploy |

### 19.2 Environment Comparison

| Aspect | Development | Staging | Production |
|--------|-------------|---------|------------|
| APP_ENV | local | staging | production |
| APP_DEBUG | true | false | false |
| DB | SQLite/MySQL local | PostgreSQL mirror | PostgreSQL HA |
| Redis | Redis (local) | Redis Sentinel | Redis Sentinel HA |
| Queue | sync | redis | redis |
| Cache | file | redis | redis |
| Session | file | redis | redis |
| Mail | Mailpit | Real SMTP | Real SMTP |
| File Storage | local | MinIO | S3 |
| Logging | debug | info | warning |
| Monitoring | none | basic | full |

### 19.3 Deployment Checklist

```bash
#!/bin/bash
# scripts/deploy-production.sh

set -e

echo "=== SIPANDU Production Deployment ==="

# 1. Pre-deployment checks
php artisan env:show | grep APP_ENV

# 2. Database backup
pgbackrest --stanza=sipandu --type=incr backup

# 3. Pull latest code
git pull origin main

# 4. Install dependencies
composer install --no-dev --optimize --no-interaction

# 5. Run migrations
php artisan migrate --force

# 6. Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart workers
php artisan horizon:terminate
docker-compose restart queue

# 8. Smoke test
curl -f https://sipandu.sangihekab.go.id/health
curl -f https://sipandu.sangihekab.go.id/api/v1/health

echo "=== Deployment Complete ==="
```

---

## 20. Rekomendasi Teknologi

### 20.1 Technology Comparison & Recommendation

| Kategori | Sekarang | Direkomendasikan | Alasan |
|----------|---------|-----------------|--------|
| **Database** | MySQL 8 | PostgreSQL 15+ | JSONB, PostGIS, better concurrency |
| **PHP** | 8.1 | 8.2+ | Performance, readonly classes |
| **Cache** | file | Redis 7+ | Shared session, pub/sub |
| **Queue** | sync | Redis + Horizon | Non-blocking, monitoring |
| **File Storage** | local | MinIO/S3 | Versioning, replication |
| **Search** | LIKE | PostgreSQL FTS | GIN index, Indonesian tokenizer |
| **Frontend** | Filament 2.x | Filament 3.x (LTS) | Upgrade path ready |
| **Mobile** | Flutter (basic) | Flutter + Isar | Offline-first local DB |
| **Container** | None | Docker + Compose | Consistency, scaling |
| **CI/CD** | Manual | GitHub Actions | Automation, testing |
| **Monitoring** | None | Prometheus + Grafana | Observability |
| **SSL** | Self-signed | Lets Encrypt | Free, auto-renewal |
| **Load Balancer** | None | Nginx | Reverse proxy + LB |
| **Connection Pool** | None | PgBouncer | DB connection efficiency |
| **Backup** | Manual | pgBackRest + Restic | Automated, tested |
| **Secret Management** | .env | SOPS / Vault | Encryption, rotation |
| **Infrastructure** | VPS | Cloud (AWS/DO) | Managed services, HA |

### 20.2 PostgreSQL vs MySQL Decision

| Aspect | MySQL (Current) | PostgreSQL 15+ (Recommended) |
|--------|-----------------|------------------------------|
| JSON Support | JSON column (basic) | JSONB (binary, indexed) |
| Full-text | LIKE / MyISAM | GIN index, better tokenizer |
| Geospatial | Basic ST_* functions | PostGIS extension |
| Partitioning | Available | Native, more flexible |
| Concurrency | Table-level locking | Row-level MVCC |
| Replication | Async binlog | Streaming (sync available) |
| Connection Pool | ProxySQL / MC | PgBouncer (native) |
| Migration Effort | - | 2-4 weeks estimated |

**Verdict:** PostgreSQL 15+ with PostGIS extension.
Reason: payload_snapshot JSONB indexing, PostGIS for rumah geolocation, better concurrency for multi-user editing.

### 20.3 Effort Estimation

| Task | Estimated Effort | Risk |
|------|-----------------|------|
| Redis queue + Horizon | 1-2 days | Low |
| PostgreSQL migration | 2-4 weeks | Medium |
| Docker setup | 3-5 days | Low |
| CI/CD pipeline | 1-2 weeks | Low |
| Staging environment | 2-3 days | Low |
| Monitoring setup | 3-5 days | Low |
| S3/MinIO storage | 1-2 weeks | Low |
| Full production stack | 2-4 weeks | Medium |

---

## 21. Daftar Istilah

| Istilah | Penjelasan |
|---------|------------|
| **SSOT** | Single Source of Truth -- satu sumber data yang dipercaya |
| **RBAC** | Role-Based Access Control -- kontrol akses berdasarkan peran |
| **MVCC** | Multi-Version Concurrency Control -- cara PostgreSQL menangani akses concurent |
| **WAL** | Write-Ahead Logging -- mekanisme PostgreSQL untuk backup dan replikasi |
| **PITR** | Point-in-Time Recovery -- restore database ke waktu tertentu |
| **RPO** | Recovery Point Objective -- maksimal toleransi kehilangan data |
| **RTO** | Recovery Time Objective -- maksimal waktu downtime yang ditoleransi |
| **GIN Index** | Generalized Inverted Index -- index untuk full-text search dan JSON |
| **PostGIS** | Ekstensi PostgreSQL untuk data geospasial |
| **PgBouncer** | Connection pooler untuk PostgreSQL |
| **Laravel Horizon** | Dashboard dan manager untuk queue jobs Laravel |
| **Spatie Permission** | Package Laravel untuk RBAC (roles + permissions) |
| **Modular Monolith** | Arsitektur monolithic dengan batas-batas modul yang jelas |
| **Bounded Context** | Batas konseptual dalam domain |
| **Outbox Pattern** | Pattern untuk memastikan konsistensi antara write database dan publish event |
| **CDC** | Change Data Capture -- mendeteksi perubahan data untuk sinkronisasi |
| **WAF** | Web Application Firewall -- firewall khusus aplikasi web |
| **CDN** | Content Delivery Network -- jaringan server untuk distribusi konten |
| **TTL** | Time To Live -- berapa lama cache berlaku sebelum di-invalidate |
| **ORM** | Object-Relational Mapping -- Eloquent di Laravel |
| **API Gateway** | Entry point tunggal untuk semua request API |
| **pgBackRest** | Tool backup/restore untuk PostgreSQL dengan dukungan streaming replication |
| **Restic** | Backup tool dengan deduplikasi dan enkripsi |
| **Loki** | Log aggregation system yang kompatibel dengan Grafana |
| **Horizon** | Laravel job monitoring dashboard |
| **Sentinel** | Redis high-availability solution |

---

## Catatan Implementasi

### Urutan Prioritas Upgrade

```
URGENT (Lakukan secepatnya):
├── 1. Migration: sync -> redis queue (hilangkan blocking jobs)
├── 2. Redis cache (shared session antar app servers)
└── 3. PostgreSQL migration (dari MySQL)

IMPORTANT (1-3 bulan):
├── 4. Docker containerization (dev + staging)
├── 5. CI/CD pipeline (automated testing)
├── 6. Staging environment
├── 7. Backup automation (pgBackRest)
└── 8. Nginx reverse proxy + SSL

STANDARD (3-6 bulan):
├── 9. Monitoring (Prometheus + Grafana)
├── 10. Laravel Horizon setup
├── 11. File storage -> MinIO/S3
├── 12. Load balancer dengan multiple app servers
├── 13. Database read replica
└── 14. PgBouncer connection pooling

OPTIONAL (6-12 bulan):
├── 15. Kubernetes migration
├── 16. Elasticsearch integration
├── 17. Real-time dashboard (Laravel Reverb / Pusher)
├── 18. Two-Factor Authentication
└── 19. API versioning (v2)
```

---

*Dokumen ini adalah rancangan arsitektur production-ready.*
*Tidak ada modifikasi source code yang dilakukan.*
*Estimasi effort berdasarkan kompleksitas sistem dan praktik terbaik DevOps.*

*Tahun: 2025-2026*
*Platform: Kabupaten Kepulauan Sangihe, Sulawesi Utara, Indonesia*
