import Link from 'next/link';
import type { Metadata } from 'next';
import { fetchTentangDaerah } from '@/lib/api/tentang-daerah';
import { fetchVisiMisi } from '@/lib/api/tentang-daerah';
import SambutanSection from '@/components/tentang/SambutanSection';
import VisiMisiSection from '@/components/tentang/VisiMisiSection';
import PejabatSection from '@/components/tentang/PejabatSection';
import OpdSection from '@/components/tentang/OpdSection';
import SejarahSection from '@/components/tentang/SejarahSection';
import { Compass, ShieldCheck } from 'lucide-react';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Tentang Daerah',
  description: 'Informasi lengkap tentang Kabupaten Kepulauan Sangihe - Sambutan Bupati, Profil Pejabat, Visi Misi, Sejarah, dan Perangkat Daerah.',
};

export default async function TentangDaerahPage() {
  const [tentang, vm] = await Promise.all([fetchTentangDaerah().catch(() => null), fetchVisiMisi().catch(() => null)]);

  return (
    <main>
      {/* HERO */}
      <section className="relative overflow-hidden bg-linear-to-br from-primary-700 via-primary-600 to-primary-800 text-white">
        <div className="absolute inset-0 bg-linear-to-br from-white/5 to-transparent" />
        <div className="container-page relative py-16 md:py-24">
          <nav className="text-sm text-primary-100">
            <Link href="/" className="hover:text-white">Beranda</Link> <span className="mx-2">/</span>
            <span className="text-white">Tentang Daerah</span>
          </nav>
          <h1 className="heading-xl mt-6">{tentang?.page?.judul_halaman ?? 'Tentang Kabupaten Kepulauan Sangihe'}</h1>
          {tentang?.page?.tagline && <p className="mt-4 max-w-2xl text-lg text-primary-100">{tentang.page.tagline}</p>}

          {/* Quick nav */}
          <div className="mt-10 flex flex-wrap gap-3">
            {(tentang?.sections ?? [
              { id: 1, section_key: 'sambutan_bupati', judul: 'Sambutan', subjudul: null, icon: null, warna_aksen: null, urutan: 0 },
              { id: 2, section_key: 'visi_misi', judul: 'Visi & Misi', subjudul: null, icon: null, warna_aksen: null, urutan: 1 },
              { id: 3, section_key: 'sejarah', judul: 'Sejarah', subjudul: null, icon: null, warna_aksen: null, urutan: 2 },
            ]).map((s) => (
              <a key={s.id} href={`#${s.section_key}`} className="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur transition hover:bg-white/20">
                {s.judul}
              </a>
            ))}
            <Link href="/tentang-daerah/geografi" className="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur transition hover:bg-white/20">Geografi</Link>
            <Link href="/tentang-daerah/lambang" className="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur transition hover:bg-white/20">Lambang Daerah</Link>
          </div>
        </div>
      </section>

      {/* Sambutan */}
      <section id="sambutan_bupati" className="section-padding">
        <div className="container-page">
          <SambutanSection />
        </div>
      </section>

      {/* Visi Misi */}
      {vm && (
        <section id="visi_misi" className="section-padding bg-slate-50">
          <div className="container-page">
            <VisiMisiSection data={vm} />
          </div>
        </section>
      )}

      {/* Pejabat */}
      <section id="pejabat" className="section-padding">
        <div className="container-page">
          <PejabatSection />
        </div>
      </section>

      {/* Sejarah */}
      <section id="sejarah" className="section-padding bg-slate-50">
        <div className="container-page">
          <SejarahSection />
        </div>
      </section>

      {/* OPD */}
      <section id="daftar_opd" className="section-padding">
        <div className="container-page">
          <OpdSection />
        </div>
      </section>

      <section className="section-padding bg-slate-50">
        <div className="container-page grid gap-4 md:grid-cols-2">
          <Link href="/tentang-daerah/geografi" className="group flex items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span className="rounded-xl bg-primary-50 p-4 text-primary-700"><Compass className="h-7 w-7" /></span>
            <span><span className="block text-lg font-semibold text-slate-900">Geografi Sangihe</span><span className="mt-1 block text-sm text-slate-600">Kenali karakter wilayah kepulauan dan pusat pemerintahan.</span></span>
          </Link>
          <Link href="/tentang-daerah/lambang" className="group flex items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span className="rounded-xl bg-amber-50 p-4 text-amber-700"><ShieldCheck className="h-7 w-7" /></span>
            <span><span className="block text-lg font-semibold text-slate-900">Lambang Daerah</span><span className="mt-1 block text-sm text-slate-600">Baca makna unsur dan warna lambang Kabupaten.</span></span>
          </Link>
        </div>
      </section>
    </main>
  );
}
