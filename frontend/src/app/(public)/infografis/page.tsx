import Link from 'next/link';
import { getInfografisCategories, getInfografisList } from '@/lib/api/infografis';

export const revalidate = 60;

export const metadata = {
  title: 'Infografis | Portal Kabupaten Kepulauan Sangihe',
  description: 'Indikator daerah yang telah diverifikasi dan diterbitkan untuk masyarakat.',
};

export default async function InfografisPage({ searchParams }: { searchParams: Promise<{ kategori?: string }> }) {
  const { kategori } = await searchParams;
  let [items, categories] = await Promise.all([
    getInfografisList().catch(() => []),
    getInfografisCategories().catch(() => []),
  ]);
  if (kategori) items = items.filter((item) => item.category.toLowerCase() === kategori.toLowerCase());

  return (
    <main className="min-h-screen bg-slate-50">
      <section className="bg-linear-to-r from-teal-800 to-teal-600 py-12 text-white">
        <div className="container-page">
          <Link href="/" className="text-sm text-white/80 hover:text-white">Beranda</Link>
          <h1 className="heading-xl mt-4">Infografis Daerah</h1>
          <p className="mt-2 max-w-2xl text-white/85">Ringkasan indikator dari dataset aktif yang telah diverifikasi, diterbitkan, dan diizinkan untuk publikasi.</p>
        </div>
      </section>
      <section className="border-b bg-white">
        <div className="container-page flex flex-wrap gap-2 py-4">
          <Link href="/infografis" className={`rounded-full px-4 py-2 text-sm ${!kategori ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-700'}`}>Semua</Link>
          {categories.map((category) => (
            <Link key={category.id} href={`/infografis?kategori=${encodeURIComponent(category.name)}`} className={`rounded-full px-4 py-2 text-sm ${kategori?.toLowerCase() === category.name.toLowerCase() ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-700'}`}>
              {category.name} <span className="opacity-70">({category.count})</span>
            </Link>
          ))}
        </div>
      </section>
      <div className="container-page py-10">
        {items.length ? <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => (
            <Link key={item.slug} href={`/infografis/${item.slug}`} className="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
              <span className="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{item.category}</span>
              <h2 className="mt-4 text-lg font-semibold text-slate-900 group-hover:text-teal-800">{item.title}</h2>
              <p className="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{item.description || 'Ringkasan dataset daerah yang telah diterbitkan.'}</p>
              <p className="mt-4 text-xs text-slate-500">{item.opd ?? 'Pemerintah Kabupaten Kepulauan Sangihe'}{item.years.length ? ` · ${item.years.join(', ')}` : ''}</p>
              <span className="mt-5 inline-block text-sm font-semibold text-teal-800">Buka indikator →</span>
            </Link>
          ))}
        </div> : <div className="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center text-slate-600">Belum ada indikator publik yang tersedia pada kategori ini.</div>}
      </div>
    </main>
  );
}
