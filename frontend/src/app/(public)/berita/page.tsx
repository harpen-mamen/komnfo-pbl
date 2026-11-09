import Link from 'next/link';
import { fetchBeritaList, type NewsListResponse } from '@/lib/api/berita';
import { Calendar, Tag } from 'lucide-react';

export const revalidate = 300;

interface PageProps {
  searchParams: Promise<{ kategori?: string; page?: string }>;
}

export async function generateMetadata({ searchParams }: PageProps) {
  const params = await searchParams;
  return {
    title: params.kategori ? `Berita - ${params.kategori}` : 'Berita',
    description: 'Informasi terkini dari Pemerintah Kabupaten Kepulauan Sangihe.',
  };
}

export default async function BeritaPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const kat = params.kategori;
  const page = parseInt(params.page ?? '1');

  let newsData: NewsListResponse = { data: [] };
  try {
    newsData = await fetchBeritaList({ kategori: kat, per_page: 12, page });
  } catch {}

  const news = newsData.data;
  const meta = newsData.meta;
  const kategoriList = ['Umum', 'Pemerintahan', 'Pembangunan', 'Kesehatan', 'Pendidikan', 'Ekonomi'];
  const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10">
          <h1 className="heading-xl">Berita</h1>
          <p className="mt-2 text-slate-600">Informasi terkini dari Pemerintah Kabupaten Kepulauan Sangihe</p>
        </div>

        <div className="mb-8 flex flex-wrap gap-2">
          <Link href="/berita" className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${!kat ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-primary-50 hover:text-primary-700'}`}>Semua</Link>
          {kategoriList.map((k) => (
            <Link key={k} href={`/berita?kategori=${encodeURIComponent(k)}`} className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${kat === k ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-primary-50 hover:text-primary-700'}`}>{k}</Link>
          ))}
        </div>

        {news.length === 0 ? (
          <div className="rounded-2xl border border-slate-200 bg-slate-50 py-20 text-center">
            <p className="text-slate-500">Belum ada berita yang dipublikasikan.</p>
          </div>
        ) : (
          <>
            {page === 1 && news[0] && (
              <Link href={`/berita/${news[0].slug}`} className="group mb-8 block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                <div className="flex flex-col md:flex-row">
                  {news[0].gambar && (
                    <div className="relative h-64 md:h-auto md:w-1/2 lg:w-2/5 flex-shrink-0">
                      <img src={news[0].gambar} alt={news[0].judul} className="absolute inset-0 h-full w-full object-cover transition group-hover:scale-105" />
                    </div>
                  )}
                  <div className="flex flex-col justify-center p-6 md:p-8">
                    <div className="mb-3 flex items-center gap-3 text-sm text-slate-500">
                      <span className="flex items-center gap-1"><Calendar className="h-3.5 w-3.5" />{formatDate(news[0].terbit_at)}</span>
                      {news[0].kategori && <span className="flex items-center gap-1"><Tag className="h-3.5 w-3.5" /><span className="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-700">{news[0].kategori}</span></span>}
                    </div>
                    <h2 className="heading-lg group-hover:text-primary-700">{news[0].judul}</h2>
                    {news[0].ringkasan && <p className="mt-3 line-clamp-3 text-slate-600">{news[0].ringkasan}</p>}
                  </div>
                </div>
              </Link>
            )}
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {(page === 1 ? news.slice(1) : news).map((item) => (
                <Link key={item.id} href={`/berita/${item.slug}`} className="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                  {item.gambar && (
                    <div className="relative h-48 flex-shrink-0 overflow-hidden">
                      <img src={item.gambar} alt={item.judul} className="absolute inset-0 h-full w-full object-cover transition group-hover:scale-105" />
                    </div>
                  )}
                  <div className="flex flex-1 flex-col p-5">
                    <div className="mb-2 flex items-center gap-3 text-xs text-slate-500">
                      <span className="flex items-center gap-1"><Calendar className="h-3 w-3" />{formatDate(item.terbit_at)}</span>
                      {item.kategori && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{item.kategori}</span>}
                    </div>
                    <h3 className="heading-md group-hover:text-primary-700">{item.judul}</h3>
                    {item.ringkasan && <p className="mt-2 line-clamp-2 text-sm text-slate-600">{item.ringkasan}</p>}
                  </div>
                </Link>
              ))}
            </div>
            {meta && meta.last_page > 1 && (
              <div className="mt-10 flex justify-center gap-2">
                {page > 1 && <Link href={`/berita?page=${page - 1}${kat ? `&kategori=${encodeURIComponent(kat)}` : ''}`} className="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 transition hover:bg-slate-50">Prev</Link>}
                <span className="flex items-center px-4 py-2 text-sm text-slate-500">Halaman {page} dari {meta.last_page}</span>
                {page < meta.last_page && <Link href={`/berita?page=${page + 1}${kat ? `&kategori=${encodeURIComponent(kat)}` : ''}`} className="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 transition hover:bg-slate-50">Next</Link>}
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
