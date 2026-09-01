import Link from 'next/link';
import { fetchTentangDaerah } from '@/lib/api/tentang-daerah';

export const revalidate = 60;

export default async function HomePage() {
  let data;
  try {
    data = await fetchTentangDaerah();
  } catch (err) {
    data = null;
  }

  return (
    <main className="min-h-screen">
      <section className="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 text-white">
        <div className="absolute inset-0 bg-[url('/pattern-batik.svg')] opacity-10"></div>
        <div className="container-page relative py-20 md:py-32">
          <div className="max-w-3xl">
            <span className="inline-block rounded-full bg-accent-500/20 px-4 py-1 text-sm text-accent-300 ring-1 ring-accent-400/30">
              Portal Resmi Pemerintah Daerah
            </span>
            <h1 className="heading-xl mt-6">
              {data?.page.judul_halaman ?? 'Kabupaten Kepulauan Sangihe'}
            </h1>
            <p className="mt-4 text-lg text-primary-100">
              {data?.page.tagline ?? 'Tanah Tona\'a, Tubuh Adat'}
            </p>
            <p className="mt-6 max-w-2xl text-primary-50/90">
              {data?.page.meta_description ?? 'Akses data publik, peta sebaran, dan informasi resmi Kabupaten kep Kabupaten kepuid'}
            </p>
            <div className="mt-10 flex flex-wrap gap-4">
              <Link
                href="/tentang-daerah"
                className="rounded-xl bg-white px-6 py-3 font-semibold text-primary-700 shadow-lg transition hover:bg-primary-50 hover:shadow-xl"
              >
                Tentang Daerah
              </Link>
              <Link
                href="/tentang-daerah/sejarah"
                className="rounded-xl border border-white/30 bg-white/10 px-6 py-3 font-semibold backdrop-blur transition hover:bg-white/20"
              >
                Sejarah
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="section-padding bg-slate-50">
        <div className="container-page">
          <h2 className="heading-lg text-center">Jelajahi Tentang Daerah</h2>
          <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {[
              { href: '/tentang-daerah/sambutan', title: 'Sambutan Bupati', icon: '📢', desc: 'Sambutan resmi Bupati Kepulauan Sangihe.' },
              { href: '/tentang-daerah/pejabat', title: 'Profil Pejabat', icon: '👥', desc: 'Bupati, Wakil Bupati, dan jajaran pejabat.' },
              { href: '/tentang-daerah/visi-misi', title: 'Visi & Misi', icon: '🎯', desc: 'Arah pembangunan daerah.' },
              { href: '/tentang-daerah/sejarah', title: 'Sejarah', icon: '📜', desc: 'Perjalanan kabupaten dari masa ke masa.' },
              { href: '/tentang-daerah/opd', title: 'Dinas & OPD', icon: '🏛️', desc: 'Daftar perangkat daerah.' },
              { href: '/tentang-daerah/geografi', title: 'Geografi', icon: '🗺️', desc: 'Kondisi geografis & demografis.' },
            ].map((c) => (
              <Link
                key={c.href}
                href={c.href}
                className="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary-300 hover:shadow-xl"
              >
                <div className="text-4xl">{c.icon}</div>
                <h3 className="mt-4 heading-md group-hover:text-primary-700">{c.title}</h3>
                <p className="mt-2 text-sm text-slate-600">{c.desc}</p>
                <div className="mt-4 text-sm font-medium text-primary-600 group-hover:underline">
                  Buka →
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}
