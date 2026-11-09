<?php

// Part 2: Pemerintahan & Admin (8 OPD)

return [
    'BPKPD' => [
        'datasets' => [
            'REALISASI_PAJAK' => [
                'nama' => 'Realisasi Pajak Daerah',
                'deskripsi' => 'Realisasi PAD dari pajak per bulan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'jenis_pajak' => ['label' => 'Jenis Pajak', 'tipe' => 'enum', 'options' => ['PBB','BPHTB','Pajak Hotel','Pajak Restoran','Pajak Reklame','Pajak Air Tanah','Pajak Parkir','Pajak PJU','Pajak Hiburan','Pajak Mineral']],
                    'target_rp' => ['label' => 'Target (Rp)', 'tipe' => 'decimal'],
                    'realisasi_rp' => ['label' => 'Realisasi (Rp)', 'tipe' => 'decimal'],
                    'persen' => ['label' => '% Pencapaian', 'tipe' => 'decimal'],
                ],
            ],
            'DATA_WAJIB_PAJAK' => [
                'nama' => 'Data Wajib Pajak',
                'deskripsi' => 'Master wajib pajak daerah',
                'period_type' => 'master',
                'lisensi' => 'terbatas',
                'fields' => [
                    'npwpd' => ['label' => 'NPWPD', 'tipe' => 'string', 'required' => true],
                    'nama_wp' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'alamat' => ['label' => 'Alamat', 'tipe' => 'text'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'jenis_pajak' => ['label' => 'Jenis Pajak', 'tipe' => 'string'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Nonaktif']],
                ],
            ],
            'PIUTANG_PAJAK' => [
                'nama' => 'Piutang Pajak',
                'deskripsi' => 'Piutang pajak yang belum tertagih',
                'period_type' => 'master',
                'fields' => [
                    'npwpd' => ['label' => 'NPWPD', 'tipe' => 'string'],
                    'nama_wp' => ['label' => 'Nama WP', 'tipe' => 'string'],
                    'tahun_pajak' => ['label' => 'Tahun Pajak', 'tipe' => 'integer'],
                    'jenis_pajak' => ['label' => 'Jenis Pajak', 'tipe' => 'string'],
                    'nilai_piutang' => ['label' => 'Nilai Piutang', 'tipe' => 'decimal'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Baru','Setengah','Macet']],
                ],
            ],
        ],
    ],

    'DUKCAPIL' => [
        'datasets' => [
            'KINERJA_LAYANAN_DUKCAPIL' => [
                'nama' => 'Kinerja Layanan Dukcapil',
                'deskripsi' => 'Rekap layanan KTP, KK, KIA, Akta per bulan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'ktp_terbit' => ['label' => 'KTP Terbit', 'tipe' => 'integer'],
                    'kk_terbit' => ['label' => 'KK Terbit', 'tipe' => 'integer'],
                    'kia_terbit' => ['label' => 'KIA Terbit', 'tipe' => 'integer'],
                    'akta_lahir_terbit' => ['label' => 'Akta Lahir Terbit', 'tipe' => 'integer'],
                    'akta_nikah_terbit' => ['label' => 'Akta Nikah Terbit', 'tipe' => 'integer'],
                    'pindah_penduduk' => ['label' => 'Pindah Penduduk', 'tipe' => 'integer'],
                    'datang_penduduk' => ['label' => 'Datang Penduduk', 'tipe' => 'integer'],
                ],
            ],
            'PROFIL_PENDUDUK' => [
                'nama' => 'Profil Penduduk Agregat',
                'deskripsi' => 'Statistik demografi per kecamatan per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'total_penduduk' => ['label' => 'Total Penduduk', 'tipe' => 'integer'],
                    'laki_laki' => ['label' => 'Laki-laki', 'tipe' => 'integer'],
                    'perempuan' => ['label' => 'Perempuan', 'tipe' => 'integer'],
                    'usia_0_14' => ['label' => 'Usia 0-14', 'tipe' => 'integer'],
                    'usia_15_64' => ['label' => 'Usia 15-64', 'tipe' => 'integer'],
                    'usia_65_plus' => ['label' => 'Usia 65+', 'tipe' => 'integer'],
                    'kepala_keluarga' => ['label' => 'Jumlah KK', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'BAPPELITBANG' => [
        'datasets' => [
            'IKU_DAERAH' => [
                'nama' => 'Indikator Kinerja Utama (IKU) Daerah',
                'deskripsi' => 'IKU Kabupaten Kepulauan Sangihe per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'indikator' => ['label' => 'Indikator', 'tipe' => 'string', 'required' => true],
                    'target' => ['label' => 'Target', 'tipe' => 'string'],
                    'realisasi' => ['label' => 'Realisasi', 'tipe' => 'string'],
                    'satuan' => ['label' => 'Satuan', 'tipe' => 'string'],
                    'kategori' => ['label' => 'Kategori', 'tipe' => 'enum', 'options' => ['Ekonomi','Pendidikan','Kesehatan','Infrastruktur','Lingkungan','Sosial']],
                ],
            ],
            'DATA_STATISTIK_SEKTORAL' => [
                'nama' => 'Data Statistik Sektoral',
                'deskripsi' => 'Agregasi statistik lintas sektor (BPS Daerah)',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string'],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'indikator' => ['label' => 'Indikator', 'tipe' => 'string'],
                    'nilai' => ['label' => 'Nilai', 'tipe' => 'decimal'],
                    'satuan' => ['label' => 'Satuan', 'tipe' => 'string'],
                ],
            ],
        ],
    ],

    'DISTAN' => [
        'datasets' => [
            'LTT_TANAMAN_PANGAN' => [
                'nama' => 'Luas Tambah Tanam (LTT) Tanaman Pangan',
                'deskripsi' => 'Luas tambah tanam padi/jagung/kedelai per kecamatan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'komoditas' => ['label' => 'Komoditas', 'tipe' => 'enum', 'options' => ['Padi','Jagung','Kedelai','Kacang Tanah','Ubi Kayu','Ubi Jalar']],
                    'luas_tanam_ha' => ['label' => 'Luas Tanam (ha)', 'tipe' => 'decimal'],
                    'luas_panen_ha' => ['label' => 'Luas Panen (ha)', 'tipe' => 'decimal'],
                    'produksi_ton' => ['label' => 'Produksi (ton)', 'tipe' => 'decimal'],
                    'produktivitas_ku_ha' => ['label' => 'Produktivitas (ku/ha)', 'tipe' => 'decimal'],
                ],
            ],
            'DATA_PETANI' => [
                'nama' => 'Data Petani',
                'deskripsi' => 'Master petani (ref:penduduk)',
                'period_type' => 'master',
                'lisensi' => 'terbatas',
                'fields' => [
                    'nik' => ['label' => 'NIK', 'tipe' => 'reference', 'reference_target' => 'penduduks', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'kelompok_tani' => ['label' => 'Kelompok Tani', 'tipe' => 'string'],
                    'komoditas_utama' => ['label' => 'Komoditas Utama', 'tipe' => 'string'],
                    'luas_lahan_ha' => ['label' => 'Luas Lahan (ha)', 'tipe' => 'decimal'],
                    'status_lahan' => ['label' => 'Status Lahan', 'tipe' => 'enum', 'options' => ['Milik Sendiri','Sewa','Garap','Bagi Hasil']],
                ],
            ],
            'POPULASI_TERNAK' => [
                'nama' => 'Populasi Ternak',
                'deskripsi' => 'Jumlah ternak per kecamatan per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'jenis_ternak' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Sapi Potong','Sapi Perah','Babi','Kambing','Ayam Kampung','Ayam Ras','Itik']],
                    'jumlah' => ['label' => 'Jumlah', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'DISPERINDAG' => [
        'datasets' => [
            'DATA_INDUSTRI' => [
                'nama' => 'Data Industri',
                'deskripsi' => 'Master industri kecil, menengah, besar',
                'period_type' => 'master',
                'fields' => [
                    'nama_perusahaan' => ['label' => 'Nama Perusahaan', 'tipe' => 'string', 'required' => true],
                    'nib' => ['label' => 'NIB', 'tipe' => 'string'],
                    'kbli' => ['label' => 'Kode KBLI', 'tipe' => 'string', 'keterangan' => 'rujuk ke kode_referensis'],
                    'skala_usaha' => ['label' => 'Skala', 'tipe' => 'enum', 'options' => ['Mikro','Kecil','Menengah','Besar']],
                    'alamat' => ['label' => 'Alamat', 'tipe' => 'text'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'jumlah_tenaga_kerja' => ['label' => 'Tenaga Kerja', 'tipe' => 'integer'],
                ],
            ],
            'DATA_PERDAGANGAN' => [
                'nama' => 'Data Perdagangan',
                'deskripsi' => 'Volume & nilai perdagangan antar daerah',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string'],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer'],
                    'jenis_komoditas' => ['label' => 'Komoditas', 'tipe' => 'string'],
                    'volume' => ['label' => 'Volume', 'tipe' => 'decimal'],
                    'satuan' => ['label' => 'Satuan', 'tipe' => 'string'],
                    'nilai_rp' => ['label' => 'Nilai (Rp)', 'tipe' => 'decimal'],
                    'asal' => ['label' => 'Asal', 'tipe' => 'string'],
                    'tujuan' => ['label' => 'Tujuan', 'tipe' => 'string'],
                ],
            ],
            'DATA_PASAR' => [
                'nama' => 'Data Pasar Tradisional',
                'deskripsi' => 'Master pasar tradisional',
                'period_type' => 'master',
                'fields' => [
                    'nama_pasar' => ['label' => 'Nama Pasar', 'tipe' => 'string', 'required' => true],
                    'pasar_objek_id' => ['label' => 'Lokasi (Polygon)', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'jumlah_kios' => ['label' => 'Jumlah Kios', 'tipe' => 'integer'],
                    'jumlah_pedagang' => ['label' => 'Jumlah Pedagang', 'tipe' => 'integer'],
                    'kondisi' => ['label' => 'Kondisi Bangunan', 'tipe' => 'enum', 'options' => ['Baik','Sedang','Rusak']],
                ],
            ],
        ],
    ],

    'BKPSDM' => [
        'datasets' => [
            'DATA_ASN' => [
                'nama' => 'Master Data ASN',
                'deskripsi' => 'Daftar Pegawai ASN se-Kabupaten Sangihe',
                'period_type' => 'master',
                'lisensi' => 'terbatas',
                'fields' => [
                    'nik' => ['label' => 'NIK', 'tipe' => 'reference', 'reference_target' => 'penduduks', 'required' => true],
                    'nip' => ['label' => 'NIP', 'tipe' => 'string', 'required' => true],
                    'nama' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'opd_id' => ['label' => 'OPD', 'tipe' => 'reference', 'reference_target' => 'opds'],
                    'jabatan' => ['label' => 'Jabatan', 'tipe' => 'string'],
                    'golongan' => ['label' => 'Golongan', 'tipe' => 'string'],
                    'pangkat' => ['label' => 'Pangkat', 'tipe' => 'string'],
                    'status_asn' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['PNS','PPPK','CPNS']],
                    'jenis_kelamin' => ['label' => 'JK', 'tipe' => 'enum', 'options' => ['L','P']],
                    'pendidikan' => ['label' => 'Pendidikan', 'tipe' => 'string'],
                ],
            ],
            'FORMASI_JABATAN' => [
                'nama' => 'Formasi Jabatan',
                'deskripsi' => 'Formasi ASN per jabatan per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'opd_id' => ['label' => 'OPD', 'tipe' => 'reference', 'reference_target' => 'opds'],
                    'jabatan' => ['label' => 'Jabatan', 'tipe' => 'string', 'required' => true],
                    'formasi' => ['label' => 'Formasi', 'tipe' => 'integer'],
                    'terisi' => ['label' => 'Terisi', 'tipe' => 'integer'],
                    'sisa' => ['label' => 'Sisa', 'tipe' => 'integer'],
                ],
            ],
            'PELATIHAN_DIKLAT' => [
                'nama' => 'Pelatihan & Diklat',
                'deskripsi' => 'Histori pelatihan yang diikuti ASN',
                'period_type' => 'event',
                'fields' => [
                    'tanggal_mulai' => ['label' => 'Tgl Mulai', 'tipe' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tgl Selesai', 'tipe' => 'date'],
                    'nama_diklat' => ['label' => 'Nama Diklat', 'tipe' => 'string', 'required' => true],
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Diklat PIM','Diklat Teknis','Workshop','Sertifikasi','Bimtek']],
                    'peserta' => ['label' => 'Jumlah Peserta', 'tipe' => 'integer'],
                    'penyelenggara' => ['label' => 'Penyelenggara', 'tipe' => 'string'],
                ],
            ],
        ],
    ],

    'PARIWISATA' => [
        'datasets' => [
            'DESTINASI_WISATA' => [
                'nama' => 'Destinasi Wisata',
                'deskripsi' => 'Master destinasi wisata Sangihe',
                'period_type' => 'master',
                'fields' => [
                    'nama_destinasi' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'destinasi_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jenis_wisata' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Wisata Bahari','Wisata Alam','Wisata Budaya','Wisata Kuliner','Wisata Religi','Wisata Buatan']],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'tarif_masuk' => ['label' => 'Tarif Masuk (Rp)', 'tipe' => 'decimal'],
                    'jam_operasional' => ['label' => 'Jam Operasional', 'tipe' => 'string'],
                ],
            ],
            'KUNJUNGAN_WISATAWAN' => [
                'nama' => 'Kunjungan Wisatawan',
                'deskripsi' => 'Statistik kunjungan per destinasi per bulan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'destinasi_id' => ['label' => 'Destinasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'wisnus' => ['label' => 'Wisatawan Nusantara', 'tipe' => 'integer'],
                    'wisman' => ['label' => 'Wisatawan Mancanegara', 'tipe' => 'integer'],
                ],
            ],
            'DATA_HOTEL' => [
                'nama' => 'Hotel & Penginapan',
                'deskripsi' => 'Master akomodasi',
                'period_type' => 'master',
                'fields' => [
                    'nama_akomodasi' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'hotel_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Hotel Bintang 3','Hotel Bintang 2','Hotel Bintang 1','Penginapan','Homestay','Wisma']],
                    'jumlah_kamar' => ['label' => 'Jumlah Kamar', 'tipe' => 'integer'],
                    'tarif_rata' => ['label' => 'Tarif Rata-rata (Rp)', 'tipe' => 'decimal'],
                ],
            ],
        ],
    ],

    'DISKOMINFO' => [
        'datasets' => [
            'DATA_SPBE' => [
                'nama' => 'Data SPBE',
                'deskripsi' => 'Aplikasi, domain, infrastruktur TIK Pemda',
                'period_type' => 'master',
                'fields' => [
                    'nama_aplikasi' => ['label' => 'Nama Aplikasi', 'tipe' => 'string', 'required' => true],
                    'domain' => ['label' => 'Domain', 'tipe' => 'enum', 'options' => ['Website','Mobile App','Sistem Internal','API','Database']],
                    'pemilik' => ['label' => 'Pemilik (OPD)', 'tipe' => 'reference', 'reference_target' => 'opds'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Tidak Aktif','Pengembangan']],
                    'tahun_pembuatan' => ['label' => 'Tahun Buat', 'tipe' => 'integer'],
                ],
            ],
            'DATA_MENARA_TELEKOM' => [
                'nama' => 'Data Menara Telekomunikasi',
                'deskripsi' => 'Master menara BTS (point)',
                'period_type' => 'master',
                'fields' => [
                    'kode_menara' => ['label' => 'Kode Menara', 'tipe' => 'string', 'required' => true],
                    'menara_objek_id' => ['label' => 'Lokasi (Point)', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans', 'required' => true],
                    'provider' => ['label' => 'Provider', 'tipe' => 'string'],
                    'tinggi_m' => ['label' => 'Tinggi (m)', 'tipe' => 'integer'],
                    'status_izin' => ['label' => 'Status Izin', 'tipe' => 'enum', 'options' => ['Berizin','Belum','Akan Expired']],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                ],
            ],
        ],
    ],
];
