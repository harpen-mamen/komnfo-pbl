import Link from 'next/link';
import { ChevronRight, Database, FileText, Megaphone, MessageCircle } from 'lucide-react';
import { api } from '@/lib/api/client';

export const revalidate = 60;

const icons = { document: FileText, megaphone: Megaphone, database: Database, chat: MessageCircle } as const;
const links = [
  { href: '/statistik', label: 'Data Statistik', desc: 'Ringkasan data daerah' },
  { href: '/peta', label: 'Peta Sebaran', desc: 'Jelajahi peta interaktif' },
  { href: '/berita', label: 'Berita', desc: 'Informasi dan pengumuman' },
  { href: '/tentang-daerah/opd', label: 'Daftar OPD', desc: 'Kontak perangkat daerah' },
];

export default async function LayananPage() {
  const result = await api.get<{ data: { judul: string; deskripsi: string; layanan: Array<{ id: number; nama: string; deskripsi: string | null; icon: string }> } }>('/public/layanan').then((response) => response.data.data).catch(() => null);
  return <main className="section-padding"><div className="container-page">
    <div className="mb-10"><h1 className="heading-xl">{result?.judul || 'Layanan Publik'}</h1><p className="mt-2 text-slate-600">{result?.deskripsi || 'Informasi layanan publik Pemerintah Kabupaten Kepulauan Sangihe.'}</p></div>
    {result?.layanan?.length ? <div className="mb-12 grid gap-6 md:grid-cols-2">{result.layanan.map((service) => { const Icon = icons[service.icon as keyof typeof icons] || FileText; return <article key={service.id} className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700"><Icon className="h-6 w-6" /></div><div><h2 className="font-semibold text-slate-900">{service.nama}</h2><p className="mt-1 text-sm text-slate-600">{service.deskripsi}</p></div></article>; })}</div> : <div className="mb-12 rounded-2xl border border-slate-200 bg-slate-50 p-10 text-center text-slate-600">Informasi layanan sedang disiapkan oleh administrator.</div>}
    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="mb-4 text-lg font-semibold text-slate-900">Tautan Cepat</h2><div className="grid gap-3 md:grid-cols-2">{links.map((item) => <Link key={item.href} href={item.href} className="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-teal-300 hover:bg-teal-50"><span><strong>{item.label}</strong><span className="ml-3 text-xs text-slate-500">{item.desc}</span></span><ChevronRight className="h-4 w-4 flex-shrink-0" /></Link>)}</div></section>
  </div></main>;
}
