<?php
/**
 * Upgrade 7 critical datasets untuk Buku Sektoral 2024.
 */

return [

    // Tabel 3.20 - Stunting
    "3.20" => [
        "deskripsi" => "Data elektronik Pencatatan Pelaporan Gizi Berbasis Masyarakat (e-PPGBM) per Puskesmas",
        "fields" => [
            "kode_puskesmas" => ["label" => "Kode Puskesmas", "tipe" => "string", "required" => true, "is_dimension" => false],
            "nama_puskesmas" => [
                "label" => "Nama Puskesmas",
                "tipe" => "enum",
                "required" => true,
                "is_dimension" => false,
                "options" => [
                    "Lapango" => "Puskesmas Lapango",
                    "Kahakitang" => "Puskesmas Kahakitang",
                    "Tamako" => "Puskesmas Tamako",
                    "Dagho" => "Puskesmas Dagho",
                    "Manalu" => "Puskesmas Manalu",
                    "Salurang" => "Puskesmas Salurang",
                    "Pintareng" => "Puskesmas Pintareng",
                    "Kuma" => "Puskesmas Kuma",
                    "Manganitu" => "Puskesmas Manganitu",
                    "Tahuna Barat" => "Puskesmas Tahuna Barat",
                    "Enemawira" => "Puskesmas Enemawira",
                    "Kalasuge" => "Puskesmas Kalasuge",
                    "Nusa" => "Puskesmas Nusa",
                    "Kendahe" => "Puskesmas Kendahe",
                    "Marore" => "Puskesmas Marore",
                    "Manente" => "Puskesmas Manente",
                    "Tona" => "Puskesmas Tona"
                ]
            ],
            "bulan_penarikan" => ["label" => "Periode Penarikan", "tipe" => "string", "required" => true, "is_dimension" => true],
            "sasaran_balita" => ["label" => "Sasaran Balita", "tipe" => "integer", "required" => true, "satuan" => "Balita"],
            "entry_pengukuran" => ["label" => "Balita Diukur", "tipe" => "integer", "required" => true, "satuan" => "Balita"],
            "persen_pengukuran" => ["label" => "% Pengukuran", "tipe" => "decimal", "required" => false, "satuan" => "Persen"],
            "jumlah_stunting" => ["label" => "Jumlah Stunting", "tipe" => "integer", "required" => true, "satuan" => "Balita"],
            "persen_stunting" => ["label" => "% Stunting", "tipe" => "decimal", "required" => false, "satuan" => "Persen"],
            "status_gizi_baik" => ["label" => "Gizi Baik", "tipe" => "integer", "required" => false, "is_dimension" => true, "satuan" => "Balita"],
            "status_gizi_kurang" => ["label" => "Gizi Kurang", "tipe" => "integer", "required" => false, "is_dimension" => true, "satuan" => "Balita"],
            "status_gizi_buruk" => ["label" => "Gizi Buruk", "tipe" => "integer", "required" => false, "is_dimension" => true, "satuan" => "Balita"],
            "status_gizi_lebih" => ["label" => "Gizi Lebih", "tipe" => "integer", "required" => false, "is_dimension" => true, "satuan" => "Balita"]
        ]
    ],
    // Tabel 3.21 - AKI/AKB/AHH
    "3.21" => [
        "deskripsi" => "AKI/AKB/AHH",
        "fields" => [
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["AKI"=>"AKI","AKB"=>"AKB","AHH"=>"AHH"]],
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "nilai" => ["label" => "Nilai", "tipe" => "decimal", "required" => true, "satuan" => "Per Indikator"]
        ]
    ],

    // Tabel 4.47
    "4.47" => [
        "deskripsi" => "Produksi per Jenis Ikan",
        "fields" => [
            "bulan" => ["label" => "Bulan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Januari"=>"Januari","Februari"=>"Februari","Maret"=>"Maret","April"=>"April","Mei"=>"Mei","Juli"=>"Juli","Agustus"=>"Agustus","September"=>"September","Oktober"=>"Oktober","November"=>"November","Desember"=>"Desember"]],
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_ikan" => ["label" => "Jenis Ikan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Selar"=>"Selar","Kuwe"=>"Kuwe","Layang"=>"Layang","Tongkol"=>"Tongkol","Cakalang"=>"Cakalang","Tuna"=>"Tuna","Madidihang"=>"Madidihang","Kakap"=>"Kakap","Kerapu"=>"Kerapu","Kurisi"=>"Kurisi","Cumi"=>"Cumi","Gurita"=>"Gurita","Teri"=>"Teri","Tenggiri"=>"Tenggiri","Kembung"=>"Kembung","Cucut"=>"Cucut","Udang"=>"Udang","Kepiting"=>"Kepiting","Teripang"=>"Teripang","Lainnya"=>"Lainnya"]],
            "volume_kg" => ["label" => "Volume", "tipe" => "decimal", "required" => true, "satuan" => "Kg"],
            "harga_rata_rp_kg" => ["label" => "Harga Rata-rata", "tipe" => "decimal", "required" => true, "satuan" => "Rp/Kg"],
            "nilai_produksi_rp" => ["label" => "Nilai Produksi", "tipe" => "decimal", "required" => false, "satuan" => "Rp"]
        ]
    ],

    // Tabel 4.48
    "4.48" => [
        "deskripsi" => "Armada Kapal per GT",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true],
            "kategori_gt" => ["label" => "Kategori GT", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["0-3"=>"0-3 GT","3-5"=>"3-5 GT","5-10"=>"5-10 GT","10-20"=>"10-20 GT","20-30"=>"20-30 GT","30-60"=>"30-60 GT","60-100"=>"60-100 GT","100-200"=>"100-200 GT",">200"=>">200 GT"]],
            "jumlah_unit" => ["label" => "Jumlah Unit", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 5.59
    "5.59" => [
        "deskripsi" => "Jalan dan Jembatan",
        "fields" => [
            "kategori" => ["label" => "Kategori", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Jalan Kabupaten"=>"Jalan Kabupaten","Jalan Provinsi"=>"Jalan Provinsi","Jalan Nasional"=>"Jalan Nasional","Konstruksi Permukaan"=>"Konstruksi","Kualifikasi Kondisi"=>"Kondisi","Jembatan"=>"Jembatan"]],
            "uraian" => ["label" => "Uraian", "tipe" => "string", "required" => true, "is_dimension" => true],
            "nilai" => ["label" => "Nilai", "tipe" => "decimal", "required" => true, "satuan" => "Km/Unit/M"]
        ]
    ],

    // Tabel 6.72
    "6.72" => [
        "deskripsi" => "Pedagang dan Sarana",
        "fields" => [
            "kecamatan" => ["label" => "Kecamatan", "tipe" => "reference", "reference_target" => "wilayahs", "required" => true, "is_dimension" => true],
            "jenis_sarana" => ["label" => "Jenis Sarana", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Ruko"=>"Ruko","Kios"=>"Kios","Los/Bangsal"=>"Los","Tenda"=>"Tenda","Hamparan"=>"Hamparan","Meja"=>"Meja"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 7.75
    "7.75" => [
        "deskripsi" => "Realisasi Anggaran",
        "fields" => [
            "kategori_utama" => ["label" => "Kategori Utama", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Pendapatan"=>"Pendapatan - LRA","Belanja Operasi"=>"Belanja Operasi","Belanja Modal"=>"Belanja Modal","Tak Terduga"=>"Belanja Tak Terduga","Transfer"=>"Belanja Transfer","Pembiayaan"=>"Pembiayaan"]],
            "subkategori" => ["label" => "Subkategori", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["PAD"=>"PAD","Transfer"=>"Transfer","Lain"=>"Lain-lain","Pegawai"=>"Belanja Pegawai","Barjas"=>"Barang/Jasa","Bunga"=>"Belanja Bunga","Hibah"=>"Belanja Hibah","Bansos"=>"Belanja Bantuan Sosial","Modal"=>"Belanja Modal","Penerimaan"=>"Penerimaan","Pengeluaran"=>"Pengeluaran"]],
            "item" => ["label" => "Item", "tipe" => "string", "required" => true, "is_dimension" => true],
            "pagu_anggaran" => ["label" => "Pagu Anggaran", "tipe" => "decimal", "required" => true, "satuan" => "Rp"],
            "realisasi_anggaran" => ["label" => "Realisasi", "tipe" => "decimal", "required" => true, "satuan" => "Rp"],
            "persen_realisasi" => ["label" => "% Realisasi", "tipe" => "decimal", "required" => false, "satuan" => "Persen"]
        ]
    ],

    // Tabel 3.12 - Jumlah KK per Kecamatan
    "3.12" => [
        "deskripsi" => "Jumlah Kepala Keluarga per Kecamatan di Kab. Sangihe",
        "fields" => [
            "kecamatan" => ["label" => "Kecamatan", "tipe" => "reference", "reference_target" => "wilayahs", "required" => true, "is_dimension" => true],
            "kk_laki" => ["label" => "KK Laki-laki", "tipe" => "integer", "required" => true, "satuan" => "KK"],
            "kk_perempuan" => ["label" => "KK Perempuan", "tipe" => "integer", "required" => true, "satuan" => "KK"],
            "total" => ["label" => "Total", "tipe" => "integer", "required" => false, "satuan" => "KK", "klasifikasi" => "Total KK (auto-calc)"]
        ]
    ],

    // Tabel 3.13 - Jumlah Penduduk per Kecamatan
    "3.13" => [
        "deskripsi" => "Jumlah Penduduk per Kecamatan di Kab. Sangihe",
        "fields" => [
            "kecamatan" => ["label" => "Kecamatan", "tipe" => "reference", "reference_target" => "wilayahs", "required" => true, "is_dimension" => true],
            "penduduk_laki" => ["label" => "Penduduk Laki-laki", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"],
            "penduduk_perempuan" => ["label" => "Penduduk Perempuan", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"],
            "total" => ["label" => "Total", "tipe" => "integer", "required" => false, "satuan" => "Jiwa", "klasifikasi" => "Total penduduk (auto-calc)"]
        ]
    ],

    // Tabel 3.14 - Penduduk Berdasarkan Usia
    "3.14" => [
        "deskripsi" => "Jumlah Penduduk Berdasarkan Kelompok Umur",
        "fields" => [
            "kelompok_umur" => ["label" => "Kelompok Umur", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["0-4"=>"0-4 Tahun","5-9"=>"5-9 Tahun","10-14"=>"10-14 Tahun","15-19"=>"15-19 Tahun","20-24"=>"20-24 Tahun","25-29"=>"25-29 Tahun","30-34"=>"30-34 Tahun","35-39"=>"35-39 Tahun","40-44"=>"40-44 Tahun","45-49"=>"45-49 Tahun","50-54"=>"50-54 Tahun","55-59"=>"55-59 Tahun","60-64"=>"60-64 Tahun","65-69"=>"65-69 Tahun","70-74"=>"70-74 Tahun","75+"=>"75+ Tahun"]],
            "lk" => ["label" => "Laki-laki", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"],
            "pr" => ["label" => "Perempuan", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"]
        ]
    ],

    // Tabel 3.15 - Kepemilikan Akta Kelahiran
    "3.15" => [
        "deskripsi" => "Jumlah Kepemilikan Akta Kelahiran 0-18 Tahun per Kecamatan",
        "fields" => [
            "kecamatan" => ["label" => "Kecamatan", "tipe" => "reference", "reference_target" => "wilayahs", "required" => true, "is_dimension" => true],
            "lk_0_18" => ["label" => "Laki-laki 0-18 th", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"],
            "pr_0_18" => ["label" => "Perempuan 0-18 th", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Jiwa"]
        ]
    ],

    // Tabel 3.16 - APM dan APS per Jenjang
    "3.16" => [
        "deskripsi" => "Angka Partisipasi Murni dan Angka Partisipasi Sekolah per Jenjang",
        "fields" => [
            "jenjang" => ["label" => "Jenjang", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["PAUD"=>"PAUD/TK","SD"=>"SD/MI","SMP"=>"SMP/MTs","SMA"=>"SMA/SMK"]],
            "apm" => ["label" => "APM (%)", "tipe" => "decimal", "required" => true, "satuan" => "Persen"],
            "aps" => ["label" => "APS (%)", "tipe" => "decimal", "required" => true, "satuan" => "Persen"],
            "apk" => ["label" => "APK (%)", "tipe" => "decimal", "required" => false, "satuan" => "Persen"]
        ]
    ],

    // Tabel 3.17 - Ketersediaan Guru dan Murid
    "3.17" => [
        "deskripsi" => "Jumlah Guru dan Murid per Jenjang Pendidikan",
        "fields" => [
            "jenjang" => ["label" => "Jenjang", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["PAUD"=>"PAUD/TK","SD"=>"SD/MI","SMP"=>"SMP/MTs","SMA"=>"SMA/SMK"]],
            "jumlah_guru" => ["label" => "Jumlah Guru", "tipe" => "integer", "required" => true, "satuan" => "Orang"],
            "jumlah_murid" => ["label" => "Jumlah Murid", "tipe" => "integer", "required" => true, "satuan" => "Siswa"],
            "rasio" => ["label" => "Rasio", "tipe" => "integer", "required" => false, "satuan" => "Murid per Guru", "klasifikasi" => "1 guru per N murid (auto-calc)"]
        ]
    ],

    // Tabel 3.18 - Sarana dan Prasarana Seni Budaya
    "3.18" => [
        "deskripsi" => "Sarana dan Prasarana Seni Budaya",
        "fields" => [
            "kategori" => ["label" => "Kategori", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Kesenian Tradisional"=>"Kesenian Tradisional","Kesenian Modern"=>"Kesenian Modern","Sarana Pendukung"=>"Sarana Pendukung","Grup Kesenian"=>"Grup Kesenian"]],
            "nama_sarana" => ["label" => "Nama Sarana", "tipe" => "string", "required" => true, "is_dimension" => true],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 3.19 - Angka Melek Huruf dan RLS
    "3.19" => [
        "deskripsi" => "Angka Melek Huruf dan Rata-rata Lama Sekolah",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_kelamin" => ["label" => "Jenis Kelamin", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["L"=>"Laki-laki","P"=>"Perempuan","Total"=>"Total"]],
            "amh_persen" => ["label" => "AMH (%)", "tipe" => "decimal", "required" => true, "satuan" => "Persen"],
            "rls_tahun" => ["label" => "RLS (Tahun)", "tipe" => "decimal", "required" => true, "satuan" => "Tahun"]
        ]
    ],

    // Tabel 4.42 - Luas Tanam, Panen, Produksi, Produktivitas Tanaman Pangan
    "4.42" => [
        "deskripsi" => "Luas Tanam, Luas Panen, Produksi dan Produktivitas Komoditas Tanaman Pangan",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "komoditas" => ["label" => "Komoditas", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Padi Sawah"=>"Padi Sawah","Jagung"=>"Jagung","Ubi Kayu"=>"Ubi Kayu","Ubi Jalar"=>"Ubi Jalar","Talas"=>"Talas","Kacang Tanah"=>"Kacang Tanah","Ubi Lainnya"=>"Ubi Lainnya"]],
            "luas_tanam_ha" => ["label" => "Luas Tanam", "tipe" => "decimal", "required" => true, "satuan" => "Ha"],
            "luas_panen_ha" => ["label" => "Luas Panen", "tipe" => "decimal", "required" => true, "satuan" => "Ha"],
            "produksi_ton" => ["label" => "Produksi", "tipe" => "decimal", "required" => true, "satuan" => "Ton"],
            "produktivitas_ton_ha" => ["label" => "Produktivitas", "tipe" => "decimal", "required" => false, "satuan" => "Ton/Ha", "klasifikasi" => "Auto-calc = produksi / luas_panen"]
        ]
    ],

    // Tabel 4.43 - Hortikultura (Sayuran dan Buah-buahan)
    "4.43" => [
        "deskripsi" => "Luas Tanam, Panen, Produksi Hortikultura Semusim",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "komoditas" => ["label" => "Komoditas", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Sawi"=>"Sawi/Petsai","Bayam"=>"Bayam","Buncis"=>"Buncis","Cabe Keriting"=>"Cabe Keriting","Cabe Rawit"=>"Cabe Rawit","Kacang Panjang"=>"Kacang Panjang","Mentimun"=>"Mentimun","Kangkung"=>"Kangkung","Terung"=>"Terung","Tomat"=>"Tomat","Semangka"=>"Semangka"]],
            "luas_tanam_ha" => ["label" => "Luas Tanam", "tipe" => "decimal", "required" => true, "satuan" => "Ha"],
            "luas_panen_ha" => ["label" => "Luas Panen", "tipe" => "decimal", "required" => true, "satuan" => "Ha"],
            "produksi_ton" => ["label" => "Produksi", "tipe" => "decimal", "required" => true, "satuan" => "Ton"],
            "produktivitas_ton_ha" => ["label" => "Produktivitas", "tipe" => "decimal", "required" => false, "satuan" => "Ton/Ha"]
        ]
    ],

    // Tabel 4.44 - Perkebunan
    "4.44" => [
        "deskripsi" => "Luas Areal dan Produksi Komoditas Perkebunan",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "komoditas" => ["label" => "Komoditas", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Kelapa Dalam"=>"Kelapa Dalam","Kelapa Hibrida"=>"Kelapa Hibrida","Cengkeh"=>"Cengkeh","Pala"=>"Pala","Sagu Barug"=>"Sagu Barug"]],
            "luas_lahan_ha" => ["label" => "Luas Lahan", "tipe" => "decimal", "required" => true, "satuan" => "Ha"],
            "produksi_ton" => ["label" => "Produksi", "tipe" => "decimal", "required" => true, "satuan" => "Ton"]
        ]
    ],

    // Tabel 5.49 - Prasarana Transportasi Laut
    "5.49" => [
        "deskripsi" => "Kondisi Prasarana Penunjang Transportasi Laut",
        "fields" => [
            "jenis_prasarana" => ["label" => "Jenis Prasarana", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Pelayanan Peti Kemas"=>"Pelayanan Peti Kemas","Pelabuhan Nusantara"=>"Pelabuhan Nusantara","Pelabuhan Perintis"=>"Pelabuhan Perintis/Rakyat","Tambatan/Dermaga"=>"Tambatan/Dermaga","Mercu"=>"Mercu Suar","Rambu Suar"=>"Rambu Suar"]],
            "nama" => ["label" => "Nama/Lokasi", "tipe" => "string", "required" => true, "is_dimension" => true],
            "jumlah_unit" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"],
            "status" => ["label" => "Status Operasional", "tipe" => "enum", "required" => false, "options" => ["Operasional"=>"Operasional","Tidak Aktif"=>"Tidak Aktif","Rehabilitasi"=>"Rehabilitasi"]]
        ]
    ],

    // Tabel 5.50 - Kendaraan Bermotor
    "5.50" => [
        "deskripsi" => "Data Perkembangan Kendaraan Bermotor",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_kendaraan" => ["label" => "Jenis Kendaraan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Bus"=>"Bus","Pick Up"=>"Pick Up","Truck"=>"Truck","Truck Tangki"=>"Truck Tangki","Sepeda Motor"=>"Sepeda Motor","Mobil Penumpang"=>"Mobil Penumpang"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 5.51 - Kecelakaan Lalu Lintas
    "5.51" => [
        "deskripsi" => "Data Kejadian Kecelakaan Lalu Lintas",
        "fields" => [
            "bulan" => ["label" => "Bulan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Januari"=>"Januari","Februari"=>"Februari","Maret"=>"Maret","April"=>"April","Mei"=>"Mei","Juni"=>"Juni","Juli"=>"Juli","Agustus"=>"Agustus","September"=>"September","Oktober"=>"Oktober","November"=>"November","Desember"=>"Desember"]],
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_kejadian" => ["label" => "Jenis Kejadian", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Kecelakaan LL"=>"Kecelakaan Lalu Lintas","Kecelakaan Tunggal"=>"Kecelakaan Tunggal","Laka JOL"=>"Laka JOL","Tabrak Lari"=>"Tabrak Lari"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Kejadian"]
        ]
    ],

    // Tabel 5.52 - Prasarana dan Fasilitas Perlengkapan Jalan
    "5.52" => [
        "deskripsi" => "Prasarana Perhubungan dan Fasilitas Perlengkapan Jalan",
        "fields" => [
            "jenis_fasilitas" => ["label" => "Jenis Fasilitas", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Rambu-rambu"=>"Rambu-rambu Lalu Lintas","RPPJ"=>"Rambu Pendahulu Petunjuk Jalan","Cermin Tikungan"=>"Cermin Tikungan","Marka Jalan"=>"Marka Jalan","APILL"=>"Traffic Light","Warning Light"=>"Warning Light","Guardrail"=>"Guardrail/Pagar Pengaman","Halte"=>"Halte","Lampu PJU"=>"Lampu Penerangan Jalan Umum"]],
            "satuan" => ["label" => "Satuan", "tipe" => "enum", "required" => true, "is_dimension" => false, "options" => ["Buah"=>"Buah","Unit"=>"Unit","Meter"=>"Meter","M2"=>"M2","Titik"=>"Titik"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "decimal", "required" => true]
        ]
    ],

    // Tabel 5.53 - Trayek Kendaraan Umum
    "5.53" => [
        "deskripsi" => "Trayek dan Jumlah Kendaraan Umum Yang Beroperasi",
        "fields" => [
            "nama_trayek" => ["label" => "Nama Trayek", "tipe" => "string", "required" => true, "is_dimension" => true],
            "jumlah_kendaraan" => ["label" => "Jumlah Kendaraan", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 6.66 - Koperasi dan UKM
    "6.66" => [
        "deskripsi" => "Data Koperasi dan UKM",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Lembaga Koperasi"=>"Jumlah Lembaga Koperasi","Koperasi Aktif"=>"Koperasi yang Masih Aktif","UMKM"=>"Usaha Mikro dan Kecil"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 6.71 - Pasar Tradisional
    "6.71" => [
        "deskripsi" => "Data Pasar Tradisional dan Bangunan Pasar",
        "fields" => [
            "kecamatan" => ["label" => "Kecamatan", "tipe" => "reference", "reference_target" => "wilayahs", "required" => true, "is_dimension" => true],
            "jumlah_pasar" => ["label" => "Jumlah Pasar", "tipe" => "integer", "required" => true, "satuan" => "Unit"],
            "jumlah_bangunan" => ["label" => "Jumlah Bangunan", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 7.73 - Penyerapan Anggaran Perangkat Daerah
    "7.73" => [
        "deskripsi" => "Penyerapan Anggaran per Perangkat Daerah",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "perangkat_daerah" => ["label" => "Perangkat Daerah", "tipe" => "string", "required" => true, "is_dimension" => true, "klasifikasi" => "Nama OPD"],
            "pagu_anggaran" => ["label" => "Pagu Anggaran", "tipe" => "decimal", "required" => true, "satuan" => "Rp"],
            "realisasi_anggaran" => ["label" => "Realisasi Anggaran", "tipe" => "decimal", "required" => true, "satuan" => "Rp"],
            "persen_realisasi" => ["label" => "% Realisasi", "tipe" => "decimal", "required" => false, "satuan" => "Persen", "klasifikasi" => "Auto-calc"]
        ]
    ],

    // Tabel 8.85 - Pengelolaan SDA dan LH
    "8.85" => [
        "deskripsi" => "Data Pengelolaan Sumber Daya Alam dan Lingkungan Hidup",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["NSPM Persampahan"=>"NSPM Penanganan Persampahan","NSPM LH SDA"=>"NSPM Pengelolaan LH SDA","NSPM RTH"=>"NSPM Pengelolaan RTH","Penanganan Sampah"=>"Persentase Penanganan Sampah","Penanggulangan Pencemaran"=>"Persentase Penanggulangan Pencemaran"]],
            "nilai_persen" => ["label" => "Nilai (%)", "tipe" => "decimal", "required" => true, "satuan" => "Persen"]
        ]
    ],

    // Tabel 8.86 - Pengelolaan Persampahan
    "8.86" => [
        "deskripsi" => "Data Pengelolaan Persampahan",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "uraian" => ["label" => "Uraian", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Unit Pengelola"=>"Unit Pengelola Persampahan","TPS Pertama"=>"Tempat Penampungan Sementara Pertama","Pemilahan"=>"Pemilahan/Pengolahan Sampah","TPS"=>"Tempat Penampungan Sementara","TPA"=>"Tempat Pembuangan Akhir"]],
            "jumlah_unit" => ["label" => "Jumlah Unit", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 8.82 - SDM Satpol PP dan Damkar
    "8.82" => [
        "deskripsi" => "Keadaan SDM Satpol PP dan Kebakaran",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jabatan" => ["label" => "Jabatan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Kasatpol PP"=>"Kasatpol PP","Kabid"=>"Kepala Bidang","Kasi"=>"Kepala Seksi","Staf"=>"Staf","Petugas Damkar"=>"Petugas Pemadam Kebakaran"]],
            "jumlah_personil" => ["label" => "Jumlah Personil", "tipe" => "integer", "required" => true, "satuan" => "Orang"]
        ]
    ],

    // Tabel 9.88 - Indeks SPBE
    "9.88" => [
        "deskripsi" => "Indeks Sistem Pemerintahan Berbasis Elektronik (SPBE)",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "tingkat_instansi" => ["label" => "Tingkat Instansi", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Nasional"=>"Nasional","Provinsi"=>"Provinsi Sulawesi Utara","Kabupaten"=>"Kabupaten Kepulauan Sangihe"]],
            "indeks_spbe" => ["label" => "Indeks SPBE", "tipe" => "decimal", "required" => true, "satuan" => "Skor"],
            "predikat" => ["label" => "Predikat", "tipe" => "enum", "required" => false, "options" => ["Sangat Baik"=>"Sangat Baik","Baik"=>"Baik","Cukup"=>"Cukup","Kurang"=>"Kurang"]]
        ]
    ],

    // Tabel 9.92 - Kunjungan Wisatawan
    "9.92" => [
        "deskripsi" => "Jumlah Kunjungan Wisatawan Mancanegara dan Nusantara",
        "fields" => [
            "bulan" => ["label" => "Bulan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Januari"=>"Januari","Februari"=>"Februari","Maret"=>"Maret","April"=>"April","Mei"=>"Mei","Juni"=>"Juni","Juli"=>"Juli","Agustus"=>"Agustus","September"=>"September","Oktober"=>"Oktober","November"=>"November","Desember"=>"Desember"]],
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "wisman" => ["label" => "Wisatawan Mancanegara", "tipe" => "integer", "required" => true, "satuan" => "Orang"],
            "wisnu" => ["label" => "Wisatawan Nusantara", "tipe" => "integer", "required" => true, "satuan" => "Orang"],
            "total" => ["label" => "Total", "tipe" => "integer", "required" => false, "satuan" => "Orang", "klasifikasi" => "Auto-calc = wisman + wisnu"]
        ]
    ],

    // Tabel 3.22 - Kesejahteraan Sosial
    "3.22" => [
        "deskripsi" => "Data Kesejahteraan Sosial Masyarakat",
        "fields" => [
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Panti Asuhan"=>"Panti Asuhan","PMKS"=>"PMKS Bantuan Sosial","KK Miskin"=>"KK Miskin Dapat Ketrampilan","Anak Terlantar"=>"Anak Terlantar Terdata","Disabilitas"=>"Disabilitas Terdata","Lansia Terlantar"=>"Lansia Terlantar"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit/Orang"]
        ]
    ],

    // Tabel 3.26 - PUS dan Peserta KB Aktif
    "3.26" => [
        "deskripsi" => "Pasangan Usia Subur (PUS) dan Peserta KB Aktif",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "kategori" => ["label" => "Kategori", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["PUS"=>"Pasangan Usia Subur (PUS)","Peserta Aktif"=>"Peserta KB Aktif","Peserta Baru"=>"Peserta KB Baru"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Orang"]
        ]
    ],

    // Tabel 3.27 - Wanita Usia Subur
    "3.27" => [
        "deskripsi" => "Jumlah Wanita Usia Subur (WUS) Berdasarkan Kelompok Umur",
        "fields" => [
            "kelompok_umur" => ["label" => "Kelompok Umur", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["15-19"=>"15-19 Tahun","20-24"=>"20-24 Tahun","25-29"=>"25-29 Tahun","30-34"=>"30-34 Tahun","35-39"=>"35-39 Tahun","40-44"=>"40-44 Tahun","45-49"=>"45-49 Tahun"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Orang"]
        ]
    ],

    // Tabel 3.31 - Kekerasan Perempuan dan Anak
    "3.31" => [
        "deskripsi" => "Data Kekerasan Terhadap Perempuan dan Anak",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_kekerasan" => ["label" => "Jenis Kekerasan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Fisik"=>"Kekerasan Fisik","Psikis"=>"Kekerasan Psikis","Seksual"=>"Kekerasan Seksual","Penelantaran"=>"Penelantaran","Eksploitasi"=>"Eksploitasi"]],
            "jumlah_kasus" => ["label" => "Jumlah Kasus", "tipe" => "integer", "required" => true, "satuan" => "Kasus"],
            "jumlah_korban" => ["label" => "Jumlah Korban", "tipe" => "integer", "required" => false, "satuan" => "Orang"]
        ]
    ],

    // Tabel 3.33 - Pemuda dan Olahraga
    "3.33" => [
        "deskripsi" => "Data Perkembangan Pemuda dan Olahraga",
        "fields" => [
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Organisasi Pemuda"=>"Jumlah Organisasi Pemuda","Atlet"=>"Jumlah Atlet Terdaftar","Pelatih"=>"Jumlah Pelatih Bersertifikat","Sarana"=>"Sarana Prasarana Olahraga","Event"=>"Event Olahraga/Tahun"]],
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Unit"]
        ]
    ],

    // Tabel 3.34 - Perkembangan Tenaga Kerja
    "3.34" => [
        "deskripsi" => "Data Perkembangan Tenaga Kerja",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "indikator" => ["label" => "Indikator", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Pencari Kerja"=>"Pencari Kerja Terdaftar","Lowongan"=>"Lowongan Kerja","Penempatan"=>"Yang Ditempatkan","Angkatan Kerja"=>"Angkatan Kerja","Bekerja"=>"Yang Bekerja","Pengangguran"=>"Pengangguran"]],
            "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "required" => true, "satuan" => "Orang"]
        ]
    ],

    // Tabel 4.37 - Kecukupan Gizi
    "4.37" => [
        "deskripsi" => "Tingkat Kecukupan Gizi Konsumsi Pangan Penduduk",
        "fields" => [
            "jenis_bahan_makanan" => ["label" => "Jenis Bahan Makanan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Padi-padian"=>"Padi-padian","Umbi-umbian"=>"Umbi-umbian","Pangan Hewani"=>"Pangan Hewani","Minyak/Lemak"=>"Minyak dan Lemak","Buah/Biji"=>"Buah/Biji Berminyak","Kacang-kacangan"=>"Kacang-kacangan","Gula"=>"Gula","Sayur/Buah"=>"Sayur dan Buah","Lain-lain"=>"Lain-lain","Total"=>"Total"]],
            "kkal_per_hari" => ["label" => "Kkal/Hari", "tipe" => "decimal", "required" => true, "satuan" => "Kkal"],
            "persen_ake" => ["label" => "% AKE", "tipe" => "decimal", "required" => false, "satuan" => "Persen"],
            "gram_protein_per_hari" => ["label" => "Protein g/hari", "tipe" => "decimal", "required" => false, "satuan" => "Gram"],
            "persen_akp" => ["label" => "% AKP", "tipe" => "decimal", "required" => false, "satuan" => "Persen"]
        ]
    ],
    // Tabel 4.38 - Skor Pola Pangan Harapan (PPH)
    "4.38" => [
        "deskripsi" => "Skor Pola Pangan Harapan Konsumsi Pangan Penduduk Tahun 2024",
        "fields" => [
            "kelompok_pangan" => ["label" => "Kelompok Pangan", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Padi-padian" => "Padi-padian", "Umbi-umbian" => "Umbi-umbian", "Pangan Hewani" => "Pangan Hewani", "Minyak/Lemak" => "Minyak dan Lemak", "Buah/Biji" => "Buah/Biji Berminyak", "Kacang-kacangan" => "Kacang-kacangan", "Gula" => "Gula", "Sayur/Buah" => "Sayur dan Buah", "Lain-lain" => "Lain-lain", "Total" => "Total"]],
            "berat_pangan" => ["label" => "Berat Pangan", "tipe" => "decimal", "required" => false, "satuan" => "Gram/Kapita/Hari"],
            "kkal_per_hari" => ["label" => "Energi", "tipe" => "decimal", "required" => false, "satuan" => "Kkal/Kapita/Hari"],
            "persen_energi" => ["label" => "% Energi", "tipe" => "decimal", "required" => false, "satuan" => "Persen"],
            "skor_aktual" => ["label" => "Skor Aktual", "tipe" => "decimal", "required" => false, "satuan" => "Skor"],
            "skor_ake" => ["label" => "Skor AKE", "tipe" => "decimal", "required" => false, "satuan" => "Skor"],
            "skor_maksimal" => ["label" => "Skor Maksimal", "tipe" => "decimal", "required" => false, "satuan" => "Skor"],
            "skor_pph" => ["label" => "Skor PPH", "tipe" => "decimal", "required" => false, "satuan" => "Skor"],
        ]
    ],

    // Tabel 8.81 - Penanganan Korban Bencana
    "8.81" => [
        "deskripsi" => "Data Penanganan Korban Bencana",
        "fields" => [
            "tahun" => ["label" => "Tahun", "tipe" => "integer", "required" => true, "is_dimension" => true, "satuan" => "Tahun"],
            "jenis_bencana" => ["label" => "Jenis Bencana", "tipe" => "enum", "required" => true, "is_dimension" => true, "options" => ["Banjir"=>"Banjir","Longsor"=>"Tanah Longsor","Angin Puting Beliung"=>"Angin Puting Beliung","Gempa"=>"Gempa Bumi","Kebakaran"=>"Kebakaran","Abrasi"=>"Abrasi","Tsunami"=>"Tsunami","Lainnya"=>"Lainnya"]],
            "jumlah_kejadian" => ["label" => "Jumlah Kejadian", "tipe" => "integer", "required" => true, "satuan" => "Kali"],
            "korban_meninggal" => ["label" => "Meninggal", "tipe" => "integer", "required" => false, "satuan" => "Orang"],
            "korban_luka" => ["label" => "Luka", "tipe" => "integer", "required" => false, "satuan" => "Orang"],
            "mengungsi" => ["label" => "Mengungsi", "tipe" => "integer", "required" => false, "satuan" => "Orang"],
            "kerugian_rp" => ["label" => "Kerugian", "tipe" => "decimal", "required" => false, "satuan" => "Rp"]
        ]
    ],
];
