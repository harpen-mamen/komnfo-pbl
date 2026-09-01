import type { Metadata } from 'next';
import { fetchTentangDaerah } from '@/lib/api/tentang-daerah';
import { fetchVisiMisi } from '@/lib/api/tentang-daerah';
import SambutanSection from '@/components/tentang/SambutanSection';
import VisiMisiSection from '@/components/tentang/VisiMisiSection';
import PejabatSection from '@/components/tentang/PejabatSection';
import OpdSection from '@/components/tentang/OpdSection';
import SejarahSection from '@/components/tentang/SejarahSection';

export const revalidate = 600;

export const metadata: Metadata = {
  title: 'Tentang Daerah',
  description: 'Informasi lengkap tentang Kabupaten Kepulauan Sangihe - Sambutan Bupati, Profil Pejabat, Visi Misi, Sejarah, dan Perangkat Daerah.',
};

export default async function TentangDaerahPage() {
  const [tentang, vm] = await Promise.all([fetchTentangDaerah().catch(() => null), fetchVisiMisi().catch(() => null)]);

  return (
    <main>
      {/* HERO */}
      <section className="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 text-white">
        <div className="absolute inset-0 bg-[url('/pattern-batik.svg')] opacity-10"></div>
        <div className="container-page relative py-16 md:py-24">
          <nav className="text-sm text-primary-100">
            <a href="/" className="hover:text-white">Beranda</a> <span className="mx-2">/</span>
            <span className="text-white">Tentang Daerah</span>
          </nav>
          <h1 className="heading-xl mt-6">{tentang?.page.judul_halaman ?? 'Tentang Kabupaten Kepulauan Sangihe'}</h1>
          <p className="mt-4 max-w-2xl text-lg text-primary-100">{tentang?.page.tagline}</p>

          {/* Quick nav */}
          {tentang?.sections && tentang.sections.length > 0 && (
            <div className="mt-10 flex flex-wrap gap-3">
              {tentang.sections.map((s) => (
                <a key={s.id} href={`#${s.section_key}`} className="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur transition hover:bg-white/20">
                  {s.judul}
                </a>
              ))}
            </div>
          )}
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
    </main>
  );
}
