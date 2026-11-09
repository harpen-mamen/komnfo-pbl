<?php

namespace Database\Seeders;

use App\Models\PortalContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SangiheOfficialNewsSeeder extends Seeder
{
    /**
     * Curated, dated entries from sangihekab.go.id. Article links and dates are
     * preserved so readers can open the complete original publication.
     */
    public function run(): void
    {
        if (! Schema::hasTable('portal_contents')) {
            return;
        }

        $articles = [
            [
                'slug' => 'bupati-pimpin-apel-kerja-bersama-asn-november-2026',
                'title' => 'Bupati Pimpin Apel Kerja Bersama ASN November 2026, Tegaskan Evaluasi APBD dan Pelayanan Tanpa Ego Sektoral',
                'summary' => 'Bupati Michael Thungari meminta perangkat daerah mengevaluasi pelaksanaan APBD 2026, menuntaskan pekerjaan sesuai jadwal, dan meningkatkan layanan publik melalui koordinasi lintas perangkat daerah.',
                'body' => "Bupati Kepulauan Sangihe Michael Thungari, S.E., M.M. memimpin Apel Kerja Bersama ASN Bulan November 2026 di Lapangan Gelora Santiago. Dalam arahannya, ia meminta administrasi kepegawaian berjalan tertib, tepat waktu, dan transparan serta pengembangan karier ASN dilakukan secara objektif.\n\nMemasuki penghujung tahun anggaran, seluruh perangkat daerah diminta mengevaluasi APBD induk dan perubahan tahun 2026, menyelesaikan hambatan administratif maupun teknis, serta menuntaskan pekerjaan sesuai jadwal dengan tetap menjaga kualitas.\n\nUntuk pelayanan publik, Bupati meminta layanan yang cepat, mudah, transparan, dan responsif. Ia juga menekankan koordinasi tanpa ego sektoral dan pemanfaatan teknologi untuk menjawab tantangan geografis wilayah kepulauan.\n\nSumber: https://sangihekab.go.id/2026/11/05/bupati-kepulauan-sangihe-pimpin-apel-kerja-bersama-asn-bulan-november-2026-tegaskan-evaluasi-apbd-dan-pelayanan-tanpa-egosektoral/",
                'category' => 'Pemerintahan',
                'published_at' => '2026-11-05 09:00:00',
                'image_path' => 'https://sangihekab.go.id/wp-content/uploads/2026/11/IMG-20261105-WA0001.jpg',
                'source_url' => 'https://sangihekab.go.id/2026/11/05/bupati-kepulauan-sangihe-pimpin-apel-kerja-bersama-asn-bulan-november-2026-tegaskan-evaluasi-apbd-dan-pelayanan-tanpa-egosektoral/',
            ],
            [
                'slug' => 'lima-pejabat-pemprov-uji-kompetensi-calon-sekda-sangihe',
                'title' => 'Lima Pejabat Pemprov Sulut Uji Kompetensi Enam Calon Sekda Kabupaten Kepulauan Sangihe',
                'summary' => 'Seleksi calon Sekretaris Daerah Sangihe memasuki tahap wawancara kompetensi teknis oleh Komite Talenta yang terdiri dari lima pejabat Pemerintah Provinsi Sulawesi Utara.',
                'body' => "Seleksi terbuka Jabatan Pimpinan Tinggi Pratama Sekretaris Daerah Kabupaten Kepulauan Sangihe memasuki tahap wawancara kompetensi teknis pada 30 Oktober 2026. Enam calon mengikuti tahapan untuk mengukur kapasitas, kompetensi, integritas, dan kemampuan kepemimpinan.\n\nKomite Talenta terdiri dari lima pejabat Pemerintah Provinsi Sulawesi Utara. Proses ini diharapkan membantu memperoleh calon Sekretaris Daerah yang mampu mengoordinasikan perangkat daerah, mendukung tata kelola pemerintahan, serta meningkatkan pelayanan kepada masyarakat.\n\nSumber: https://sangihekab.go.id/2026/10/30/lima-pejabat-pemprov-sulut-uji-kompetensi-enam-calon-sekda-kabupaten-kepulauan-sangihe/",
                'category' => 'Pemerintahan',
                'published_at' => '2026-10-30 09:00:00',
                'image_path' => 'https://sangihekab.go.id/wp-content/uploads/2026/10/IMG-20261030-WA0001.jpg',
                'source_url' => 'https://sangihekab.go.id/2026/10/30/lima-pejabat-pemprov-sulut-uji-kompetensi-enam-calon-sekda-kabupaten-kepulauan-sangihe/',
            ],
            [
                'slug' => 'wakil-bupati-hadiri-rups-luar-biasa-bank-sulutgo-2026',
                'title' => 'Wakil Bupati Tendris Bulahari Hadiri RUPS Luar Biasa Bank SulutGo di Manado',
                'summary' => 'RUPS Luar Biasa Bank SulutGo membahas penyesuaian susunan pengurus dan nomenklatur direksi agar selaras dengan ketentuan OJK.',
                'body' => "Wakil Bupati Kepulauan Sangihe Tendris Bulahari menghadiri Rapat Umum Pemegang Saham Luar Biasa PT Bank Pembangunan Daerah Sulawesi Utara Gorontalo di Manado pada 29 Oktober 2026.\n\nRapat membahas penataan susunan pengurus perseroan dan penyesuaian nomenklatur sejumlah jabatan direksi. Langkah tersebut ditujukan untuk menyelaraskan tata kelola dan fungsi organisasi dengan ketentuan yang berlaku.\n\nKehadiran Wakil Bupati menjadi bagian dari keterlibatan Pemerintah Kabupaten Kepulauan Sangihe dalam forum pemegang saham Bank SulutGo, bank pembangunan daerah yang mendukung aktivitas ekonomi dan pembangunan di Sulawesi Utara dan Gorontalo.\n\nSumber: https://sangihekab.go.id/2026/10/29/wakil-bupati-kepulauan-sangihe-tendris-bulahari-hadiri-rups-luar-biasa-bank-sulutgo-di-manado/",
                'category' => 'Ekonomi',
                'published_at' => '2026-10-29 09:00:00',
                'image_path' => 'https://sangihekab.go.id/wp-content/uploads/2026/10/IMG-20261029-WA0024.jpg',
                'source_url' => 'https://sangihekab.go.id/2026/10/29/wakil-bupati-kepulauan-sangihe-tendris-bulahari-hadiri-rups-luar-biasa-bank-sulutgo-di-manado/',
            ],
            [
                'slug' => 'enam-calon-sekda-ikuti-presentasi-wawancara-2026',
                'title' => 'Enam Calon Sekda Sangihe Ikuti Presentasi dan Wawancara',
                'summary' => 'Enam calon Sekretaris Daerah mengikuti tahapan presentasi dan wawancara sebagai bagian seleksi Jabatan Pimpinan Tinggi Pratama.',
                'body' => "Pada 28 Oktober 2026, enam calon Sekretaris Daerah Kabupaten Kepulauan Sangihe mengikuti tahapan presentasi dan wawancara yang diuji oleh Asesor Utama BKN RI. Tahapan ini memberi kesempatan kepada peserta untuk menunjukkan kemampuan, potensi, dan kesiapan mengemban tugas sebagai Sekretaris Daerah.\n\nSeleksi diharapkan menghasilkan tiga nama terbaik untuk direkomendasikan kepada pimpinan daerah. Proses tersebut merupakan bagian dari upaya memperkuat tata kelola pemerintahan dan mendukung pelaksanaan program pembangunan.\n\nSumber: https://sangihekab.go.id/2026/10/28/enam-calon-sekda-sangihe-ikuti-presentasi-dan-wawancara-tiga-nama-terbaik-akan-direkomendasikan-kepada-pimpinan-daerah/",
                'category' => 'Pemerintahan',
                'published_at' => '2026-10-28 09:00:00',
                'image_path' => 'https://sangihekab.go.id/wp-content/uploads/2026/10/IMG-20261028-WA0001.jpg',
                'source_url' => 'https://sangihekab.go.id/2026/10/28/enam-calon-sekda-sangihe-ikuti-presentasi-dan-wawancara-tiga-nama-terbaik-akan-direkomendasikan-kepada-pimpinan-daerah/',
            ],
            [
                'slug' => 'wakil-bupati-buka-assessment-calon-sekda-2026',
                'title' => 'Wakil Bupati Buka Assessment dan Seleksi Kompetensi Teknis Calon Sekda Tahun 2026',
                'summary' => 'Assessment calon Sekda digelar untuk mengukur kompetensi dan potensi peserta secara objektif dalam rangka memperkuat tata kelola dan pelayanan publik.',
                'body' => "Wakil Bupati Kepulauan Sangihe Tendris Bulahari membuka Assessment Test dan Seleksi Kompetensi Teknis calon Sekretaris Daerah Kabupaten Kepulauan Sangihe di Kantor Regional XI BKN Manado pada 27 Oktober 2026.\n\nKegiatan tersebut dihadiri Kepala Kantor Regional XI BKN Manado, Sekretaris Daerah Kabupaten Kepulauan Sangihe, asesor, dan para peserta. Assessment menggunakan simulasi dan instrumen penilaian untuk mengukur kompetensi secara objektif dan terukur.\n\nWakil Bupati menegaskan bahwa seleksi harus menjadi sarana untuk membuktikan kompetensi, kapasitas, dan kesiapan peserta, serta dilaksanakan secara profesional dan sesuai ketentuan.\n\nSumber: https://sangihekab.go.id/2026/10/27/wakil-bupati-sangihe-buka-assessment-dan-seleksi-kompetensi-teknis-calon-sekda-tahun-2026/",
                'category' => 'Pemerintahan',
                'published_at' => '2026-10-27 09:00:00',
                'image_path' => 'https://sangihekab.go.id/wp-content/uploads/2026/10/IMG-20261027-WA0001.jpg',
                'source_url' => 'https://sangihekab.go.id/2026/10/27/wakil-bupati-sangihe-buka-assessment-dan-seleksi-kompetensi-teknis-calon-sekda-tahun-2026/',
            ],
        ];

        foreach ($articles as $order => $article) {
            $sourceUrl = $article['source_url'];
            unset($article['source_url']);

            PortalContent::query()->updateOrCreate(
                ['type' => 'news', 'slug' => $article['slug']],
                array_merge($article, [
                    'body' => $article['body']."\n\nBaca publikasi asli: {$sourceUrl}",
                    'year' => 2026,
                    'sort_order' => $order + 1,
                    'is_published' => true,
                ])
            );
        }
    }
}
