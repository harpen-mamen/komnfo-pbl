import Link from 'next/link';
import { notFound } from 'next/navigation';
import { getInfografisDetail } from '@/lib/api/infografis';

export const revalidate = 60;

export default async function InfografisDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const item = await getInfografisDetail(id);
  if (!item) notFound();
  const max = Math.max(1, ...item.charts.flatMap((chart) => chart.values.filter(Number.isFinite)));
  const date = item.published_at ? new Date(item.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : null;

  return (
    <main className="min-h-screen bg-slate-50 py-10">
      <div className="container-page">
        <Link href="/infografis" className="text-sm font-medium text-teal-800 hover:underline">← Kembali ke infografis</Link>
        <header className="mt-6 max-w-3xl">
          <span className="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{item.category}</span>
          <h1 className="heading-xl mt-4">{item.title}</h1>
          <p className="mt-3 leading-7 text-slate-600">{item.description}</p>
          <p className="mt-4 text-sm text-slate-500">Sumber: {item.source || item.opd || 'Pemerintah Kabupaten Kepulauan Sangihe'}{item.years.length ? ` · Tahun ${item.years.join(', ')}` : ''}{date ? ` · Terbit ${date}` : ''}</p>
        </header>
        <section className="mt-8 grid gap-6">
          {item.charts.map((chart) => (
            <article key={`${chart.title}-${chart.unit}`} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-semibold text-slate-900">{chart.title}</h2>
              {chart.description && <p className="mt-1 text-sm text-slate-600">{chart.description}</p>}
              {chart.labels.length ? <div className="mt-6 space-y-4">
                {chart.labels.map((label, index) => {
                  const value = chart.values[index] ?? 0;
                  const width = Math.max(0, Math.min(100, (value / max) * 100));
                  return <div key={`${label}-${index}`}>
                    <div className="mb-1 flex flex-wrap justify-between gap-2 text-sm"><span className="text-slate-700">{label}</span><span className="font-semibold text-slate-900">{value.toLocaleString('id-ID', { maximumFractionDigits: 2 })}{chart.unit ? ` ${chart.unit}` : ''}</span></div>
                    <div className="h-3 overflow-hidden rounded-full bg-slate-100"><div className="h-full rounded-full bg-teal-700" style={{ width: `${width}%` }} /></div>
                  </div>;
                })}
              </div> : <p className="mt-4 text-sm text-slate-500">Rincian angka belum tersedia.</p>}
              {chart.aggregation_note && <p className="mt-5 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">{chart.aggregation_note}</p>}
            </article>
          ))}
        </section>
        <footer className="mt-6 rounded-xl bg-slate-100 p-4 text-xs leading-5 text-slate-600">
          Angka ini merupakan ringkasan dataset yang sudah dipublikasikan. Detail warga perorangan, rumah tangga, dan catatan internal tidak ditampilkan.
          {item.source_url && <a href={item.source_url} target="_blank" rel="noreferrer" className="ml-1 font-semibold text-teal-800 underline">Buka sumber</a>}
        </footer>
      </div>
    </main>
  );
}
