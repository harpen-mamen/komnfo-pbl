<?php

namespace Database\Seeders;

use App\Models\ObjekLapanganType;
use Illuminate\Database\Seeder;

/**
 * Seed Objek Lapangan Types spesifik untuk Kabupaten Kepulauan Sangihe.
 * Termasuk tipe khas Sangihe: pelabuhan rakyat, kapal, TPI,
 * pulau kecil terluar, monumen batas, dst.
 */
class ObjekLapanganTypeSangiheSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Umum
            ['kode' => 'RUMAH', 'nama' => 'Rumah', 'geometry_type' => 'point', 'icon' => 'home', 'color' => '#10b981', 'kategori' => 'umum'],
            ['kode' => 'KANTOR', 'nama' => 'Kantor Pemerintahan', 'geometry_type' => 'point', 'icon' => 'building-office', 'color' => '#3b82f6', 'kategori' => 'umum'],

            // Pendidikan
            ['kode' => 'SEKOLAH', 'nama' => 'Sekolah', 'geometry_type' => 'point', 'icon' => 'academic-cap', 'color' => '#f59e0b', 'kategori' => 'pendidikan'],
            ['kode' => 'PERPUSTAKAAN', 'nama' => 'Perpustakaan', 'geometry_type' => 'point', 'icon' => 'book-open', 'color' => '#a855f7', 'kategori' => 'pendidikan'],
            ['kode' => 'SARANA_OLAHRAGA', 'nama' => 'Sarana Olahraga', 'geometry_type' => 'point', 'icon' => 'trophy', 'color' => '#ec4899', 'kategori' => 'pemuda'],

            // Kesehatan
            ['kode' => 'PUSKESMAS', 'nama' => 'Puskesmas', 'geometry_type' => 'point', 'icon' => 'plus-circle', 'color' => '#ef4444', 'kategori' => 'kesehatan'],
            ['kode' => 'RUMAH_SAKIT', 'nama' => 'Rumah Sakit', 'geometry_type' => 'point', 'icon' => 'plus', 'color' => '#dc2626', 'kategori' => 'kesehatan'],
            ['kode' => 'POSYANDU', 'nama' => 'Posyandu', 'geometry_type' => 'point', 'icon' => 'heart', 'color' => '#f472b6', 'kategori' => 'kesehatan'],
            ['kode' => 'APOTEK', 'nama' => 'Apotek', 'geometry_type' => 'point', 'icon' => 'beaker', 'color' => '#fb7185', 'kategori' => 'kesehatan'],

            // PUPR
            ['kode' => 'JALAN', 'nama' => 'Jalan Kabupaten', 'geometry_type' => 'linestring', 'icon' => 'truck', 'color' => '#475569', 'kategori' => 'pu'],
            ['kode' => 'JEMBATAN', 'nama' => 'Jembatan', 'geometry_type' => 'point', 'icon' => 'minus', 'color' => '#64748b', 'kategori' => 'pu'],
            ['kode' => 'DRAINASE', 'nama' => 'Drainase', 'geometry_type' => 'linestring', 'icon' => 'water', 'color' => '#0ea5e9', 'kategori' => 'pu'],
            ['kode' => 'IRIGASI', 'nama' => 'Saluran Irigasi', 'geometry_type' => 'linestring', 'icon' => 'sparkles', 'color' => '#06b6d4', 'kategori' => 'pu'],
            ['kode' => 'MATA_AIR', 'nama' => 'Sumber Mata Air', 'geometry_type' => 'point', 'icon' => 'fire', 'color' => '#22d3ee', 'kategori' => 'pu'],
            ['kode' => 'TANGKI_AIR_BERSIH', 'nama' => 'Tangki Air Bersih', 'geometry_type' => 'point', 'icon' => 'cube', 'color' => '#3b82f6', 'kategori' => 'pu'],
            ['kode' => 'RUSUN', 'nama' => 'Rumah Susun', 'geometry_type' => 'polygon', 'icon' => 'building', 'color' => '#0891b2', 'kategori' => 'perkim'],
            ['kode' => 'PERUMAHAN', 'nama' => 'Perumahan', 'geometry_type' => 'polygon', 'icon' => 'home-modern', 'color' => '#0d9488', 'kategori' => 'perkim'],

            // Lingkungan
            ['kode' => 'TPS', 'nama' => 'Tempat Penampungan Sampah', 'geometry_type' => 'point', 'icon' => 'trash', 'color' => '#16a34a', 'kategori' => 'lingkungan'],
            ['kode' => 'TPA', 'nama' => 'Tempat Pemrosesan Akhir', 'geometry_type' => 'polygon', 'icon' => 'archive', 'color' => '#15803d', 'kategori' => 'lingkungan'],
            ['kode' => 'RTH', 'nama' => 'Ruang Terbuka Hijau', 'geometry_type' => 'polygon', 'icon' => 'leaf', 'color' => '#22c55e', 'kategori' => 'lingkungan'],

            // Sosial
            ['kode' => 'PANTI_SOSIAL', 'nama' => 'Panti Sosial', 'geometry_type' => 'point', 'icon' => 'users', 'color' => '#f97316', 'kategori' => 'sosial'],
            ['kode' => 'POS_PAMONG', 'nama' => 'Pos Pamong Praja', 'geometry_type' => 'point', 'icon' => 'shield-check', 'color' => '#ea580c', 'kategori' => 'sosial'],
            ['kode' => 'MOBIL_DAMKAR', 'nama' => 'Pos Pemadam Kebakaran', 'geometry_type' => 'point', 'icon' => 'fire', 'color' => '#dc2626', 'kategori' => 'sosial'],

            // Perhubungan (khas Sangihe - kepulauan)
            ['kode' => 'PELABUHAN', 'nama' => 'Pelabuhan', 'geometry_type' => 'point', 'icon' => 'paper-airplane', 'color' => '#1e40af', 'kategori' => 'perhubungan'],
            ['kode' => 'DERMAGA', 'nama' => 'Dermaga', 'geometry_type' => 'point', 'icon' => 'minus-circle', 'color' => '#2563eb', 'kategori' => 'perhubungan'],
            ['kode' => 'PELABUHAN_RAKYAT', 'nama' => 'Pelabuhan Rakyat (TPI)', 'geometry_type' => 'point', 'icon' => 'anchor', 'color' => '#0369a1', 'kategori' => 'perhubungan'],
            ['kode' => 'HALTE', 'nama' => 'Halte Angkutan Umum', 'geometry_type' => 'point', 'icon' => 'truck', 'color' => '#1d4ed8', 'kategori' => 'perhubungan'],
            ['kode' => 'TERMINAL', 'nama' => 'Terminal', 'geometry_type' => 'point', 'icon' => 'arrows-right-left', 'color' => '#1e3a8a', 'kategori' => 'perhubungan'],
            ['kode' => 'RAMBU_LALIN', 'nama' => 'Rambu Lalu Lintas', 'geometry_type' => 'point', 'icon' => 'exclamation-triangle', 'color' => '#facc15', 'kategori' => 'perhubungan'],

            // Kominfo
            ['kode' => 'MENARA_TELEKOM', 'nama' => 'Menara Telekomunikasi', 'geometry_type' => 'point', 'icon' => 'signal', 'color' => '#7c3aed', 'kategori' => 'kominfo'],
            ['kode' => 'WIFI_PUBLIK', 'nama' => 'Akses WiFi Publik', 'geometry_type' => 'point', 'icon' => 'wifi', 'color' => '#8b5cf6', 'kategori' => 'kominfo'],

            // Perbatasan (khas Sangihe)
            ['kode' => 'PULAU_KECIL', 'nama' => 'Pulau Kecil Terluar', 'geometry_type' => 'point', 'icon' => 'map', 'color' => '#7f1d1d', 'kategori' => 'perbatasan'],
            ['kode' => 'MERCUSUAR', 'nama' => 'Mercusuar / Menara Suar', 'geometry_type' => 'point', 'icon' => 'light-bulb', 'color' => '#fde047', 'kategori' => 'perbatasan'],
            ['kode' => 'MOMEN_BATAS', 'nama' => 'Monumen Batas Negara', 'geometry_type' => 'point', 'icon' => 'flag', 'color' => '#b91c1c', 'kategori' => 'perbatasan'],
            ['kode' => 'POS_PENGAMANAN', 'nama' => 'Pos Pengamanan Perbatasan', 'geometry_type' => 'point', 'icon' => 'shield-exclamation', 'color' => '#991b1b', 'kategori' => 'perbatasan'],

            // Pariwisata
            ['kode' => 'DESTINASI_WISATA', 'nama' => 'Destinasi Wisata', 'geometry_type' => 'point', 'icon' => 'camera', 'color' => '#14b8a6', 'kategori' => 'pariwisata'],
            ['kode' => 'PANTAI', 'nama' => 'Pantai', 'geometry_type' => 'linestring', 'icon' => 'sun', 'color' => '#0d9488', 'kategori' => 'pariwisata'],
            ['kode' => 'TERUMBU_KARANG', 'nama' => 'Lokasi Terumbu Karang', 'geometry_type' => 'polygon', 'icon' => 'sparkles', 'color' => '#06b6d4', 'kategori' => 'pariwisata'],
            ['kode' => 'SITE_BUDAYA', 'nama' => 'Situs Cagar Budaya', 'geometry_type' => 'point', 'icon' => 'library', 'color' => '#7c2d12', 'kategori' => 'pariwisata'],
            ['kode' => 'HOTEL', 'nama' => 'Hotel/Penginapan', 'geometry_type' => 'point', 'icon' => 'building-office-2', 'color' => '#5b21b6', 'kategori' => 'pariwisata'],

            // Perdagangan
            ['kode' => 'PASAR', 'nama' => 'Pasar Tradisional', 'geometry_type' => 'polygon', 'icon' => 'shopping-bag', 'color' => '#9f1239', 'kategori' => 'perdagangan'],
            ['kode' => 'TKBM', 'nama' => 'Tempat Pelelangan Ikan (TPI)', 'geometry_type' => 'point', 'icon' => 'scale', 'color' => '#0891b2', 'kategori' => 'perdagangan'],

            // Pertanian & Perkebunan
            ['kode' => 'LAHAN_PERTANIAN', 'nama' => 'Lahan Pertanian', 'geometry_type' => 'polygon', 'icon' => 'globe-alt', 'color' => '#65a30d', 'kategori' => 'pertanian'],
            ['kode' => 'KELOMPOK_TANI', 'nama' => 'Lokasi Kelompok Tani', 'geometry_type' => 'point', 'icon' => 'user-group', 'color' => '#84cc16', 'kategori' => 'pertanian'],
            ['kode' => 'PABRIK', 'nama' => 'Pabrik / UMKM', 'geometry_type' => 'point', 'icon' => 'cog', 'color' => '#737373', 'kategori' => 'industri'],
            ['kode' => 'KOPERASI', 'nama' => 'Koperasi', 'geometry_type' => 'point', 'icon' => 'briefcase', 'color' => '#a3a3a3', 'kategori' => 'ekonomi'],

            // Keagamaan & Sosial
            ['kode' => 'RUMAH_IBADAH', 'nama' => 'Rumah Ibadah', 'geometry_type' => 'point', 'icon' => 'star', 'color' => '#fbbf24', 'kategori' => 'sosial'],
            ['kode' => 'MAKAM', 'nama' => 'TPU / Makam', 'geometry_type' => 'polygon', 'icon' => 'minus-circle', 'color' => '#525252', 'kategori' => 'sosial'],
            ['kode' => 'PASAR_HEWAN', 'nama' => 'Pasar Hewan', 'geometry_type' => 'point', 'icon' => 'shopping-cart', 'color' => '#b45309', 'kategori' => 'pertanian'],
        ];

        foreach ($types as $i => $row) {
            ObjekLapanganType::updateOrCreate(
                ['kode' => $row['kode']],
                [
                    'nama' => $row['nama'],
                    'geometry_type' => $row['geometry_type'],
                    'icon' => $row['icon'],
                    'color' => $row['color'],
                    'is_active' => true,
                    'urutan' => $i,
                ]
            );
        }
    }
}
