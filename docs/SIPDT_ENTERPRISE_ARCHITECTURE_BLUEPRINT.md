# SIPDT Enterprise Architecture Blueprint

## Sistem Informasi Pendataan Terpadu Kabupaten Kepulauan Sangihe

Versi 1.0 | 7 Agustus 2026  
Status: target architecture dan roadmap; bukan implementasi kode

---

## 0. Ringkasan Eksekutif

SIPDT diarahkan menjadi Government Data Platform Kabupaten, bukan kumpulan aplikasi OPD. OPD menjadi pemilik dan produsen dataset; Kominfo menjadi pengelola platform, Walidata/Gateway Publikasi, serta pengawal kualitas dan keamanan data.

Keputusan arsitektur:

1. Modular monolith sebagai target tiga tahun pertama, dengan batas modul dan kontrak API yang disiplin.
2. Satu platform data, master data bersama, dan banyak dataset; bukan database terpisah per OPD.
3. Data resmi hanya lahir melalui submission, verification, approval, dan publication workflow.
4. Mobile offline-first dengan local database, outbox, retry, idempotency, conflict resolution, dan audit.
5. Operational database dipisahkan dari analytical/public read model.
6. API-first dan metadata-first sejak awal.
7. AI berfungsi sebagai decision support, bukan pengganti verifikator atau pengambil keputusan.

Rantai nilai:

~~~text
Master Data -> Dataset Registry -> Capture -> Submission -> Workflow
-> Verification -> Approval -> Publication -> Analytics / API / Portal Publik
~~~

Arsitektur ini mengikuti arah SPBE untuk mengurangi duplikasi aplikasi/infrastruktur, meningkatkan standardisasi dan integrasi, serta prinsip Satu Data Indonesia tentang standar data, metadata, interoperabilitas, kode referensi, dan data induk. Rujukan: [Kementerian PANRB tentang SPBE](https://menpan.go.id/site/tentang-kami/kedeputian/transformasi-digital-pemerintah/sistem-pemerintahan-berbasis-elektronik-spbe-2) dan [Portal Satu Data Indonesia - Regulasi](https://data.go.id/regulation).

---

## 1. Vision Architecture

### Visi 10-20 tahun

SIPDT menjadi sumber data pemerintahan daerah yang terpercaya, terukur, dapat ditelusuri, aman, dan dapat dibagipakaikan. Setiap indikator publik memiliki owner, definisi, periode, status validasi, versi, provenance, dan aturan publikasi.

### Prinsip

| Prinsip | Implikasi |
|---|---|
| Platform, bukan aplikasi OPD | OPD memiliki dataset, bukan schema aplikasi sendiri |
| Single Source of Truth | Master wilayah, penduduk, fasilitas, organisasi, dan kode bersama |
| Data as a product | Owner, steward, metadata, SLA, quality score, versi, lifecycle |
| Official data by workflow | Input tidak otomatis menjadi data resmi |
| Metadata before data | Dataset tanpa metadata minimum tidak boleh dipublikasi |
| Secure by design | Least privilege, separation of duties, audit, backup |
| Offline is normal | Mobile dirancang untuk koneksi terputus |
| API first | Kontrak API versioned untuk semua integrasi |
| Open when safe | Data publik melalui agregasi, masking, dan privacy policy |
| Evolvable | Modul dapat diekstrak menjadi service jika benar-benar diperlukan |

### Capability map

~~~text
Governance: Data | Security | Privacy | Architecture | Portfolio | Compliance

Platform: Identity | Master Data | Dataset Catalog | Capture | Submission
          Workflow | Quality | Verification | Publication | Analytics | GIS
          Notification | Audit | API | Integration | AI | Portal Publik

Foundation: Reference Code | Storage | Search | Queue | Observability
            Backup/DR | Configuration | Feature Flags | Developer Platform
~~~

---

## 2. Business Architecture

### Operating model

~~~text
Produsen Data OPD / Desa
        -> Data Steward Dataset
        -> Submission + Metadata
        -> Verifikasi Kecamatan / OPD
        -> Kominfo / Walidata
        -> Published Dataset
        -> Dashboard / API / Portal Publik
~~~

### Peran

| Peran | Tanggung jawab | Batas |
|---|---|---|
| Super Admin | konfigurasi teknis, role, feature flag | bukan satu-satunya approver |
| Kominfo/Walidata | katalog, kualitas, publikasi, API gateway | tidak mengubah fakta tanpa audit |
| Kepala OPD | owner dataset dan approval bisnis | domain sendiri |
| Operator OPD | input, import, perbaikan, submit | tidak publish sendiri |
| Admin Kecamatan | verifikasi kewilayahan | kecamatan sendiri |
| Admin Desa | capture dan koreksi | desa sendiri |
| Petugas Lapangan | capture, GPS, foto, draft | tidak menghapus data resmi |
| Bupati/Pimpinan | dashboard dan keputusan | read-only default |
| Masyarakat | data publik dan informasi | tidak melihat raw/PII |

### Business services

Registrasi dataset; capture web/mobile; import; submission dan revisi; verifikasi; quality scoring; approval; publication; dashboard; GIS; open data; API; audit; arsip; dan permintaan data publik.

### KPI

- Persentase dataset yang memiliki owner, steward, metadata, workflow, dan SLA.
- Waktu submission sampai published.
- First-pass quality rate.
- Sync success/conflict rate mobile.
- Workflow SLA compliance.
- Availability platform/portal.
- Persentase API terdokumentasi dan terversi.
- Persentase data publik yang memiliki provenance dan freshness.

---

## 3. Application Architecture

### Tiga produk

~~~text
+--------------------+       +--------------------+
| SIPDT Mobile       |       | Portal Publik      |
| Flutter + Isar     |       | public read model  |
| offline capture    |       | open data + GIS    |
+---------+----------+       +---------+----------+
          | REST/SYNC API               | Public API
          +--------------+--------------+
                         v
              +------------------------+
              | SIPDT Platform Backend |
              | Laravel Modular        |
              | Monolith               |
              +------------------------+
              | Identity/API Gateway   |
              | Master Data            |
              | Dataset Registry       |
              | Capture & Import       |
              | Submission             |
              | Workflow/Verification  |
              | Quality & Publication  |
              | Analytics/GIS          |
              | Notification/Audit     |
              | AI Assistant           |
              +------------------------+
~~~

### Modul backend

1. Identity & Access.
2. Reference & Master Data.
3. Dataset Registry.
4. Capture & Import.
5. Submission, Snapshot & Revision.
6. Workflow, Task & Approval.
7. Data Quality & Issue Management.
8. Publication & Public Data Product.
9. Analytics & GIS.
10. API & Integration.
11. Notification.
12. Audit & Compliance.
13. AI Gateway, RAG dan Evaluation.

Setiap modul memiliki use case/service, policy, event, query, test, dan ownership. Resource Filament hanya adapter UI; business rule berada di application/domain service.

### Batas modularitas

- Tidak ada akses langsung ke tabel modul lain jika ada service/event.
- Database boleh satu pada fase awal, tetapi ownership tabel wajib jelas.
- Gunakan event bisnis seperti SubmissionApproved, DatasetPublished, dan SyncConflictDetected.
- Integrasi eksternal hanya melalui Integration/API module.
- UI tidak boleh menentukan aturan approval inti.

---

## 4. Technology Architecture

### Baseline workspace

Platform saat ini sudah memiliki Laravel 10, Filament 2, Livewire, Spatie Permission, modul wilayah/penduduk/rumah, kesehatan, pendidikan, submission/workflow, audit trail, portal publik, analytics, dan AI assistant. Fondasi ini cukup untuk fase foundation; business logic perlu dipisahkan bertahap dari Resource Filament.

### Target stack

| Area | Rekomendasi |
|---|---|
| Backend | Laravel LTS-compatible, modular domain |
| Back office | Filament + Livewire |
| Mobile | Flutter + Isar + background sync |
| Database | PostgreSQL preferred; MySQL fase transisi |
| Queue/cache | Redis |
| Object storage | S3-compatible Government Cloud |
| GIS | PostGIS; GeoJSON/vector tiles |
| Search | PostgreSQL full-text; OpenSearch saat skala perlu |
| API | REST JSON + OpenAPI + /api/v1 |
| Auth | Sanctum; OIDC/SSO-ready |
| Observability | structured logs, metrics, traces, alerts |
| Delivery | CI/CD, test, security scan, migration gate |
| Secrets | Secret Manager/Vault, bukan repository |
| AI | provider gateway, adapter, RAG approved-data |

### PostgreSQL versus MySQL

MySQL tetap valid untuk aplikasi saat ini. PostgreSQL menjadi target jangka panjang karena dukungan PostGIS, JSON metadata, constraint, analytical query, partitioning, dan extensibility lebih kuat. Migrasi hanya setelah data dictionary, schema contract, backup, rehearsal, dan rollback plan tersedia.

---

## 5. Data Architecture

### Lapisan data

~~~text
1. Master/Reference: wilayah, penduduk, rumah, OPD, fasilitas, kode
2. Operational Dataset: kesehatan, pendidikan, jalan, UMKM, sosial, lingkungan
3. Submission/Evidence: draft, batch, snapshot, GPS, foto, attachment, revision
4. Official Published: approved release, provenance, validity, publication policy
5. Analytical Read Model: KPI, aggregate, trend, spatial index
6. Public Data Product: open data, API, map, dashboard, download
~~~

### Dataset Registry

Dataset wajib memiliki dataset ID, nama/kode, definisi indikator, owner, steward, sumber, periode, frekuensi, schema version, field dictionary, classification, master/reference dependency, quality rule, workflow profile, approver, retention, publication policy, lineage, last update, dan next update.

### High-level ERD

~~~text
ORGANIZATION(OPD) 1---N DATASET N---1 DATASET_VERSION
DATASET 1---N DATASET_FIELD
DATASET 1---N QUALITY_RULE

USER N---N ROLE
USER N---1 OPD
USER N---1 WILAYAH_SCOPE

MASTER_WILAYAH 1---N VILLAGE 1---N HOUSE
MASTER_HOUSE 1---N HOUSEHOLD 1---N MASTER_PERSON

DATASET 1---N RECORD 1---N RECORD_VERSION
RECORD N---1 MASTER_PERSON
RECORD N---1 MASTER_WILAYAH

SUBMISSION N---1 DATASET_VERSION
SUBMISSION N---1 USER
SUBMISSION 1---N SUBMISSION_ITEM N---1 RECORD
SUBMISSION 1---N SNAPSHOT
SUBMISSION 1---N WORKFLOW_INSTANCE 1---N WORKFLOW_TASK

DATASET_RELEASE 1---N PUBLICATION_ITEM N---1 RECORD_VERSION
AUDIT_EVENT N---1 USER
FILE_OBJECT N---1 RECORD / SUBMISSION
~~~

### Quality gate

1. Capture-time: required, format, enum, range, GPS, duplicate lokal.
2. Ingestion-time: schema, referential integrity, duplicate server, malware.
3. Workflow-time: completeness, cross-field, cross-dataset, outlier.
4. Publication-time: privacy, aggregation, suppression, freshness, provenance.

---

## 6. Infrastructure Architecture

~~~text
Users / Mobile / Public
          |
       DNS + TLS
          |
   WAF / Load Balancer
          |
  Web/API Application Tier
      |       |       |
    Redis   Queue   Object Storage
      |
 PostgreSQL Primary ---- Read Replica / Analytics DB
      |
 Backup Vault + DR / Government Cloud
~~~

Production minimum:

- WAF, reverse proxy, TLS, rate limit.
- Dua atau lebih application instances.
- Queue worker terpisah dari web process.
- Database backup dan point-in-time recovery bila tersedia.
- Object storage dengan versioning, lifecycle, checksum, malware scan.
- Central log, metrics, traces, alerts.
- Bastion/admin access dengan MFA.
- Dev, staging, production terpisah.

Target awal: availability 99.5%; RPO 15 menit bila PITR tersedia; RTO 4 jam fase awal, 1 jam setelah DR matang.

---

## 7. Security Architecture

SIPDT memakai pendekatan zero trust: setiap request dinilai berdasarkan identity, role, scope, device/token, resource, dan policy; lokasi jaringan bukan dasar kepercayaan.

Kontrol:

- RBAC untuk role dan ABAC untuk OPD, wilayah, dataset, status.
- Separation of duties; submitter tidak boleh approve submission sendiri.
- MFA untuk Super Admin, Kominfo, dan approver.
- Device binding, token expiry, rotation, revoke.
- TLS, encryption at rest, secure headers, CSRF, brute-force protection.
- Masking NIK, nomor telepon, alamat, dan data sensitif.
- PII tidak masuk public API/AI tanpa policy.
- Immutable audit untuk login, read sensitive, export, write, approve, publish, delete.
- SAST, DAST, dependency scan, secret scan, penetration test.
- Restore test; backup yang belum berhasil direstore belum dianggap aman.

Klasifikasi:

~~~text
PUBLIC       -> agregat dan release publik
INTERNAL     -> operasional antar unit berwenang
CONFIDENTIAL -> data individu/operasional sensitif
RESTRICTED   -> risiko tinggi, approval akses khusus
~~~

---

## 8. Mobile Offline Architecture

Mobile bukan miniatur web, melainkan local-first capture client.

~~~text
User Action -> Local Validation -> Isar Transaction
-> Local Draft/Outbox -> Background Scheduler
-> Upload Chunk/Evidence -> Server ACK
-> Local Status Updated
~~~

Entity lokal minimum:

- local ID UUID dan server ID nullable.
- dataset ID dan dataset version.
- sync state: draft, queued, uploading, synced, conflict, failed, rejected.
- client/server updated time, device ID.
- version, base version, operation ID.
- attachment manifest, GPS, foto, checksum.
- retry count, next retry time, last error.

Device wajib mendukung registration/revoke, local encryption, auto-lock, token expiry, remote revoke, evidence metadata, kompresi foto, dan resumable upload.

---

## 9. Synchronization Architecture

Protocol:

~~~text
Mobile -> POST /api/v1/sync/push
         operation_id, dataset, record_id, base_version, payload

Server -> ACK / CONFLICT / REJECT

Mobile -> GET /api/v1/sync/pull?cursor=...
Server -> changes, tombstones, next_cursor
~~~

Conflict policy:

| Konflik | Tindakan |
|---|---|
| Record baru | create dan server ID |
| Base version sama | accept dan increment version |
| Stale version | conflict queue, jangan overwrite diam-diam |
| Field non-critical | merge bila rule jelas |
| Field/status critical | manual steward/verifikator |
| Delete versus update | tombstone dan review |
| Duplicate NIK/GPS | quality issue, bukan auto-merge |

Retry menggunakan exponential backoff dan jitter. Retry hanya network/5xx/429; validation 4xx tidak diulang tanpa perubahan payload. Semua operasi memiliki idempotency key dan dead-letter queue.

---

## 10. AI Architecture

AI Assistant menjadi decision support untuk pencarian, ringkasan, analitik, dan rekomendasi tindak lanjut.

~~~text
User Question -> AI Gateway -> Scope Check
-> Intent/Tool Router -> Approved Dataset Query/RAG
-> PII Redaction -> Model Provider
-> Provenance/Citation -> Answer + Confidence + Audit
~~~

Guardrail:

- Retrieval mengikuti permission user.
- Data personal diminimalkan, dimasking, atau ditolak.
- Jawaban menyebut sumber, periode, dan confidence.
- Tool/SQL memakai read-only service account.
- Prompt injection defense dan output validation.
- Model provider dapat diganti melalui adapter.
- Human review untuk konten publik, risiko tinggi, dan keputusan administratif.
- Simpan evaluasi grounding, latency, cost, prompt, tool call, dan model sesuai retention.

Use case bertahap: metadata Q&A; ringkasan dashboard; quality triage; draft laporan bersumber; geospatial insight; forecasting setelah kualitas historis memadai.

---

## 11. API Architecture

API product groups:

~~~text
/api/v1/auth/*
/api/v1/reference/*
/api/v1/master/*
/api/v1/datasets/*
/api/v1/submissions/*
/api/v1/workflows/*
/api/v1/sync/*
/api/v1/analytics/*
/api/v1/public/*
/api/v1/integrations/*
~~~

Standar:

- OpenAPI versioned.
- Error contract konsisten, RFC 7807-style.
- Cursor pagination, filter, sort, include, field selection.
- Asynchronous export.
- Idempotency-Key pada create/sync.
- ETag/If-Match atau version untuk optimistic concurrency.
- API scopes, rate limit, signed webhook, replay protection.
- Deprecation policy minimal 12 bulan.
- Public API tidak memakai primary key sensitif sebagai satu-satunya identifier.

---

## 12. Workflow Architecture

State machine target:

~~~text
DRAFT -> SUBMITTED -> VALIDATING
-> NEEDS_REVISION -> RESUBMITTED
-> VERIFIED_DOMAIN -> VERIFIED_WILAYAH
-> APPROVED -> SCHEDULED/EMBARGOED
-> PUBLISHED -> SUPERSEDED/ARCHIVED
~~~

Workflow profile disimpan per dataset dan menentukan submitter, verifikator wilayah, domain owner, Kominfo approval, evidence, SLA, alasan penolakan, revision policy, redaction, dan publication policy.

Submission menyimpan immutable snapshot. Approval mengesahkan snapshot, bukan data live yang dapat berubah. Published release memiliki release ID, timestamp, approver, dataset version, source submission, dan checksum.

---

## 13. Government Cloud Architecture

Model hybrid portable:

~~~text
Government Cloud Primary
  App | API | Queue | DB | Object Storage | Monitoring

Government Cloud DR
  Backup replication | Warm standby | Restore rehearsal

Field Edge
  Mobile Isar | Offline evidence | Temporary network cache
~~~

Requirements:

- Container image reproducible.
- Infrastructure as Code.
- Portable DB backup.
- S3-compatible object storage.
- Tidak mengunci business logic pada proprietary service.
- Exit plan dan egress cost.
- SLA cloud mencakup data residency, logging, admin access, dan incident response.

Kominfo memegang platform governance. Vendor/cloud menyediakan infrastructure terkelola dengan akses dibatasi, diaudit, dan dapat dicabut.

---

## 14. Deployment Architecture

~~~text
Developer -> CI -> Dev -> Integration -> Staging/UAT -> Production
                                  |           |
                              Security     Approval
~~~

Release gate:

1. Unit, feature, integration, API contract test.
2. Static analysis, dependency, secret scan.
3. Migration forward/rollback rehearsal.
4. Backup restore test.
5. Security test.
6. UAT data owner.
7. Change approval dan release note.
8. Canary/blue-green atau maintenance window.
9. Smoke test dan rollback readiness.

Semua request/job penting memiliki correlation ID. Monitor error, latency, throughput, queue, database lock/slow query, workflow SLA, mobile sync, API rate limit, AI cost, dan backup freshness.

---

## 15. Future Scalability Architecture

### Scale 1: foundation

Modular codebase, transactional database dengan ownership boundary, queue untuk proses berat, dan read model/cache untuk dashboard.

### Scale 2: multi-OPD/mobile

Read replica, analytical schema, object storage, resumable upload, search index, sync workers, dataset registry, dan API products.

### Scale 3: selective service extraction

Ekstraksi service hanya jika deployment cadence, load, security boundary, failure domain, team ownership, SLO, atau storage requirement memang berbeda.

Kandidat awal: Sync Gateway, Public Data API, Analytics/ETL, Notification, dan AI Gateway. Master Data dan Workflow jangan diekstrak terlalu cepat karena membutuhkan consistency dan governance kuat.

Data scale: partition event/audit, materialized KPI, archive cold data, spatial index, asynchronous export, catalog, dan lineage.

---

## 16. Service Diagram

~~~text
                 +------------------+
                 | Identity & Scope |
                 +--------+---------+
                          |
 +-----------+    +-------v--------+    +----------------+
 | Mobile    +--->| API / Sync     |<---+ External APIs  |
 +-----------+    +-------+--------+    +----------------+
                          |
        +-----------------+------------------+
        |                 |                  |
 +------v------+   +------v------+   +-------v-------+
 | Master Data |   | Dataset     |   | Submission    |
 | & Reference |   | Registry    |   | + Snapshot    |
 +------+------+   +------+------+   +-------+-------+
        |                 |                  |
        +-----------------+------------------+
                          v
                 +--------+---------+
                 | Workflow/Quality|
                 +--------+---------+
                          |
        +-----------------+-----------------+
        |                                   |
 +------v------+                    +-------v-------+
 | Publication |                    | Audit/Notify  |
 +------+------+                    +---------------+
        |
 +------v-------+       +--------------+
 | Analytics/GIS|------>| Public Portal|
 +--------------+       +--------------+

AI Gateway hanya membaca approved data/tools melalui policy gateway.
~~~

---

## 17. Microservice versus Modular Monolith

| Kriteria | Modular monolith | Microservices |
|---|---|---|
| Fase awal | cepat dan sederhana | lambat dan kompleks |
| Workflow consistency | lebih mudah | distributed transaction |
| Operasional | lebih ringan | perlu platform/SRE |
| Scale independent | terbatas | sangat baik |
| Team saat ini | lebih cocok | berisiko over-engineering |
| Rekomendasi SIPDT | fase 1-3 | selektif mulai fase 3 |

Keputusan: gunakan modular monolith dengan event boundary. Rancang kontrak seolah modul dapat dipisah, tetapi jangan membayar kompleksitas microservice sebelum kebutuhan terbukti.

---

## 18. Roadmap Implementasi 5 Tahun

### Tahun 1: foundation dan governance

Governance board; dataset registry; data dictionary; master/reference policy; owner/steward; classification; workflow; submission snapshot; audit; backup; API v1; CI/CD; security baseline; monitoring.

Exit: dataset prioritas memiliki owner, metadata, workflow, quality rule, audit.

### Tahun 2: mobile dan perluasan OPD

Flutter offline-first; device management; sync gateway; outbox/retry/conflict; pilot 2-3 dataset; import controlled; quality dashboard; evidence object storage.

Exit: pilot offline dan seluruh konflik tercatat/terselesaikan.

### Tahun 3: integrasi dan analytics

Dataset jalan, sekolah, UMKM, sosial, lingkungan, aset; analytical read model; GIS; API products; webhook; KPI lintas OPD; quality scorecard.

Exit: KPI lintas OPD berasal dari published data dengan lineage.

### Tahun 4: scale, resilience, AI governance

Read replica; partitioning; DR zone; public API portal; catalog; AI grounded approved data; anomaly triage; SSO readiness; penetration test; continuity exercise.

Exit: SLO, DR, security, dan AI evaluation threshold terpenuhi.

### Tahun 5: interoperability dan institutionalization

Government Cloud/API nasional sesuai otorisasi; semantic mapping; open data maturity; public feedback; selective service extraction; architecture repository; capability measurement; lifecycle funding.

Exit: SIPDT menjadi platform resmi lintas OPD dengan governance dan pendanaan lifecycle.

---

## 19. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Definisi indikator berbeda antar-OPD | Registry, dictionary, steward, metadata approval |
| Kominfo menjadi bottleneck | Delegated verification, SLA, automated quality gate |
| PII bocor ke portal/AI | Classification, masking, aggregation, policy gateway |
| Mobile duplicate/conflict | UUID, idempotency, version, conflict queue |
| Schema change memutus integrasi | Dataset versioning, API contract, rehearsal |
| Cloud/vendor lock-in | Container, portable DB/object, IaC, exit plan |
| Microservice terlalu dini | Modular monolith dan extraction trigger |
| Dashboard membebani transaksi | Published read model dan materialized view |
| Backup tidak bisa direstore | Restore drill dan RPO monitoring |
| AI salah | Grounding, citation, confidence, human review |
| Pergantian personel | SOP, RACI, runbook, training, ADR |
| Anggaran hanya pembangunan | TCO 5-10 tahun, OPEX, maintenance, refresh plan |
| Jaringan kepulauan | Offline-first, resumable upload, queue, retry |

---

## 20. Governance dan Operating Model

Forum:

- Digital Governance Board: Bupati/Sekda, Kominfo, Bappeda, OPD prioritas.
- Data Governance Board: Walidata, produsen data, steward, statistik/perencanaan.
- Architecture Review Board: architecture, security, infrastructure, application owner.
- Change Advisory Board: release, migration, incident, emergency change.

Artefak wajib:

- Enterprise principles dan target architecture.
- Capability map, dataset catalog, data dictionary.
- RACI dataset.
- API catalog/OpenAPI.
- Threat model dan security control matrix.
- Backup/DR dan incident runbook.
- Mobile sync conflict playbook.
- Retention/publication policy.
- Architecture Decision Records.

Definition of Done dataset:

- owner/steward;
- field definition dan reference code;
- classification/privacy review;
- workflow/approver;
- quality rules;
- API/read model/publication policy;
- audit/retention;
- UAT;
- monitoring dan support runbook.

---

## 21. Prioritas 90 Hari Pertama

1. Sahkan SIPDT sebagai platform data dan bentuk governance board.
2. Pilih lima dataset prioritas: Penduduk, Wilayah, Kesehatan/Stunting, Pendidikan, Jalan/Aset.
3. Selesaikan registry, dictionary, owner/steward, classification, workflow.
4. Tetapkan master data dan reference code policy.
5. Stabilkan submission/snapshot/workflow/publication sebagai kernel.
6. Pisahkan published read model dari query dashboard/portal.
7. Tetapkan API v1, OpenAPI, idempotency, pagination, error contract, audit.
8. Buat mobile sync proof-of-concept untuk satu dataset.
9. Lakukan threat modeling dan privacy review data personal.
10. Tetapkan SLO, backup/restore drill, monitoring, dan incident ownership.

## Kesimpulan

SIPDT sebaiknya dibangun sebagai platform data pemerintah yang modular, governed, workflow-driven, dan API-first. Laravel/Filament saat ini cukup untuk kernel platform. Fokus strategis bukan mengganti framework, melainkan menetapkan domain boundary, data contract, dataset lifecycle, security control, dan operating model yang dapat bertahan lintas periode proyek dan vendor.

Arsitektur paling aman adalah modular monolith yang disiapkan untuk evolusi, bukan microservice sejak hari pertama. Dengan master data, immutable submission snapshot, workflow per dataset, offline sync yang benar, dan public read model terpisah, SIPDT dapat tumbuh dari aplikasi pendataan menjadi platform pemerintahan digital Kabupaten Kepulauan Sangihe.

