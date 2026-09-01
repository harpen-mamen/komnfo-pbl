# Rancangan UI/UX Portal Publik SIPDT Sangihe

## Acuan visual

Rancangan mengikuti artefak Stitch yang disediakan untuk Beranda, Peta Data, Statistik Publik, dan Berita. Tampilan desktop adalah adaptasi responsif dari komposisi mobile, bukan desain baru yang terpisah.

## Design system

| Elemen | Ketentuan |
|---|---|
| Font judul | Poppins 600–700 |
| Font isi | Inter 400–600 |
| Primary | `#00355F` |
| Secondary | `#006D3D` |
| Latar | `#F7F9FC` |
| Surface | `#FFFFFF` |
| Radius kartu | 12–16px |
| Border | `#E0E3E6` |
| Shadow | halus, warna navy dengan opacity rendah |

## Struktur global

- Mobile: app bar putih, bottom navigation lima menu.
- Desktop: header putih dengan logo, navigasi horizontal, tombol Portal Admin; bottom navigation dihilangkan.
- Semua halaman memakai grid maksimum 1280px, gutter 24px, dan section gap 48px.

## Beranda

- Hero foto/video lebar, overlay navy, CTA Jelajahi Data dan Statistik.
- Empat kartu statistik mengambang di bawah hero.
- Grid menu Profil, Layanan, Dokumen, Transparansi.
- Section Sebaran Wilayah dengan peta ringkas dan CTA Peta Interaktif.
- Section Berita Terbaru dengan kartu artikel.

## Peta Data

- Search bar terapung di atas peta.
- Filter pill Penduduk, Rumah, Stunting, dan Penyakit.
- Basemap Street, Satellite, Topografi.
- Batas kecamatan/desa, popup ringkas, dan legenda.
- Mobile: panel informasi bawah; desktop: panel kanan tanpa menutupi peta.

## Statistik

- Filter tahun dan wilayah di atas.
- KPI: total penduduk, IPM, laju ekonomi, kemiskinan.
- Chart demografi, pendidikan, ketenagakerjaan, stunting, dan komoditas.
- Data hanya agregat terverifikasi; tanpa informasi pribadi.

## Berita

- Tab kategori.
- Kartu berita dengan gambar, kategori, tanggal, judul, ringkasan, dan CTA.
- Desktop: featured article + grid tiga kolom.

## Integrasi admin

- Foto/video Hero diatur Admin Kominfo.
- Berita dikelola Admin Kominfo.
- KPI, chart, dan peta membaca data yang telah disetujui pada batch.
- Peta publik hanya memuat statistik agregat per wilayah.
