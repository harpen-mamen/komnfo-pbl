<?php

// Part 3: Sisa 16 OPD (Pemberdayaan, Sosial, Layanan)

return [
    'SATPOLPP' => [
        'datasets' => [
            'POS_PAMONG' => [
                'nama' => 'Pos Satpol PP & Damkar',
                'deskripsi' => 'Master pos jaga + armada',
                'period_type' => 'master',
                'fields' => [
                    'nama_pos' => ['label' => 'Nama Pos', 'tipe' => 'string', 'required' => true],
                    'pos_objek_id' => ['label' => 'Lokasi (Point)', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jumlah_personil' => ['label' => 'Jumlah Personil', 'tipe' => 'integer'],
                    'jumlah_armada' => ['label' => 'Jumlah Armada', 'tipe' => 'integer'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                ],
            ],
            'KEGIATAN_PATROLI' => [
                'nama' => 'Kegiatan Patroli',
                'deskripsi' => 'Patroli ketertiban & lalu lintas',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'jenis_patroli' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Trantibum','Lalin','Razia','Pengamanan']],
                    'lokasi' => ['label' => 'Lokasi', 'tipe' => 'text'],
                    'jumlah_personil' => ['label' => 'Jumlah Personil', 'tipe' => 'integer'],
                    'jumlah_kasus' => ['label' => 'Kasus Ditangani', 'tipe' => 'integer'],
                ],
            ],
            'KEBAKARAN' => [
                'nama' => 'Penanganan Kebakaran',
                'deskripsi' => 'Histori kejadian & penanganan',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'lokasi' => ['label' => 'Lokasi', 'tipe' => 'text', 'required' => true],
                    'jenis_objek' => ['label' => 'Objek Terbakar', 'tipe' => 'enum', 'options' => ['Rumah','Gedung','Kendaraan','Hutan/Lahan','Lainnya']],
                    'penyebab' => ['label' => 'Penyebab', 'tipe' => 'string'],
                    'kerugian_rp' => ['label' => 'Kerugian (Rp)', 'tipe' => 'decimal'],
                    'korban' => ['label' => 'Korban', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'DPMD' => [
        'datasets' => [
            'DATA_PEMERINTAHAN_DESA' => [
                'nama' => 'Data Pemerintahan Desa',
                'deskripsi' => 'Master desa + aparatur',
                'period_type' => 'master',
                'fields' => [
                    'desa_id' => ['label' => 'Desa', 'tipe' => 'reference', 'reference_target' => 'wilayahs', 'required' => true],
                    'jumlah_dusun' => ['label' => 'Jumlah Dusun', 'tipe' => 'integer'],
                    'jumlah_rt' => ['label' => 'Jumlah RT', 'tipe' => 'integer'],
                    'jumlah_rw' => ['label' => 'Jumlah RW', 'tipe' => 'integer'],
                    'kepala_desa' => ['label' => 'Nama Kades', 'tipe' => 'string'],
                    'periode_jabatan' => ['label' => 'Periode Jabatan', 'tipe' => 'string'],
                ],
            ],
            'DATA_BUMDES' => [
                'nama' => 'Data BUMDes',
                'deskripsi' => 'Badan Usaha Milik Desa',
                'period_type' => 'master',
                'fields' => [
                    'nama_bumdes' => ['label' => 'Nama BUMDes', 'tipe' => 'string', 'required' => true],
                    'desa_id' => ['label' => 'Desa', 'tipe' => 'reference', 'reference_target' => 'wilayahs', 'required' => true],
                    'jenis_usaha' => ['label' => 'Jenis Usaha', 'tipe' => 'string'],
                    'modal_awal' => ['label' => 'Modal Awal (Rp)', 'tipe' => 'decimal'],
                    'laba_tahun_ini' => ['label' => 'Laba Tahun Ini (Rp)', 'tipe' => 'decimal'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Tidak Aktif','Baru']],
                ],
            ],
            'PENGGALIAN_DANA_DESA' => [
                'nama' => 'Penggunaan Dana Desa (ADD/DD)',
                'deskripsi' => 'Alokasi & realisasi Dana Desa per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'desa_id' => ['label' => 'Desa', 'tipe' => 'reference', 'reference_target' => 'wilayahs', 'required' => true],
                    'jenis_dana' => ['label' => 'Jenis Dana', 'tipe' => 'enum', 'options' => ['ADD','DD','BKK','Bantuan Provinsi']],
                    'alokasi_rp' => ['label' => 'Alokasi (Rp)', 'tipe' => 'decimal'],
                    'realisasi_rp' => ['label' => 'Realisasi (Rp)', 'tipe' => 'decimal'],
                    'bidang' => ['label' => 'Bidang', 'tipe' => 'string'],
                ],
            ],
        ],
    ],

    'PERKIM' => [
        'datasets' => [
            'DATA_RUSUN' => [
                'nama' => 'Rumah Susun Sederhana',
                'deskripsi' => 'Master rusunawa',
                'period_type' => 'master',
                'fields' => [
                    'nama_rusun' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'rusun_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jumlah_unit' => ['label' => 'Jumlah Unit', 'tipe' => 'integer'],
                    'jumlah_terhuni' => ['label' => 'Terhuni', 'tipe' => 'integer'],
                    'tarif_sewa' => ['label' => 'Tarif Sewa (Rp)', 'tipe' => 'decimal'],
                ],
            ],
            'DATA_RTLH' => [
                'nama' => 'Rumah Tidak Layak Huni (RTLH)',
                'deskripsi' => 'Master RTLH & penerima bantuan',
                'period_type' => 'master',
                'fields' => [
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'desa_id' => ['label' => 'Desa', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'pemilik_nik' => ['label' => 'NIK Pemilik', 'tipe' => 'reference', 'reference_target' => 'penduduks'],
                    'kondisi_rumah' => ['label' => 'Kondisi', 'tipe' => 'enum', 'options' => ['RTLH','RLH']],
                    'tahun_verifikasi' => ['label' => 'Tahun Verifikasi', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'DPMPTSP' => [
        'datasets' => [
            'DATA_PERIZINAN' => [
                'nama' => 'Data Perizinan',
                'deskripsi' => 'Penerbitan perizinan per bulan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'jenis_izin' => ['label' => 'Jenis Izin', 'tipe' => 'string'],
                    'jumlah_diterbitkan' => ['label' => 'Diterbitkan', 'tipe' => 'integer'],
                    'jumlah_ditolak' => ['label' => 'Ditolak', 'tipe' => 'integer'],
                    'jumlah_direvisi' => ['label' => 'Direvisi', 'tipe' => 'integer'],
                ],
            ],
            'DATA_INVESTASI' => [
                'nama' => 'Data Investasi',
                'deskripsi' => 'Realisasi investasi PMA & PMDN',
                'period_type' => 'triwulanan',
                'fields' => [
                    'triwulan' => ['label' => 'Triwulan', 'tipe' => 'integer'],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer'],
                    'jenis_investasi' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['PMA','PMDN']],
                    'sektor' => ['label' => 'Sektor', 'tipe' => 'string'],
                    'nilai_rp' => ['label' => 'Nilai Investasi (Rp)', 'tipe' => 'decimal'],
                    'jumlah_proyek' => ['label' => 'Jumlah Proyek', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'KETPANGAN' => [
        'datasets' => [
            'KETERSEDIAAN_PANGAN' => [
                'nama' => 'Ketersediaan Pangan',
                'deskripsi' => 'Stok & Neraca Pangan Daerah',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'komoditas' => ['label' => 'Komoditas', 'tipe' => 'enum', 'options' => ['Beras','Jagung','Kedelai','Gula Pasir','Minyak Goreng','Daging Ayam','Daging Sapi','Telur','Bawang','Cabe']],
                    'ketersediaan_ton' => ['label' => 'Ketersediaan (ton)', 'tipe' => 'decimal'],
                    'kebutuhan_ton' => ['label' => 'Kebutuhan (ton)', 'tipe' => 'decimal'],
                    'neraca' => ['label' => 'Neraca', 'tipe' => 'decimal'],
                ],
            ],
            'HARGA_PANGAN' => [
                'nama' => 'Harga Pangan Pokok',
                'deskripsi' => 'Harga bahan pangan per pasar',
                'period_type' => 'harian',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'pasar_id' => ['label' => 'Pasar', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'komoditas' => ['label' => 'Komoditas', 'tipe' => 'string'],
                    'harga_rp' => ['label' => 'Harga (Rp/kg)', 'tipe' => 'decimal'],
                ],
            ],
        ],
    ],

    'DP3A' => [
        'datasets' => [
            'KASUS_KDRT' => [
                'nama' => 'Kasus KDRT',
                'deskripsi' => 'Kasus kekerasan dalam rumah tangga',
                'period_type' => 'event',
                'lisensi' => 'terbatas',
                'fields' => [
                    'tanggal_lapor' => ['label' => 'Tanggal Lapor', 'tipe' => 'date', 'required' => true],
                    'korban_nik' => ['label' => 'NIK Korban', 'tipe' => 'reference', 'reference_target' => 'penduduks', 'required' => true],
                    'pelaku_hubungan' => ['label' => 'Hubungan Pelaku', 'tipe' => 'enum', 'options' => ['Suami','Ayah','Anak','Famili','Lainnya']],
                    'jenis_kekerasan' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Fisik','Psikis','Seksual','Penelantaran','Eksploitasi']],
                    'status_penanganan' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Dilaporkan','Diproses','Selesai','Dirujuk']],
                ],
            ],
            'DATA_PUSAT_KRISIS' => [
                'nama' => 'Pusat Krisis Perempuan & Anak',
                'deskripsi' => 'Master pusat layanan',
                'period_type' => 'master',
                'fields' => [
                    'nama' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'jenis_layanan' => ['label' => 'Jenis Layanan', 'tipe' => 'string'],
                    'lokasi_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'kontak' => ['label' => 'Kontak', 'tipe' => 'string'],
                ],
            ],
        ],
    ],

    'DPPKB' => [
        'datasets' => [
            'PESERTA_KB' => [
                'nama' => 'Peserta KB Aktif',
                'deskripsi' => 'Rekap peserta KB aktif',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'metode_kb' => ['label' => 'Metode', 'tipe' => 'enum', 'options' => ['IUD','Suntik','Pil','Kondom','Implan','MOW','MOP']],
                    'jumlah_aktif' => ['label' => 'Aktif', 'tipe' => 'integer'],
                    'jumlah_baru' => ['label' => 'Baru', 'tipe' => 'integer'],
                ],
            ],
            'DATA_STUNTING' => [
                'nama' => 'Data Stunting',
                'deskripsi' => 'Kasus stunting per desa per tahun',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'desa_id' => ['label' => 'Desa', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'jumlah_anak_ukur' => ['label' => 'Jumlah Anak Diukur', 'tipe' => 'integer'],
                    'jumlah_stunting' => ['label' => 'Jumlah Stunting', 'tipe' => 'integer'],
                    'persentase' => ['label' => '%', 'tipe' => 'decimal'],
                ],
            ],
        ],
    ],

    'DISPORA' => [
        'datasets' => [
            'DATA_ATLET' => [
                'nama' => 'Data Atlet',
                'deskripsi' => 'Master atlet (ref:penduduk)',
                'period_type' => 'master',
                'lisensi' => 'terbatas',
                'fields' => [
                    'nik' => ['label' => 'NIK', 'tipe' => 'reference', 'reference_target' => 'penduduks', 'required' => true],
                    'nama' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'cabang_olahraga' => ['label' => 'Cabang', 'tipe' => 'string', 'required' => true],
                    'kategori' => ['label' => 'Kategori', 'tipe' => 'enum', 'options' => ['Pemula','Junior','Senior','Master']],
                    'klub' => ['label' => 'Klub', 'tipe' => 'string'],
                    'pelatih' => ['label' => 'Pelatih', 'tipe' => 'string'],
                ],
            ],
            'SARANA_OLAHRAGA' => [
                'nama' => 'Sarana Olahraga',
                'deskripsi' => 'Master sarana olahraga',
                'period_type' => 'master',
                'fields' => [
                    'nama_sarana' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'sarana_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Lapangan Sepak Bola','Lapangan Basket','Lapangan Tenis','GOR','Kolam Renang','Lintasan Lari']],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'kondisi' => ['label' => 'Kondisi', 'tipe' => 'enum', 'options' => ['Baik','Sedang','Rusak']],
                ],
            ],
            'PRESTASI_OLAHRAGA' => [
                'nama' => 'Prestasi Olahraga',
                'deskripsi' => 'Histori prestasi',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'nama_event' => ['label' => 'Event', 'tipe' => 'string', 'required' => true],
                    'tingkat' => ['label' => 'Tingkat', 'tipe' => 'enum', 'options' => ['Kabupaten','Provinsi','Nasional','Internasional']],
                    'cabang' => ['label' => 'Cabang', 'tipe' => 'string'],
                    'perolehan' => ['label' => 'Perolehan', 'tipe' => 'enum', 'options' => ['Emas','Perak','Perunggu','Partisipasi']],
                    'nama_atlet' => ['label' => 'Nama Atlet', 'tipe' => 'string'],
                ],
            ],
        ],
    ],

    'DISNAKER' => [
        'datasets' => [
            'DATA_PENCARI_KERJA' => [
                'nama' => 'Data Pencari Kerja',
                'deskripsi' => 'Master PAKAR (ref:penduduk)',
                'period_type' => 'master',
                'lisensi' => 'terbatas',
                'fields' => [
                    'nik' => ['label' => 'NIK', 'tipe' => 'reference', 'reference_target' => 'penduduks', 'required' => true],
                    'nama' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'jenis_kelamin' => ['label' => 'JK', 'tipe' => 'enum', 'options' => ['L','P']],
                    'pendidikan' => ['label' => 'Pendidikan', 'tipe' => 'string'],
                    'jurusan' => ['label' => 'Jurusan', 'tipe' => 'string'],
                    'tahun_lulus' => ['label' => 'Tahun Lulus', 'tipe' => 'integer'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Baru','Aktif','Ditempatkan','Mengundurkan Diri']],
                ],
            ],
            'LOWONGAN_KERJA' => [
                'nama' => 'Lowongan Kerja',
                'deskripsi' => 'Info lowongan dari perusahaan',
                'period_type' => 'event',
                'fields' => [
                    'tanggal_posting' => ['label' => 'Tanggal Posting', 'tipe' => 'date', 'required' => true],
                    'perusahaan' => ['label' => 'Perusahaan', 'tipe' => 'string', 'required' => true],
                    'posisi' => ['label' => 'Posisi', 'tipe' => 'string', 'required' => true],
                    'jumlah' => ['label' => 'Jumlah Lowongan', 'tipe' => 'integer'],
                    'kualifikasi' => ['label' => 'Kualifikasi', 'tipe' => 'text'],
                    'batas_akhir' => ['label' => 'Batas Lamaran', 'tipe' => 'date'],
                ],
            ],
        ],
    ],

    'PERPUS' => [
        'datasets' => [
            'DATA_PERPUSTAKAAN' => [
                'nama' => 'Data Perpustakaan',
                'deskripsi' => 'Master perpustakaan',
                'period_type' => 'master',
                'fields' => [
                    'nama' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'perpustakaan_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Umum','Sekolah','Khusus','Keliling']],
                    'jumlah_koleksi' => ['label' => 'Jumlah Koleksi', 'tipe' => 'integer'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                ],
            ],
            'KUNJUNGAN_PEMUSTAKA' => [
                'nama' => 'Kunjungan Pemustaka',
                'deskripsi' => 'Statistik pengunjung perpustakaan',
                'period_type' => 'bulanan',
                'fields' => [
                    'bulan' => ['label' => 'Bulan', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'perpustakaan_id' => ['label' => 'Perpustakaan', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jumlah_pengunjung' => ['label' => 'Pengunjung', 'tipe' => 'integer'],
                    'jumlah_peminjam' => ['label' => 'Peminjam', 'tipe' => 'integer'],
                    'jumlah_buku_dipinjam' => ['label' => 'Buku Dipinjam', 'tipe' => 'integer'],
                ],
            ],
        ],
    ],

    'KOPERASI' => [
        'datasets' => [
            'DATA_KOPERASI' => [
                'nama' => 'Data Koperasi',
                'deskripsi' => 'Master koperasi',
                'period_type' => 'master',
                'fields' => [
                    'nama_koperasi' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'koperasi_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Simpan Pinjam','Produksi','Konsumsi','Jasa','Serba Usaha']],
                    'badan_hukum' => ['label' => 'Badan Hukum', 'tipe' => 'string'],
                    'jumlah_anggota' => ['label' => 'Jumlah Anggota', 'tipe' => 'integer'],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Pasif','Bubar']],
                ],
            ],
            'DATA_UMKM' => [
                'nama' => 'Data UMKM',
                'deskripsi' => 'Master UMKM',
                'period_type' => 'master',
                'fields' => [
                    'nama_umkm' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'nik_pemilik' => ['label' => 'NIK Pemilik', 'tipe' => 'reference', 'reference_target' => 'penduduks'],
                    'jenis_usaha' => ['label' => 'Jenis', 'tipe' => 'string'],
                    'kbli' => ['label' => 'KBLI', 'tipe' => 'string'],
                    'skala' => ['label' => 'Skala', 'tipe' => 'enum', 'options' => ['Mikro','Kecil','Menengah']],
                    'alamat' => ['label' => 'Alamat', 'tipe' => 'text'],
                ],
            ],
        ],
    ],

    'KESBANGPOL' => [
        'datasets' => [
            'DATA_ORMAS' => [
                'nama' => 'Data Ormas',
                'deskripsi' => 'Master Organisasi Masyarakat',
                'period_type' => 'master',
                'fields' => [
                    'nama_ormas' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'bidang' => ['label' => 'Bidang', 'tipe' => 'string'],
                    'ketua' => ['label' => 'Ketua', 'tipe' => 'string'],
                    'jumlah_anggota' => ['label' => 'Jumlah Anggota', 'tipe' => 'integer'],
                    'status_terdaftar' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Terdaftar','Tidak Terdaftar','Aktif','Tidak Aktif']],
                ],
            ],
            'KASUS_KONFLIK' => [
                'nama' => 'Kasus Konflik Sara',
                'deskripsi' => 'Histori kasus',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'lokasi' => ['label' => 'Lokasi', 'tipe' => 'text'],
                    'jenis_konflik' => ['label' => 'Jenis', 'tipe' => 'string'],
                    'pihak_terlibat' => ['label' => 'Pihak Terlibat', 'tipe' => 'text'],
                    'status_penanganan' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Dilaporkan','Mediasi','Selesai','Eskalasi']],
                ],
            ],
        ],
    ],

    'INSPEKTORAT' => [
        'datasets' => [
            'HASIL_AUDIT' => [
                'nama' => 'Hasil Audit Internal',
                'deskripsi' => 'Hasil audit per OPD',
                'period_type' => 'event',
                'fields' => [
                    'tanggal_audit' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'opd_id' => ['label' => 'OPD Diperiksa', 'tipe' => 'reference', 'reference_target' => 'opds', 'required' => true],
                    'jenis_pemeriksaan' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Reguler','Khusus','Kasus','Evaluasi']],
                    'jumlah_temuan' => ['label' => 'Jumlah Temuan', 'tipe' => 'integer'],
                    'jumlah_rekomendasi' => ['label' => 'Rekomendasi', 'tipe' => 'integer'],
                    'nilai' => ['label' => 'Nilai', 'tipe' => 'enum', 'options' => ['Sangat Baik','Baik','Cukup','Kurang','Buruk']],
                ],
            ],
            'TEMUAN_PEMERIKSAAN' => [
                'nama' => 'Temuan Pemeriksaan',
                'deskripsi' => 'Detail temuan per audit',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'opd_id' => ['label' => 'OPD', 'tipe' => 'reference', 'reference_target' => 'opds'],
                    'jenis_temuan' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Penyimpangan','Ketidakpatuhan','Inefisiensi','Potensi Kerugian']],
                    'uraian' => ['label' => 'Uraian', 'tipe' => 'text'],
                    'nilai_temuan' => ['label' => 'Nilai Temuan (Rp)', 'tipe' => 'decimal'],
                    'status_tl' => ['label' => 'Status Tindak Lanjut', 'tipe' => 'enum', 'options' => ['Belum','Proses','Selesai']],
                ],
            ],
        ],
    ],

    'SETDA' => [
        'datasets' => [
            'AGENDA_PEMDA' => [
                'nama' => 'Agenda Pemda',
                'deskripsi' => 'Agenda kegiatan Pimpinan Daerah',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'kegiatan' => ['label' => 'Kegiatan', 'tipe' => 'string', 'required' => true],
                    'lokasi' => ['label' => 'Lokasi', 'tipe' => 'string'],
                    'pimpinan_hadir' => ['label' => 'Pimpinan Hadir', 'tipe' => 'string'],
                ],
            ],
            'DOKUMEN_KERJASAMA' => [
                'nama' => 'Dokumen Kerjasama Daerah',
                'deskripsi' => 'MoU/MoA/PKS Pemda',
                'period_type' => 'master',
                'fields' => [
                    'no_dokumen' => ['label' => 'No Dokumen', 'tipe' => 'string', 'required' => true],
                    'judul' => ['label' => 'Judul', 'tipe' => 'string', 'required' => true],
                    'pihak_kedua' => ['label' => 'Mitra', 'tipe' => 'string', 'required' => true],
                    'tanggal_ttd' => ['label' => 'Tgl TTD', 'tipe' => 'date'],
                    'tanggal_berakhir' => ['label' => 'Tgl Berakhir', 'tipe' => 'date'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Berakhir','Diperpanjang']],
                ],
            ],
        ],
    ],

    'SETWAN' => [
        'datasets' => [
            'AGENDA_SIDANG' => [
                'nama' => 'Agenda Sidang DPRD',
                'deskripsi' => 'Jadwal rapat/sidang DPRD',
                'period_type' => 'event',
                'fields' => [
                    'tanggal' => ['label' => 'Tanggal', 'tipe' => 'date', 'required' => true],
                    'jenis_sidang' => ['label' => 'Jenis Sidang', 'tipe' => 'enum', 'options' => ['Paripurna','Komisi','Badan Anggaran','Pansus','Rapat Kerja']],
                    'agenda' => ['label' => 'Agenda', 'tipe' => 'text', 'required' => true],
                    'lokasi' => ['label' => 'Lokasi', 'tipe' => 'string'],
                ],
            ],
            'PRODUK_DPRD' => [
                'nama' => 'Produk Hukum DPRD',
                'deskripsi' => 'Peraturan Daerah & Keputusan DPRD',
                'period_type' => 'master',
                'fields' => [
                    'jenis' => ['label' => 'Jenis', 'tipe' => 'enum', 'options' => ['Perda','Peraturan DPRD','Keputusan DPRD','Keputusan Pimpinan DPRD']],
                    'nomor' => ['label' => 'Nomor', 'tipe' => 'string', 'required' => true],
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer'],
                    'tentang' => ['label' => 'Tentang', 'tipe' => 'string'],
                    'tanggal_penetapan' => ['label' => 'Tgl Penetapan', 'tipe' => 'date'],
                ],
            ],
        ],
    ],

    'DISTAMAN' => [
        'datasets' => [
            'DATA_PERKEBUNAN' => [
                'nama' => 'Data Perkebunan',
                'deskripsi' => 'Luas & produksi perkebunan',
                'period_type' => 'tahunan',
                'fields' => [
                    'tahun' => ['label' => 'Tahun', 'tipe' => 'integer', 'required' => true],
                    'kecamatan_id' => ['label' => 'Kecamatan', 'tipe' => 'reference', 'reference_target' => 'wilayahs'],
                    'komoditas' => ['label' => 'Komoditas', 'tipe' => 'enum', 'options' => ['Kelapa','Cengkeh','Pala','Kopi','Kakao','Vanili']],
                    'luas_ha' => ['label' => 'Luas (ha)', 'tipe' => 'decimal'],
                    'produksi_ton' => ['label' => 'Produksi (ton)', 'tipe' => 'decimal'],
                ],
            ],
            'DATA_PERTAMBANGAN' => [
                'nama' => 'Data Pertambangan',
                'deskripsi' => 'IUP & produksi pertambangan',
                'period_type' => 'master',
                'fields' => [
                    'nama_perusahaan' => ['label' => 'Nama', 'tipe' => 'string', 'required' => true],
                    'no_iup' => ['label' => 'No IUP', 'tipe' => 'string'],
                    'jenis_tambang' => ['label' => 'Jenis', 'tipe' => 'string'],
                    'lokasi_objek_id' => ['label' => 'Lokasi', 'tipe' => 'reference', 'reference_target' => 'objek_lapangans'],
                    'status' => ['label' => 'Status', 'tipe' => 'enum', 'options' => ['Aktif','Nonaktif','Cabut']],
                ],
            ],
        ],
    ],
];
