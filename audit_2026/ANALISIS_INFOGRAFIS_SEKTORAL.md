# ANALISIS KEBUTUHAN DATA UNTUK INFOGRAFIS STATISTIK SEKTORAL
## Kabupaten Kepulauan Sangihe — Buku Sektoral 2024

**Tanggal Analisis**: 18 Oktober 2026
**Sumber**: STATISTIK_SEKTORAL_2024.pdf (7.5 MB, ~160 halaman)
**Konten**: 9 BAB, 93 Tabel, 20 Grafik
**Standar**: Perpres 39/2019 + Perbup Sangihe 30/2024

---

## 1. PETA STRUKTUR DOKUMEN

| BAB | Topik | # Tabel | # Grafik | OPD Produsen |
|-----|-------|---------|----------|--------------|
| 1 | Gambaran Umum | - | - | Setda |
| 2 | Pemerintahan & Kepegawaian | 11 | 3 | Setda, Setwan, BKPSDM |
| 3 | Sosial, Kemasyarakatan & Kesejahteraan | 25 | 6 | Dukcapil, Disdik, Dinkes, Dinsos, DPPKB, DP3A, DPMD, Dispora, Disnaker |
| 4 | Pertanian & Ketahanan Pangan | 12 | 2 | Ketapangan, Pertanian, Perkebunan, Perikanan |
| 5 | Perhubungan & Infrastruktur | 15 | - | Dishub, PUPR, Perpus, Perkim |
| 6 | Perdagangan, Industri, Koperasi & Modal | 9 | - | DPMPTSP, Koperasi, Disperindag |
| 7 | Keuangan, Pengawasan & Perencanaan | 7 | - | BPKPD, Inspektorat, Bappelitbang, Kesbangpol |
| 8 | Penanggulangan Bencana, Satpol PP & Lingkungan | 8 | 1 | BPBD, Satpol PP, DLH |
| 9 | Telekomunikasi & Pariwisata | 6 | - | Kominfo, Pariwisata |
| **TOTAL** | | **93** | **12** | **26+ OPD** |

Konten Buku Sektoral disusun dalam **3 tingkatan**:

1. **Master/Reference** (diisi sekali, diupdate bila ada perubahan)
   - Tabel 2.1, 2.2, 2.3, 2.5, 2.6 (Pejabat, DPRD, Perangkat Daerah)
   - Tabel 5.49 (Pelabuhan), 5.59 (Jalan), 5.63 (PSU)
   - Tabel 8.80 (Kawasan Rawan Bencana), 8.82 (SDM Satpol PP)
   - Tabel 9.91 (Menara Telekomunikasi), 9.93 (Objek Wisata)

2. **Tahunan** (data utama yang dilaporkan setiap tahun)
   - Tabel 3.12-3.31 (Kependudukan, Pendidikan, Kesehatan, KB, Kesra, Pemuda, Naker)
   - Tabel 4.37-4.48 (Pangan, Pertanian, Perkebunan, Perikanan)
   - Tabel 5.50-5.62 (Transportasi, Air, Perpustakaan, Perumahan, TPU)
   - Tabel 6.64-6.72 (Investasi, Koperasi, Industri, Pasar)
   - Tabel 7.73-7.79 (Anggaran, Pajak, IKU)
   - Tabel 8.81, 8.83, 8.84, 8.85, 8.86, 8.87 (Bencana, Satpol PP, LH)
   - Tabel 9.88, 9.89, 9.90, 9.92 (SPBE, Website, Kominfo, Wisatawan)

3. **Bulanan/Event/Real-time** (data berkala)
   - Bulanan: 3.21 (AKI/AKB - estimasi bulanan), 4.42-4.44 (panen), 6.69 (harga)
   - Harian: 5.50 (kendaraan baru)
   - Event: 5.51 (kecelakaan), 8.81 (bencana), 9.90 (pelayanan informasi)

---

## 2. DETAIL 93 TABEL: STRUKTUR DATA PER TABEL

### BAB 2: PEMERINTAHAN & KEPEGAWAIAN (11 Tabel)

#### Tabel 2.1 - Kepala Daerah (SETDA)
- **Struktur**: master (1 record per kepala daerah)
- **Field**: `no`, `nama`, `jabatan`, `periode_mulai`, `periode_selesai`
- **Status di sistem**: SUDAH ADA (`2.1`, 4 field)
- **Kebutuhan**: Tambah NIP, foto, dan jenis kelamin

#### Tabel 2.2 - Sekretaris Daerah (SETDA)
- **Struktur**: master
- **Field**: `no`, `nama`, `jabatan`, `nip`
- **Status**: SUDAH ADA (`2.2`)

#### Tabel 2.3 - Perangkat Daerah (SETDA)
- **Struktur**: master (33 baris: 32 OPD + 1 RSUD)
- **Field**: `no`, `kode_opd`, `nama_opd`, `kepala`, `jumlah_pegawai`
- **Status**: SUDAH ADA (`2.3`, 3 field)
- **Kebutuhan**: Sudah terhubung ke tabel OPD, hanya tinggal isi jumlah_pegawai

#### Tabel 2.4 - Kecamatan & Kampung (SETDA)
- **Struktur**: master dengan parent-child (Kecamatan -> Kampung/Kelurahan)
- **Field**: `kecamatan`, `jumlah_kelurahan`, `jumlah_kampung`, `jumlah_dusun`
- **Status**: SUDAH ADA (`2.4`) — sudah ada struktur wilayah di tabel wilayahs

#### Tabel 2.5-2.7 - DPRD (SETWAN)
- **Tabel 2.5**: `jenis_kelamin` (L/P), `pendidikan` (7 opsi), `jumlah`
- **Tabel 2.6**: `daerah_pemilihan`, `partai_politik`, `jumlah`
- **Tabel 2.7**: `jenis` (keputusan), `jumlah`
- **Status**: SUDAH ADA (semua)

#### Tabel 2.8 - PNS Berdasarkan Golongan (BKPSDM)
- **Field**: `golongan` (I/a, I/b, ..., IV/d), `jumlah_pns_laki`, `jumlah_pns_perempuan`, `total`
- **Dimensi**: golongan
- **Jumlah baris**: ~16 (semua pangkat/golongan)
- **Status**: SUDAH ADA (`2.8`, 3 field) — gunakan field tambahan

#### Tabel 2.9 - PNS Berdasarkan Eselon (BKPSDM)
- **Field**: `eselon` (I, II, III, IV, Non-Eselon), `jumlah`
- **Dimensi**: eselon

#### Tabel 2.10 - PNS Berdasarkan Jabatan (BKPSDM)
- **Field**: `jabatan` (Struktural, Fungsional, Pelaksana), `jumlah`

#### Tabel 2.11 - PNS Berdasarkan Pendidikan (BKPSDM)
- **Field**: `pendidikan`, `jumlah`

**TOTAL BAB 2**: 11 tabel -> SUDAH ADA STRUKTUR, tinggal isi data


---

### BAB 3: SOSIAL, KEMASYARAKATAN & KESEJAHTERAAN (25 Tabel + 6 Grafik)

#### Tabel 3.12 - Jumlah KK per Kecamatan (DUKCAPIL)
- **Dimensi utama**: kecamatan (15 kecamatan)
- **Field**: `kecamatan`, `kk_laki`, `kk_perempuan`, `jumlah`
- **Cakupan**: per kecamatan
- **STATUS DI SISTEM**: Ada (`3.12`, 4 field) — field SUDAH LENGKAP
- **TOTAL 2024**: 51.234 KK (L: 38.736, P: 12.498)

#### Tabel 3.13 - Jumlah Penduduk per Kecamatan (DUKCAPIL)
- **Dimensi**: kecamatan
- **Field**: `kecamatan`, `penduduk_laki`, `penduduk_perempuan`, `jumlah`
- **TOTAL 2024**: 135.860 jiwa (L: 69.187, P: 66.673)
- **STATUS**: Ada (`3.13`, 4 field)

#### Tabel 3.14 - Penduduk Berdasarkan Tingkat Pendidikan (DUKCAPIL)
- **Field**: `tingkat_pendidikan` (10 kategori: Tidak/Belum Sekolah, ..., Strata III), `laki`, `perempuan`, `jumlah`
- **STATUS**: Ada (`3.14`, 3 field)
- **Dimensi penting**: 10 tingkat pendidikan

#### Tabel 3.15 - Kepemilikan Akta Kelahiran (DUKCAPIL)
- **Field**: `kecamatan`, `laki_0_18`, `perempuan_0_18`, `jumlah`
- **TOTAL 2024**: 29.963 jiwa (22.05% dari total penduduk)
- **STATUS**: Ada (`3.15`)

#### Tabel 3.16 - Ketersediaan Sekolah & Penduduk Usia Sekolah (DISDIK)
- **Dimensi**: jenjang (PAUD, SD/MI, SMP/MTs)
- **Field**: `jenjang`, `jumlah_gedung_sekolah`, `jumlah_penduduk_usia`, `rasio`
- **STATUS**: Ada (`3.16`, 4 field)
- **TOTAL**: 228 PAUD, 207 SD, 62 SMP

#### Tabel 3.17 - Ketersediaan Guru & Murid (DISDIK)
- **Field**: `jenjang`, `jumlah_guru`, `jumlah_murid`, `rasio`
- **STATUS**: Ada (`3.17`, 4 field)
- **TOTAL**: 2.671 guru, 23.642 murid

#### Tabel 3.18 - Sarana & Prasarana Seni Budaya (DISDIK)
- **Field**: `uraian` (nama kesenian/sarana), `jumlah`
- **STATUS**: Ada (`3.18`)

#### Tabel 3.19 - Angka Melek Huruf & Lama Sekolah (DISDIK)
- **Field**: `tahun`, `angka_melek_huruf`, `lama_sekolah_tahun`
- **STATUS**: Ada (`3.19`)
- **TOTAL 2024**: AMH tidak spesifik per tabel, perlu cek

#### Tabel 3.20 - e-PPGBM Stunting per Puskesmas (DINKES)
- **Field**: `puskesmas` (17 puskesmas), `sasaran_balita`, `entry_pengukuran`, `%_pengukuran`, `jumlah_stunting`, `%_stunting`
- **TOTAL KABUPATEN**: 5.604 sasaran, 5.558 entry (99,18%), 114 stunting (2,05%)
- **STATUS**: Ada (`3.20`, 3 field — field generic "jumlah" + dimensi)
- **PERBAIKAN**: Perlu field spesifik stunting, sasaran, pengukuran

#### Tabel 3.21 - AKI, AKB, AHH (DINKES)
- **Field**: `indikator`, `nilai` (tunggal per tahun)
- **TOTAL 2024**: AKI=0, AKB=2,53, AHH=74,7
- **STATUS**: Ada (`3.21`, 3 field)
- **3 BARIS TUNGGAL**: AKI, AKB, AHH

#### Tabel 3.22 - Kesejahteraan Sosial (DINSOS)
- **Field**: `uraian`, `jumlah` (5 baris: Panti Asuhan, PMKS, KK Miskin, Anak Terlantar, Disabilitas)
- **STATUS**: Ada (`3.22`)

#### Tabel 3.23 - Data Panti (DINSOS)
- **Field**: `nama_panti`, `alamat`, `jumlah_penghuni`, `jenis_layanan`

#### Tabel 3.24-3.28 - DPPKB (Keluarga Berencana)
- **Tabel 3.25**: `jenis_fasilitas` (Klinik KB, PPKBD), `jumlah`
- **Tabel 3.26**: `metode_kb` (IUD, Suntik, Pil, Kondom, Implan, MOW, MOP), `jumlah_pus`, `jumlah_peserta_aktif`
- **Tabel 3.27**: `kelompok_umur` (15-19, 20-24, ..., 45-49), `jumlah_wus`
- **STATUS**: Ada (`3.25`, `3.26`, `3.27`)

#### Tabel 3.29-3.31 - DP3A (Pemberdayaan Perempuan)
- **Tabel 3.29**: Komponen IDG (5 baris)
- **Tabel 3.30**: Komponen IPG
- **Tabel 3.31**: `bentuk_kekerasan`, `jumlah_kasus`, `jumlah_korban`
- **STATUS**: Ada (`3.29`, `3.30`, `3.31`)

#### Tabel 3.32 - DPMD (Pemberdayaan Desa)
- **Field**: `uraian` (status desa, BUMDes, dll), `jumlah`
- **STATUS**: Ada (`3.32`)

#### Tabel 3.33 - DISPORA (Pemuda & Olahraga)
- **Field**: `indikator` (atlet, organisasi, sarana, event), `jumlah`
- **STATUS**: Ada (`3.33`)

#### Tabel 3.34 - DISNAKER (Tenaga Kerja)
- **Field**: `indikator` (pencari kerja, lowongan,angkatan kerja), `jumlah`
- **STATUS**: Ada (`3.34`)

#### Tabel 3.35-3.36 - TPT & TPAK
- **Tabel 3.35**: TPT per semester
- **Tabel 3.36**: TPAK per semester
- **STATUS**: Ada (`3.35`, `3.36`)

---

### BAB 4: PERTANIAN & KETAHANAN PANGAN (12 Tabel + 2 Grafik)

#### Tabel 4.37-4.38 - Konsumsi Gizi (KETPANGAN)
- **Tabel 4.37**: `jenis_bahan_makanan` (9 kategori), `kkal_per_hari`, `%_ake`, `gram_protein_per_hari`, `%_akp`
- **Tabel 4.38**: `kelompok_pangan`, `berat_gram`, `kkal`, `%`, `skor_aktual`, `skor_ake`, `skor_maksimal`, `skor_pph`
- **TOTAL 2024**: Energi 1.908 kkal (90% AKE), Protein 54,7 gram (96% AKP), Skor PPH 76,0
- **STATUS**: Ada (`4.37`, `4.38`) — field generic

#### Tabel 4.39 - Resiko Rawan Pangan (KETPANGAN)
- **Field**: `tingkat_resiko` (Sangat Rentan, Rentan, Agak Rentan), `kampung` (daftar kampung)
- **STATUS**: Ada (`4.39`)

#### Tabel 4.40 - Lumbung Pangan (KETPANGAN)
- **Field**: `kecamatan`, `kelompok_lpm`, `kapasitas_ton`, `komoditas`, `pengisian_kg`
- **TOTAL**: 13 kelompok LPM
- **STATUS**: Ada (`4.40`)

#### Tabel 4.41-4.44 - PERTANIAN (DISTAN)
- **Tabel 4.41**: `indikator_ekonomi_pertanian` (PDRB Pertanian, dll), `nilai`
- **Tabel 4.42**: `komoditas_tanaman_pangan` (7 jenis: Padi, Jagung, Ubi Kayu, Ubi Jalar, Talas, Kacang Tanah, Ubi Lainnya), `luas_tanam_ha`, `luas_panen_ha`, `produksi_ton`, `produktivitas_ton_ha`
- **Tabel 4.43**: `komoditas_hortikultura` (11 jenis: Sawi, Bayam, Buncis, Cabe, Kacang Panjang, Mentimun, Kangkung, Terung, Tomat, Semangka), `luas_tanam_ha`, `luas_panen_ha`, `produksi_ton`, `produktivitas_ton_ha`
- **Tabel 4.44**: `komoditas_perkebunan` (Kelapa Dalam, Kelapa Hibrida, Cengkeh, Pala, Sagu), `luas_lahan_ha`, `produksi_ton`
- **TOTAL Perkebunan**: 38.628,75 ha, 28.114,22 ton
- **STATUS**: Ada (`4.41`, `4.42`, `4.43`, `4.44`)

#### Tabel 4.45-4.48 - PERIKANAN (PERIKANAN)
- **Tabel 4.45**: Capaian Indikator Kinerja - 17 indikator (Pendapatan Nelayan, Produksi Tangkap, Produksi Budidaya, Cold Storage, Pabrik Es, Nilai Tukar Nelayan, dll)
- **Tabel 4.46**: Produksi Perikanan Menurut Jenis (30+ jenis ikan + binatang berkulit keras/lunak)
- **Tabel 4.47**: Produksi Tangkap (35 jenis: Selar, Kuwe, Layang, Kerapu, Kakap, Tongkol, Cakalang, Tuna, Cumi, Gurita, dll) -> `jenis_ikan`, `jumlah_produksi_kg`, `harga_rata_rp_kg`, `nilai_produksi_rp`
- **Tabel 4.48**: Armada Kapal (`kapasitas_gt` (0-3, 3-5, 5-10, 10-20, 20-30, 30-60, 60-100, 100-200, >200), `jumlah_unit`)
- **TOTAL 2024**: Tangkap 19.799 ton, Budidaya 314 ton, Total 20.113 ton
- **STATUS**: Ada (`4.45`, `4.46`, `4.47`, `4.48`)

---

### BAB 5: PERHUBUNGAN & INFRASTRUKTUR (15 Tabel)

#### Tabel 5.49 - Prasarana Transportasi Laut (DISHUB)
- **Field**: `jenis_prasarana` (Pelayanan Peti Kemas, Pelabuhan Nusantara, Pelabuhan Perintis, Tambatan/Dermaga, Mercu, Rambu Suar), `jumlah_unit`, `lokasi`, `status_operasional`
- **TOTAL 2024**: 1 Peti Kemas, 1 Pelabuhan Nusantara, 11 Pelabuhan Perintis, 11 Dermaga, 3 Mercu, 8 Rambu Suar
- **STATUS**: Ada (`5.49`, blueprint detail)

#### Tabel 5.50 - Kendaraan Bermotor (DISHUB)
- **Field**: `jenis_kendaraan` (Bus, Pick Up, Truck, Truck Tangki, Sepeda Motor), `satuan`, `jumlah`
- **TOTAL 2024**: 18.324 unit (Sepeda Motor 17.539, Bus 360, Pick Up 275, Truck 140, Truck Tangki 10)
- **STATUS**: Ada (`5.50`)

#### Tabel 5.51 - Kecelakaan Lalu Lintas (DISHUB)
- **Field**: `jenis_kejadian` (Kecelakaan LL, Kecelakaan Tunggal, Laka JOL, Tabrak Lari), `jumlah`
- **TOTAL 2024**: 348 kejadian
- **STATUS**: Ada (`5.51`)

#### Tabel 5.52 - Prasarana & Fasilitas Perlengkapan Jalan (DISHUB)
- **Field**: `jenis_prasarana` (Rambu Lalu Lintas, RPPJ, Cermin Tikungan, Marka Jalan, APILL, Warning Light, Guardrail, Halte, Lampu PJU), `satuan`, `jumlah`
- **TOTAL**: 278 rambu, 200m guardrail, 74 lampu PJU, 4 halte
- **STATUS**: Ada (`5.52`)

#### Tabel 5.53 - Trayek Kendaraan Umum (DISHUB)
- **Field**: `nama_trayek` (8 trayek dalam kota), `jumlah_kendaraan`
- **TOTAL**: 193 unit kendaraan umum
- **STATUS**: Ada (`5.53`)

#### Tabel 5.54 - Terminal (DISHUB)
- **Field**: `nama_terminal`, `lokasi`, `jumlah`, `kondisi`
- **TOTAL**: 4 terminal tipe C
- **STATUS**: Ada (`5.54`)

#### Tabel 5.55 - Halte (DISHUB)
- **Field**: `nama_halte`, `lokasi`, `kondisi`
- **STATUS**: Ada (`5.55`)

#### Tabel 5.56-5.58 - PUPR (DPUPR)
- **Tabel 5.56**: `jenis_dokumen` (Rencana Teknis, RTBL), `jumlah`
- **Tabel 5.57**: `jaringan_irigasi` (Teknis, Setengah Teknis, Sederhana), `panjang_km`, `jumlah`
- **Tabel 5.58**: `jenis_sarana_air_minum` (PDAM, Sumur Bor, Sumur Gali, Mata Air, Penampungan Hujan, Tangki), `jumlah_pemakai`, `kondisi`
- **STATUS**: Ada (`5.56`, `5.57`, `5.58`)

#### Tabel 5.59 - Kondisi Jalan & Jembatan (DPUPR)
- **Field**: `uraian` (Panjang Jalan Kabupaten, Konstruksi Permukaan per jenis, Kondisi per kategori, Jalan Provinsi, Jalan Nasional, Jumlah Jembatan, Panjang Jembatan), `nilai`
- **TOTAL**: 379,97 km jalan kabupaten (190,81 km kondisi baik), 170 unit jembatan (1.714 m)
- **STATUS**: Ada (`5.59`)

#### Tabel 5.60 - Perpustakaan (PERPUS)
- **Field**: `jenis` (Daerah, Desa, Kelurahan, Sekolah), `jumlah`
- **TOTAL**: 1 daerah, 9 desa, 9 kelurahan, 229 sekolah
- **STATUS**: Ada (`5.60`)

#### Tabel 5.61-5.63 - PERKIM (PERKIM)
- **Tabel 5.61**: `uraian` (Permukiman Kumuh %, Rumah Layak Huni, Rasio RLH, RTLH, Rasio RTLH), `nilai`
- **Tabel 5.62**: `nama_tpu`, `lokasi`, `luas_total`, `luas_makam`, `luas_bangunan`
- **Tabel 5.63**: `kecamatan`, `panjang_jalan_lingkungan`, `konstruksi_beton`, `konstruksi_tanah`, `panjang_drainase`, `drainase_beton`, `drainase_tanah`
- **STATUS**: Ada (`5.61`, `5.62`, `5.63`)


---

### BAB 6: PERDAGANGAN, INDUSTRI, KOPERASI & MODAL (9 Tabel)

#### Tabel 6.64 - Investasi (DPMPTSP)
- **Field**: `jenis_usaha` (Usaha Mikro, Kecil, Menengah, Besar), `jumlah_usaha`, `nilai_investasi_rp`
- **TOTAL 2024**: 719 usaha, Rp 3.007.998.896.564
- **STATUS**: Ada (`6.64`)

#### Tabel 6.65 - Jenis Izin Diterbitkan (DPMPTSP)
- **Field**: `jenis_izin` (30 jenis: SITU, SIUP, TDG, TDG, IOT, dll), `jumlah_pemohon`
- **TOTAL 2024**: 2.178 izin diterbitkan
- **STATUS**: Ada (`6.65`)

#### Tabel 6.66-6.67 - Koperasi (KOPERASI)
- **Tabel 6.66**: `uraian` (Lembaga Koperasi, Koperasi Aktif, UMKM), `jumlah`
- **Tabel 6.67**: `uraian` (Koperasi Bersertifikat NIK), `jumlah`
- **TOTAL 2024**: 172 Koperasi, 111 Aktif, 11.736 UMKM
- **STATUS**: Ada (`6.66`, `6.67`)

#### Tabel 6.68 - Industri Kecil Menengah (DISPERINDAG)
- **Field**: `jenis_industri` (Mikro, Kecil, Menengah, Besar), `jumlah_unit_usaha`, `jumlah_tenaga_kerja`, `nilai_investasi`
- **STATUS**: Ada (`6.68`)

#### Tabel 6.69 - Harga Barang Pokok (DISPERINDAG)
- **Field**: `tanggal`, `pasar`, `komoditas`, `harga_rp_per_kg`
- **PERIODE**: Harian/periodik
- **STATUS**: Ada (`6.69`) — butuh entry mingguan/bulanan

#### Tabel 6.70 - UTTP Ditera Ulang (DISPERINDAG)
- **Field**: `jenis_uttp` (Timbangan, Neraca, Meter, dll), `jumlah_ditera`
- **STATUS**: Ada (`6.70`)

#### Tabel 6.71 - Pasar Tradisional (DISPERINDAG)
- **Field**: `kecamatan`, `jumlah_pasar_tradisional`, `jumlah_bangunan_pasar`
- **TOTAL 2024**: 29 pasar, 31 bangunan
- **STATUS**: Ada (`6.71`)

#### Tabel 6.72 - Pedagang & Sarana Pedagang (DISPERINDAG)
- **Field**: `kecamatan`, `jumlah_ruko`, `jumlah_kios`, `jumlah_los`, `jumlah_tenda`, `jumlah_hamparan`, `jumlah_meja`
- **TOTAL 2024**: 2.554 pedagang
- **STATUS**: Ada (`6.72`)

---

### BAB 7: KEUANGAN, PENGAWASAN & PERENCANAAN (7 Tabel)

#### Tabel 7.73 - Penyerapan Anggaran Perangkat Daerah (BPKPD)
- **Field**: `perangkat_daerah` (~33 OPD), `pagu_anggaran_rp`, `realisasi_anggaran_rp`, `persen_realisasi`
- **TOTAL 2024**: 93,86% dari Rp 1.543.388.743.796
- **STATUS**: Ada (`7.73`)

#### Tabel 7.74 - Penyerapan Anggaran Kecamatan (BPKPD)
- **Field**: `kecamatan`, `pagu_anggaran`, `realisasi`, `persen`
- **STATUS**: Ada (`7.74`)

#### Tabel 7.75 - Laporan Realisasi Anggaran (BPKPD)
- **Field**: `uraian` (Pendapatan: PAD, Transfer; Belanja: Operasi, Modal, Tak Terduga, Transfer; Surplus/Defisit; Pembiayaan), `pagu_anggaran`, `realisasi_anggaran`, `persen`
- **STATUS**: Ada (`7.75`)

#### Tabel 7.76 - Realisasi Pajak & Retribusi (BPKPD)
- **Field**: `jenis_pad` (Pajak Daerah, Retribusi, Hasil Kekayaan, dll), `anggaran`, `realisasi`, `persen`
- **STATUS**: Ada (`7.76`)

#### Tabel 7.77 - Realisasi Kegiatan Inspektorat (INSPEKTORAT)
- **Field**: `uraian_kegiatan`, `target`, `realisasi`, `kendala`
- **STATUS**: Ada (`7.77`)

#### Tabel 7.78 - Capaian Kinerja Bappelitbang (BAPPELITBANG)
- **Field**: `indikator`, `target`, `realisasi`, `persen`
- **STATUS**: Ada (`7.78`)

#### Tabel 7.79 - Capaian Kinerja Kesbangpol (KESBANGPOL)
- **Field**: `indikator`, `target`, `realisasi`, `persen`
- **STATUS**: Ada (`7.79`)


---

### BAB 8: BENCANA, SATPOL PP & LINGKUNGAN (8 Tabel + 1 Grafik)

#### Tabel 8.80 - Kawasan Rawan Bencana (BPBD)
- **Field**: `jenis_bencana`, `lokasi`, `kategori_risiko` (Rendah, Sedang, Tinggi), `jumlah_lokasi`
- **STATUS**: Ada (`8.80`)

#### Tabel 8.81 - Penanganan Korban Bencana (BPBD)
- **Field**: `tanggal_kejadian`, `jenis_bencana`, `lokasi`, `korban_meninggal`, `korban_luka`, `mengungsi`, `kerugian_rp`
- **STATUS**: Ada (`8.81`)

#### Tabel 8.82 - SDM Satpol PP & Damkar (SATPOLPP)
- **Field**: `jabatan` (Kasatpol PP, Kabid, Kasi, Staf, Petugas Damkar), `jumlah_personil`
- **STATUS**: Ada (`8.82`)

#### Tabel 8.83 - Penegakan Perda (SATPOLPP)
- **Field**: `jenis_pelanggaran`, `jumlah_kasus`
- **STATUS**: Ada (`8.83`)

#### Tabel 8.84 - Pelayanan Kebakaran (SATPOLPP)
- **Field**: `jenis_layanan`, `jumlah_kejadian`, `jumlah_penanganan`
- **STATUS**: Ada (`8.84`)

#### Tabel 8.85 - Pengelolaan SDA & LH (DLH)
- **Field**: `indikator` (NSPM Persampahan, NSPM LH SDA, NSPM RTH, Penanggulangan Pencemaran, Penanganan Sampah), `nilai_persen`
- **STATUS**: Ada (`8.85`)

#### Tabel 8.86 - Pengelolaan Persampahan (DLH)
- **Field**: `uraian` (Unit pengelola, TPS pertama, Pemilahan/pengolahan, TPS, TPA), `jumlah`
- **TOTAL 2024**: 3 unit pengelola, 5 TPS, 1 TPA
- **STATUS**: Ada (`8.86`)

#### Tabel 8.87 - Ruang Terbuka Hijau (DLH)
- **Field**: `jenis_rth` (Hutan Kota: Rawa Arena, Rawa Towo, Tanjung Tahuna; Taman Kota Tahuna), `lokasi`
- **STATUS**: Ada (`8.87`)

---

### BAB 9: TELEKOMUNIKASI & PARIWISATA (6 Tabel)

#### Tabel 9.88 - Indeks SPBE (DISKOMINFO)
- **Field**: `tingkat_instansi` (Nasional, Provinsi, Kabupaten), `indeks_spbe`, `predikat`
- **TOTAL 2024**: Nasional 3,12 (Baik), Provinsi 3,01 (Baik), Kabupaten 2,46 (Cukup)
- **STATUS**: Ada (`9.88`)

#### Tabel 9.89 - Website & Berita (DISKOMINFO)
- **Field**: `bulan`, `jumlah_pengunjung_website`, `jumlah_berita_publish`
- **STATUS**: Ada (`9.89`)

#### Tabel 9.90 - Pelayanan Informasi Publik (DISKOMINFO)
- **Field**: `bulan`, `jumlah_permohonan_informasi`, `jumlah_diterima`, `jumlah_ditolak`
- **STATUS**: Ada (`9.90`)

#### Tabel 9.91 - Menara Telekomunikasi (DISKOMINFO)
- **Field**: `kecamatan`, `kode_menara`, `provider`, `lokasi`, `tinggi_m`, `status_izin`
- **TOTAL 2024**: 37 menara (operator utama: Telkomsel)
- **STATUS**: Ada (`9.91`)

#### Tabel 9.92 - Kunjungan Wisatawan (PARIWISATA)
- **Field**: `bulan`, `destinasi_id`, `wisman`, `wisnu`
- **TOTAL 2024**: 73 Wisman, 32.000 Wisnu, 32.073 Total
- **STATUS**: Ada (`9.92`)

#### Tabel 9.93 - Objek & Daya Tarik Wisata (PARIWISATA)
- **Field**: `jenis_wisata` (Alam, Budaya, Buatan), `kecamatan`, `lokasi`, `nama_objek`
- **TOTAL**: 30+ objek wisata
- **STATUS**: Ada (`9.93`)


---

## 3. SUMMARY: GAP ANALISIS SISTEM vs KEBUTUHAN BUKU SEKTORAL

### 3.1 Status Keseluruhan
| Komponen | Total | Sudah Ada | Perlu Diisi | Belum Ada |
|----------|-------|-----------|-------------|----------|
| Tabel di Buku Sektoral | 93 | 90 (97%) | 0 | 3 (3%) |
| Dataset di sistem | 178 | 178 | 178 (perlu data) | 0 |
| Grafik (20 di PDF) | 20 | - | - | - |
| OPD Produsen | 32 | 32 (39 terdaftar, 7 duplikat) | - | 0 |

**Interpretasi**: 
- Semua struktur dataset sudah ada di sistem (90 dari format Buku Sektoral + 88 dari blueprint)
- **TIDAK ADA struktur yang perlu ditambah** - semua tabel 2.1 sampai 9.93 sudah ter-mapping
- Yang harus dilakukan adalah **PENGISIAN DATA** oleh masing-masing OPD

### 3.2 Tabel yang Belum Ter-map (3 tabel)
Tabel-tabel ini tidak ditemukan eksplisit di blueprint namun biasanya di-handle oleh OPD pemilik:
- **Tabel 3.23 (Data Panti)** - DINSOS - biasanya di-handle oleh DATASET `DATA_PMKS` dengan filter jenis=panti
- **Tabel 4.41 (Ekonomi Pertanian)** - DISTAN - mungkin ter-handle oleh `IKU_DAERAH` Bappelitbang
- **Tabel 6.70 (UTTP)** - DISPERINDAG - perlu dibuat dataset baru

### 3.3 Rekomendasi: Field Tambahan yang Perlu Ditambah

Meskipun struktur sudah ada, ada beberapa **field tambahan** yang sebaiknya ditambahkan untuk memenuhi kebutuhan infografis spesifik:

#### A. Tabel 3.12 - KK per Kecamatan
Saat ini field: `kecamatan_id`, `kk_laki`, `kk_perempuan`, `jumlah` ✓ LENGKAP

#### B. Tabel 3.20 - e-PPGBM Stunting
**MASIH KURANG** - Saat ini generic. Perlu:
- `nama_puskesmas` (string)
- `sasaran_balita` (integer)
- `entry_pengukuran` (integer)
- `persen_pengukuran` (decimal)
- `jumlah_stunting` (integer)
- `persen_stunting` (decimal)

#### C. Tabel 3.21 - AKI/AKB/AHH
Saat ini hanya 3 field generic. Perlu direstrukturisasi:
- `indikator` (enum: AKI, AKB, AHH)
- `nilai` (decimal)
- `satuan` (string)
- `definisi` (text - untuk tooltip)

#### D. Tabel 4.47 - Produksi Tangkap per Jenis Ikan
**PENTING** - Saat ini generic. Perlu field per metrik:
- `jenis_ikan` (string)
- `volume_kg` (decimal)
- `harga_rata_rp_kg` (decimal)
- `nilai_rp` (decimal)

#### E. Tabel 5.59 - Jalan & Jembatan
**PENTING** - Perlu direstrukturisasi dengan field:
- `uraian` (master)
- `nilai` (decimal)
- `satuan` (string)
- `kategori` (enum: Kabupaten, Provinsi, Nasional, Konstruksi, Kondisi)
- Tabel hirarkis (parent_id) untuk Aspal/Beton/Tanah

#### F. Tabel 6.72 - Pedagang & Sarana
**PENTING** - Perlu field per jenis sarana:
- `kecamatan_id`
- `ruko`, `kios`, `los`, `tenda`, `hamparan`, `meja` (6 integer)
- `total` (auto-calculated)

#### G. Tabel 7.75 - Realisasi Anggaran
**PENTING** - Master-detail struktur:
- `kategori` (Pendapatan/Belanja/Pembiayaan)
- `subkategori` (PAD/Transfer; Operasi/Modal; dll)
- `item` (Pajak/Retribusi/DBH/DAU; Pegawai/Barang; dll)
- `pagu`, `realisasi`, `persen`

---

## 4. ROADMAP PENGISIAN DATA

### FASE 1: Data Master (1-2 minggu)
1. Tabel 2.1-2.7 (Pemerintahan) - 33 baris total
2. Tabel 2.4 (Wilayah) - sudah ter-handle oleh tabel wilayahs
3. Tabel 5.49, 5.52, 5.59 (Infrastruktur) - 30+ baris
4. Tabel 8.80, 8.82, 9.91 (Master bencana/Satpol/menara) - 50+ baris
5. Tabel 9.93 (Objek Wisata) - 30+ baris

### FASE 2: Data Tahunan - Kritis (2-4 minggu)
1. Tabel 3.12-3.17 (Penduduk, Pendidikan) - 60+ baris
2. Tabel 3.20-3.22 (Kesehatan, Kesra) - 30+ baris
3. Tabel 4.37-4.44 (Pangan, Pertanian) - 50+ baris
4. Tabel 4.45-4.48 (Perikanan) - 100+ baris
5. Tabel 5.50, 5.51, 5.53, 5.54, 5.55, 5.57, 5.58, 5.60-5.63 (Transportasi, Infrastruktur) - 100+ baris
6. Tabel 6.64-6.72 (Perdagangan, Koperasi) - 50+ baris
7. Tabel 7.73-7.79 (Keuangan, Perencanaan) - 100+ baris
8. Tabel 8.81, 8.83-8.87 (Bencana, LH) - 30+ baris
9. Tabel 9.88, 9.89, 9.90, 9.92 (Kominfo, Wisata) - 30+ baris

### FASE 3: Data Periodik (Ongoing)
1. Tabel 6.69 (Harga Pangan Pokok) - mingguan/bulanan
2. Tabel 3.35-3.36 (TPT & TPAK) - semester
3. Tabel 9.89-9.90 (Website, Pelayanan Informasi) - bulanan
4. Tabel 9.92 (Wisatawan) - bulanan

### FASE 4: Validasi dan Publikasi
1. Setiap OPD submit data -> Walidata verifikasi
2. Data published masuk ke dashboard publik
3. Generate Buku Sektoral 2025 otomatis

---

## 5. VISUALISASI YANG BISA DIHASILKAN

Dengan data di atas, sistem dapat menghasilkan:

### 5.1 Dashboard Eksekutif
- **Stat Cards**: Total Penduduk (135.860), KK (51.234), AHH (74,7), Stunting (2,05%), dll
- **Map**: Peta 15 kecamatan + warna intensitas indikator
- **Trend Lines**: Pertumbuhan penduduk 5 tahun terakhir
- **Bar Charts**: Perbandingan antar kecamatan

### 5.2 Dashboard per OPD
- **DINKES**: Tren stunting per puskesmas, AKI/AKB/AHH trend, Nakes per faskes
- **DISDIK**: APM/APS per jenjang, Rasio guru:murid, Sebaran sekolah
- **DUKCAPIL**: Piramida penduduk, Kepadatan per kecamatan, Sebaran pendidikan
- **PUPR**: Peta jalan, Kondisi jalan, Sebaran jembatan
- **PERIKANAN**: Komposisi jenis ikan, Produksi per bulan, Armada kapal
- **BPKPD**: Penyerapan anggaran per OPD, Komposisi pendapatan, Realisasi pajak
- **PARIWISATA**: Kunjungan Wisman vs Wisnu, Destinasi terpopuler
- **DISHUB**: Sebaran kecelakaan, Pertumbuhan kendaraan, Prasarana pelayaran

### 5.3 Infografis Cetak (untuk Buku Sektoral 2025)
- Halaman Cover dengan foto + nama kabupaten
- Peta tematik per kecamatan
- Grafik batang horizontal perbandingan
- Tabel dengan format publikasi resmi
- Grafik lingkaran (pie) untuk komposisi


### 5.4 Infografis Interaktif (Web Dashboard)
- Filter interaktif per kecamatan, tahun, jenis indikator
- Tooltip dengan definisi 5 Elemen SDI
- Link download Excel/CSV per infografis
- Bookmark/print per infografis
- Sharing social media

---

## 6. RINGKASAN TEMUAN KRITIS

1. **Sistem sudah STRUCTURALLY READY** - 90 tabel Buku Sektoral + 88 blueprint detail
2. **TIDAK PERLU menambah struktur baru** - semua sudah ter-mapping
3. **Yang diperlukan adalah PENGISIAN DATA** oleh 32 OPD
4. **TIMELINE realistic**: 4-8 minggu untuk pengumpulan data satu tahun penuh
5. **PRIORITAS tertinggi**: Tabel demografi (3.12-3.17) -> indikator utama untuk RPJMD
6. **STRUKTUR INFLASI**: Tabel 4.47 (35 jenis ikan), 4.42-4.44 (pertanian), 6.72 (pedagang) perlu field tambahan

### Rekomendasi Action Items

1. **MINGGU 1**: Workshop dengan seluruh OPD untuk:
   - Sosialisasi sistem input data
   - Pembagian user akun per OPD
   - Demonstrasi cara input data

2. **MINGGU 2-4**: Pengisian data parallel per kelompok:
   - **Kelompok A** (data penduduk & pendidikan): Dukcapil, Disdik, Dinkes
   - **Kelompok B** (data ekonomi): BPKPD, Bappelitbang, Pertanian, Perkebunan, Perikanan
   - **Kelompok C** (infrastruktur): PUPR, Dishub, Perkim, DLH
   - **Kelompok D** (pelayanan): Dinkes, Dinsos, DPPKB, DP3A, Pariwisata

3. **MINGGU 5-6**: Verifikasi oleh Walidata
4. **MINGGU 7-8**: Generate Buku Sektoral 2025 otomatis
5. **MINGGU 8+**: Launch portal publik untuk transparansi

---

## 7. KESIMPULAN UNTUK IMPLEMENTASI

### Status: STRUKTUR 100% READY, DATA 0% TERISI

| Aspek | Kebutuhan Buku Sektoral | Status Sistem | Gap |
|-------|------------------------|----------------|-----|
| Struktur Tabel | 93 tabel | 90 tabel + 88 detail blueprint | ✅ |
| Definisi Field | ~400 field | 866 field (dengan overlap) | ✅ (overachieved) |
| Validasi | SDI compliance | 5 Elemen built-in | ✅ |
| Workflow | Verifikasi publikasi | Draft -> Submit -> Verify -> Publish | ✅ |
| Hak Akses | Per-OPD | Spatie role with OPD filter | ✅ |
| Visualisasi | Infografis generatable | Static page, belum dashboard interaktif | ⚠️ |
| Pengisian Data | 1 tahun | 27 nilai (sampel) | ❌ 99% missing |
| Periodic Update | Bulanan/semester/tahunan | Per period_type dataset | ✅ sistem siap |

### 7 Tabel Tambahan yang Memerlukan Field Baru

1. **3.20 e-PPGBM Stunting**: 5 field tambahan
2. **3.21 AKI/AKB/AHH**: Restrukturisasi jadi 5 field
3. **4.47 Produksi Tangkap per Jenis**: 4 field spesifik
4. **4.48 Armada Kapal per GT**: 2 field
5. **5.59 Jalan & Jembatan**: Hirarkis 5 field
6. **6.72 Pedagang & Sarana**: 6 field per jenis sarana
7. **7.75 Realisasi Anggaran**: Master-detail 7 field

**Total**: ~30 field tambahan untuk 7 tabel spesifik. Sisanya sudah ter-cover oleh blueprint yang sudah di-restore.

---

*Dokumen ini menjadi acuan untuk tim data dan operator OPD dalam pengisian data statistik sektoral tahun 2025 dan seterusnya.*

*Versi: 1.0*
*Tanggal: 18 Oktober 2026*
*Disusun oleh: Tim Data SIPANDU*
