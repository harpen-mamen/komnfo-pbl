import Link from 'next/link';
import type { Metadata } from 'next';
import Image from 'next/image';
import { notFound } from 'next/navigation';
import { fetchOpdDetail } from '@/lib/api/tentang-daerah';

const API_BASE = (process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

export const revalidate = 60;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  try {
    const { slug } = await params;
    const data = await fetchOpdDetail(slug);
    return {
      title: data.data.nama_instansi,
      description: data.data.profil_singkat ?? 'Profil ' + data.data.nama_instansi,
    };
  } catch {
    return { title: 'OPD' };
  }
}

export default async function OpdDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const data = await fetchOpdDetail(slug).catch(() => null);
  if (!data) return notFound();

  const opd = data.data;
  const kepala = opd.kepala;

  return (
    <main className="min-h-screen bg-slate-50">
      <section className="bg-linear-to-br from-primary-700 to-primary-800 text-white">
        <div className="container-page py-12 md:py-16">
          <nav className="text-sm text-primary-100">
            <Link href="/" className="hover:text-white">Beranda</Link> <span className="mx-2">/</span>
            <Link href="/tentang-daerah" className="hover:text-white">Tentang Daerah</Link> <span className="mx-2">/</span>
            <Link href="/tentang-daerah/opd" className="hover:text-white">OPD</Link> <span className="mx-2">/</span>
            <span className="text-white">{opd.nama_instansi}</span>
          </nav>
          <div className="mt-6 flex items-center gap-6">
            <div className="flex h-24 w-24 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-4xl backdrop-blur">
              {opd.logo ? (
                <Image src={`${API_BASE}/storage/${opd.logo}`} alt={opd.nama_instansi} width={80} height={80} className="object-contain" />
              ) : 'O'}
            </div>
            <div>
              <h1 className="heading-lg">{opd.nama_instansi}</h1>
              {opd.singkatan && <p className="mt-1 text-primary-100">{opd.singkatan}</p>}
            </div>
          </div>
        </div>
      </section>

      <section className="container-page py-12">
        <div className="grid gap-8 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-8">
            {opd.profil_singkat && (
              <div className="rounded-2xl bg-white p-8 shadow-sm">
                <h2 className="heading-md text-slate-900">Profil Singkat</h2>
                <div className="mt-4 whitespace-pre-line leading-7 text-slate-600">{opd.profil_singkat}</div>
              </div>
            )}
            {opd.tupoksi && (
              <div className="rounded-2xl bg-white p-8 shadow-sm">
                <h2 className="heading-md text-slate-900">Tugas Pokok & Fungsi</h2>
                <div className="mt-4 whitespace-pre-line leading-7 text-slate-600">{opd.tupoksi}</div>
              </div>
            )}
          </div>

          <aside className="space-y-6">
            {kepala && (
              <div className="rounded-2xl bg-white p-6 shadow-sm">
                <h3 className="text-sm font-semibold text-slate-500">Kepala Instansi</h3>
                <div className="mt-3 flex items-center gap-3">
                  <div className="flex h-14 w-14 items-center justify-center rounded-full bg-primary-100 text-primary-700">U</div>
                  <div>
                    <div className="font-semibold text-slate-900">{kepala.nama_lengkap}</div>
                    <div className="text-xs text-slate-500">{kepala.jabatan}</div>
                  </div>
                </div>
              </div>
            )}
            <div className="rounded-2xl bg-white p-6 shadow-sm">
              <h3 className="text-sm font-semibold text-slate-500">Informasi Kontak</h3>
              <dl className="mt-3 space-y-2 text-sm">
                {opd.alamat && (<><dt className="font-medium text-slate-700">Alamat</dt><dd className="text-slate-600">{opd.alamat}</dd></>)}
                {opd.telepon && (<><dt className="font-medium text-slate-700">Telepon</dt><dd className="text-slate-600">{opd.telepon}</dd></>)}
                {opd.email && (<><dt className="font-medium text-slate-700">Email</dt><dd className="text-slate-600">{opd.email}</dd></>)}
                {opd.website && (<><dt className="font-medium text-slate-700">Website</dt><dd><a href={opd.website} target="_blank" rel="noopener" className="text-primary-600 hover:underline">{opd.website}</a></dd></>)}
              </dl>
            </div>
          </aside>
        </div>
      </section>
    </main>
  );
}
