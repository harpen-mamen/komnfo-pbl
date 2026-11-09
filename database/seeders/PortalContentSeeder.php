<?php

namespace Database\Seeders;

use App\Models\PortalContent;
use Illuminate\Database\Seeder;

class PortalContentSeeder extends Seeder
{
    public function run(): void
    {
        PortalContent::query()->firstOrCreate(['type' => 'hero', 'slug' => 'beranda-sangihe'], [
            'title' => 'Kabupaten Kepulauan Sangihe',
            'summary' => 'Akses data publik, peta sebaran, berita, dan informasi resmi Pemerintah Kabupaten Kepulauan Sangihe, Sulawesi Utara.',
            'body' => 'Tanah Tona’a, Tubuh Adat',
            'sort_order' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $services = [
            ['penerbitan-surat', 'Penerbitan Surat Keterangan', 'Informasi layanan surat keterangan dan administrasi pada perangkat daerah terkait.', 'document'],
            ['pengaduan-masyarakat', 'Pengaduan Masyarakat', 'Informasi kanal pengaduan dan aspirasi masyarakat yang dikelola Pemerintah Daerah.', 'megaphone'],
            ['permohonan-data', 'Permohonan Data', 'Informasi permohonan dan akses data terbuka sesuai ketentuan yang berlaku.', 'database'],
            ['konsultasi-publik', 'Konsultasi Publik', 'Informasi konsultasi dengan perangkat daerah sesuai bidang layanan.', 'chat'],
        ];

        foreach ($services as $index => [$slug, $title, $summary, $icon]) {
            PortalContent::query()->firstOrCreate(['type' => 'service', 'slug' => $slug], [
                'title' => $title,
                'summary' => $summary,
                'category' => $icon,
                'sort_order' => $index + 1,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
