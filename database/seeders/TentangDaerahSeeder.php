<?php

namespace Database\Seeders;

use App\Models\AboutPageSetting;
use App\Models\AboutSectionContent;
use App\Models\PejabatProfile;
use App\Models\TentangOpd;
use App\Models\TentangSejarah;
use App\Models\TentangVisiMisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TentangDaerahSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            AboutPageSetting::query()->updateOrCreate(
                ['id' => 1],
                [
                    'judul_halaman' => 'Tentang Kabupaten Kepulauan Sangihe',
                    'tagline'       => "Tanah Tona'a, Tubuh Adat",
                    'banner_image'  => null,
                    'meta_description' => 'Portal informasi resmi Pemerintah Kabupaten Kepulauan Sangihe - Sulawesi Utara.',
                    'meta_keywords'    => 'Sangihe, Kabupaten, Sulawesi Utara, pemerintah daerah',
                    'updated_by'       => null,
                ]
            );

            $sections = [
                ['key' => 'sambutan_bupati', 'judul' => 'Sambutan Bupati', 'subjudul' => 'Periode 2025-2030',
                 'icon' => 'heroicon-o-megaphone', 'warna' => '#0066CC', 'urutan' => 1,
                 'ringkasan' => 'Sambutan resmi Bupati Kepulauan Sangihe untuk masyarakat.', 'konten' => ''],
                ['key' => 'visi_misi', 'judul' => 'Visi & Misi', 'subjudul' => 'Arah pembangunan daerah',
                 'icon' => 'heroicon-o-eye', 'warna' => '#10B981', 'urutan' => 2,
                 'ringkasan' => 'Visi dan misi Pemerintah Kabupaten Kepulauan Sangihe.', 'konten' => ''],
                ['key' => 'sejarah', 'judul' => 'Sejarah Singkat', 'subjudul' => 'Perjalanan panjang Kabupaten',
                 'icon' => 'heroicon-o-clock', 'warna' => '#F59E0B', 'urutan' => 3,
                 'ringkasan' => 'Sejarah singkat pembentukan Kabupaten Kepulauan Sangihe.', 'konten' => ''],
                ['key' => 'geografi', 'judul' => 'Geografi & Demografi', 'subjudul' => 'Kondisi wilayah',
                 'icon' => 'heroicon-o-map', 'warna' => '#3B82F6', 'urutan' => 4,
                 'ringkasan' => 'Letak geografis dan data demografi.', 'konten' => ''],
                ['key' => 'lambang_daerah', 'judul' => 'Lambang Daerah', 'subjudul' => 'Simbol & makna',
                 'icon' => 'heroicon-o-shield-check', 'warna' => '#DC2626', 'urutan' => 5,
                 'ringkasan' => 'Makna dan filosofi lambang daerah.', 'konten' => ''],
                ['key' => 'daftar_opd', 'judul' => 'Dinas & OPD', 'subjudul' => 'Perangkat daerah',
                 'icon' => 'heroicon-o-building-office-2', 'warna' => '#7C3AED', 'urutan' => 6,
                 'ringkasan' => 'Daftar dinas dan organisasi perangkat daerah.', 'konten' => ''],
            ];

            foreach ($sections as $s) {
                AboutSectionContent::query()->updateOrCreate(
                    ['section_key' => $s['key']],
                    [
                        'judul' => $s['judul'],
                        'subjudul' => $s['subjudul'],
                        'icon' => $s['icon'],
                        'warna_aksen' => $s['warna'],
                        'urutan' => $s['urutan'],
                        'ringkasan' => $s['ringkasan'],
                        'konten' => $s['konten'] !== '' ? $s['konten'] : 'Konten akan diisi oleh administrator.',
                        'status_publikasi' => 'published',
                        'tampil_di_menu' => true,
                        'published_at' => now(),
                    ]
                );
            }

            PejabatProfile::query()->updateOrCreate(
                ['nip' => '197001011995031001'],
                [
                    'tipe_pejabat' => 'bupati',
                    'nama_lengkap' => 'Bupati Kepulauan Sangihe',
                    'jabatan' => 'Bupati Kepulauan Sangihe',
                    'status' => 'aktif',
                    'urutan' => 1,
                    'published_at' => now(),
                    'biodata_singkat' => 'Profil bupati akan diperbarui oleh administrator.',
                ]
            );

            PejabatProfile::query()->updateOrCreate(
                ['nip' => '197001011995031002'],
                [
                    'tipe_pejabat' => 'wakil_bupati',
                    'nama_lengkap' => 'Wakil Bupati Kepulauan Sangihe',
                    'jabatan' => 'Wakil Bupati Kepulauan Sangihe',
                    'status' => 'aktif',
                    'urutan' => 2,
                    'published_at' => now(),
                    'biodata_singkat' => 'Profil wakil bupati akan diperbarui oleh administrator.',
                ]
            );

            TentangVisiMisi::query()->updateOrCreate(
                ['tipe' => 'visi', 'periode' => '2025-2030'],
                [
                    'konten' => 'Terwujudnya Kepulauan Sangihe yang Maju, Berdaya Saing, dan Sejahtera.',
                    'urutan' => 0,
                ]
            );

            $misi = [
                'Meningkatkan kualitas sumber daya manusia yang berdaya saing.',
                'Mendorong pertumbuhan ekonomi daerah berbasis potensi lokal.',
                'Memperkuat tata kelola pemerintahan yang bersih dan melayani.',
                'Meningkatkan infrastruktur wilayah kepulauan.',
                'Melestarikan budaya dan lingkungan hidup Sangihe.',
            ];
            foreach ($misi as $i => $m) {
                TentangVisiMisi::query()->updateOrCreate(
                    ['tipe' => 'misi', 'konten' => $m],
                    ['urutan' => $i, 'periode' => '2025-2030']
                );
            }

            $sejarah = [
                ['tahun' => '1620', 'judul' => 'Awal Mula Peradaban', 'deskripsi' => 'Masuknya pengaruh Kesultanan Ternate ke wilayah kepulauan Sangihe.'],
                ['tahun' => '1950', 'judul' => 'Pembentukan Kabupaten', 'deskripsi' => 'Pembentukan Kabupaten Administratif Sangihe dan Talaud.'],
                ['tahun' => '1964', 'judul' => 'Status Kabupaten Dati II', 'deskripsi' => 'Sangihe menjadi kabupaten tingkat II di Provinsi Sulawesi Utara.'],
                ['tahun' => '2000', 'judul' => 'Era Otonomi Daerah', 'deskripsi' => 'Implementasi otonomi daerah dengan UU 22/1999.'],
            ];
            foreach ($sejarah as $i => $s) {
                TentangSejarah::query()->updateOrCreate(
                    ['tahun' => $s['tahun'], 'judul_peristiwa' => $s['judul']],
                    ['deskripsi' => $s['deskripsi'], 'urutan' => $i + 1, 'tampilkan' => true]
                );
            }

            $opds = \App\Models\Opd::query()->where('aktif', true)->limit(20)->get();
            foreach ($opds as $i => $opd) {
                TentangOpd::query()->updateOrCreate(
                    ['opd_id' => $opd->id],
                    [
                        'nama_instansi' => $opd->nama,
                        'singkatan' => $opd->singkatan ?? null,
                        'slug' => Str::slug($opd->nama),
                        'kategori' => 'dinas',
                        'profil_singkat' => 'Profil ' . $opd->nama . ' akan diperbarui.',
                        'urutan' => $i + 1,
                        'tampilkan' => true,
                    ]
                );
            }
        });
    }
}
