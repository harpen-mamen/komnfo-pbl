<?php

// Coordinates as reported by primary public sources, reviewed 2026-10-25.
// A source point is not a surveyed footprint or a route suitable for navigation.
return [
    [
        'opd'=>'DISHUB', 'code'=>'LOKASI_TRANSPORTASI_PUBLIK', 'name'=>'Bandar Udara Naha', 'kind'=>'Bandara',
        'latitude'=>3 + 41/60 + 8/3600, 'longitude'=>125 + 31/60 + 45/3600,
        'source_url'=>'https://hubud.kemenhub.go.id/bandara/31',
        'reference'=>'Direktorat Jenderal Perhubungan Udara; koordinat ARP 03°41\'08" LU 125°31\'45" BT. Titik referensi bandara, bukan pintu masuk terminal.',
    ],
    [
        'opd'=>'DISHUB', 'code'=>'LOKASI_TRANSPORTASI_PUBLIK', 'name'=>'Pelabuhan Tahuna', 'kind'=>'Pelabuhan',
        'latitude'=>3.60119629, 'longitude'=>125.50158691,
        'source_url'=>'https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/SARANA_PRASARANA/MapServer/1',
        'reference'=>'BIG Satu Peta: Peta Sebaran Pelabuhan Umum, objectid 501, metadata PelabuhanUmum.xls. Tahun survei sumber tidak dicantumkan.',
    ],
    [
        'opd'=>'DINKES', 'code'=>'LOKASI_FASKES_PUBLIK', 'name'=>'Puskesmas Kaluwatu', 'kind'=>'Puskesmas',
        'latitude'=>3.4386328, 'longitude'=>125.5823597,
        'source_url'=>'https://santertb.sangihekab.go.id/faskes/1',
        'reference'=>'Koordinat yang ditampilkan portal SANTER TB Pemkab Sangihe. Titik rujukan publik; perlu konfirmasi lapangan sebelum dipakai sebagai inventaris aset.',
    ],
    [
        'opd'=>'DINKES', 'code'=>'LOKASI_FASKES_PUBLIK', 'name'=>'RSUD Liun Kendage', 'kind'=>'Rumah sakit',
        'latitude'=>3.6082851, 'longitude'=>125.4782947,
        'source_url'=>'https://santertb.sangihekab.go.id/faskes/2',
        'reference'=>'Koordinat yang ditampilkan portal SANTER TB Pemkab Sangihe. Identitas fasilitas dikonfirmasi pada https://sirs.kemkes.go.id/fo/home/profile_rs/7103015; bukan hasil survei batas bangunan.',
    ],
];
