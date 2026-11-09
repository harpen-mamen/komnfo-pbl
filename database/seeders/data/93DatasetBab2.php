<?php
return [
    "category_kode" => "1.06",
    "opds" => [
        "SETDA" => [
            "datasets" => [
                "2.1" => ["nama" => "Kepala Daerah Kabupaten Kepulauan Sangihe", "deskripsi" => "Daftar Kepala Daerah", "period_type" => "master", "fields" => [
                    "no" => ["label" => "Nomor Urut", "tipe" => "integer", "is_dimension" => true],
                    "nama" => ["label" => "Nama", "tipe" => "string"],
                    "jabatan" => ["label" => "Jabatan", "tipe" => "string"],
                    "periode_mulai" => ["label" => "Periode Mulai", "tipe" => "integer", "satuan" => "Tahun"],
                    "periode_selesai" => ["label" => "Periode Selesai", "tipe" => "integer", "satuan" => "Tahun"],
                ]],
                "2.2" => ["nama" => "Sekretaris Daerah", "period_type" => "master", "fields" => [
                    "no" => ["label" => "Nomor", "tipe" => "integer", "is_dimension" => true],
                    "nama" => ["label" => "Nama", "tipe" => "string"],
                    "jabatan" => ["label" => "Jabatan", "tipe" => "string"],
                    "nip" => ["label" => "NIP", "tipe" => "string"],
                ]],
                "2.3" => ["nama" => "Perangkat Daerah Kabupaten Kepualuan Sangihe", "period_type" => "master", "fields" => [
                    "no" => ["label" => "Nomor", "tipe" => "integer", "is_dimension" => true],
                    "kode_opd" => ["label" => "Kode OPD", "tipe" => "string"],
                    "nama_opd" => ["label" => "Nama OPD", "tipe" => "string"],
                    "kepala" => ["label" => "Kepala", "tipe" => "string"],
                    "jumlah_pegawai" => ["label" => "Jumlah Pegawai", "tipe" => "integer", "satuan" => "Orang"],
                ]],
                "2.4" => ["nama" => "Kecamatan, Kelurahan dan Kampung", "period_type" => "tahunan", "fields" => [
                    "kecamatan" => ["label" => "Kecamatan", "tipe" => "string", "is_dimension" => true, "lookup_source" => "wilayah"],
                    "jumlah_kelurahan" => ["label" => "Jumlah Kelurahan", "tipe" => "integer", "satuan" => "Kelurahan"],
                    "jumlah_kampung" => ["label" => "Jumlah Kampung", "tipe" => "integer", "satuan" => "Kampung"],
                    "jumlah_dusun" => ["label" => "Jumlah Dusun", "tipe" => "integer", "satuan" => "Dusun"],
                ]],
            ],
        ],
        "SETWAN" => [
            "category_kode" => "1.06",
            "datasets" => [
                "2.5" => ["nama" => "Anggota DPRD Menurut Jenis Kelamin dan Tingkat Pendidikan", "period_type" => "master", "fields" => [
                    "jenis_kelamin" => ["label" => "Jenis Kelamin", "tipe" => "enum", "options" => ["L", "P"], "is_dimension" => true],
                    "pendidikan" => ["label" => "Pendidikan", "tipe" => "enum", "options" => ["SD", "SMP", "SMA", "D3", "S1", "S2", "S3"], "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                ]],
                "2.6" => ["nama" => "Anggota DPRD Menurut Daerah Pemilihan dan Partai Politik", "period_type" => "master", "fields" => [
                    "daerah_pemilihan" => ["label" => "Daerah Pemilihan", "tipe" => "string", "is_dimension" => true],
                    "partai_politik" => ["label" => "Partai Politik", "tipe" => "string", "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                ]],
                "2.7" => ["nama" => "Keputusan DPRD", "period_type" => "tahunan", "fields" => [
                    "jenis" => ["label" => "Jenis Keputusan", "tipe" => "string", "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Dokumen"],
                    "tahun" => ["label" => "Tahun", "tipe" => "integer", "is_dimension" => true],
                ]],
            ],
        ],
        "BKPSDMD" => [
            "category_kode" => "2.18",
            "datasets" => [
                "2.8" => ["nama" => "PNS Berdasarkan Golongan Ruang", "period_type" => "tahunan", "fields" => [
                    "golongan" => ["label" => "Golongan", "tipe" => "enum", "options" => ["I", "II", "III", "IV"], "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                    "tahun" => ["label" => "Tahun", "tipe" => "integer", "is_dimension" => true],
                ]],
                "2.9" => ["nama" => "PNS Berdasarkan Eselon", "period_type" => "tahunan", "fields" => [
                    "eselon" => ["label" => "Eselon", "tipe" => "enum", "options" => ["I", "II", "III", "IV", "Non-Eselon"], "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                    "tahun" => ["label" => "Tahun", "tipe" => "integer", "is_dimension" => true],
                ]],
                "2.10" => ["nama" => "PNS Berdasarkan Jabatan", "period_type" => "tahunan", "fields" => [
                    "jabatan" => ["label" => "Jabatan", "tipe" => "enum", "options" => ["Struktural", "Fungsional", "Pelaksana"], "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                    "tahun" => ["label" => "Tahun", "tipe" => "integer", "is_dimension" => true],
                ]],
                "2.11" => ["nama" => "PNS Berdasarkan Pendidikan", "period_type" => "tahunan", "fields" => [
                    "pendidikan" => ["label" => "Pendidikan", "tipe" => "enum", "options" => ["SD", "SMP", "SMA", "D3", "S1", "S2", "S3"], "is_dimension" => true],
                    "jumlah" => ["label" => "Jumlah", "tipe" => "integer", "satuan" => "Orang"],
                    "tahun" => ["label" => "Tahun", "tipe" => "integer", "is_dimension" => true],
                ]],
            ],
        ],
    ],
];
