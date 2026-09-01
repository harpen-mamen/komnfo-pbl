import type { Metadata } from 'next';
import VisiMisiSection from '@/components/tentang/VisiMisiSection';
import { fetchVisiMisi } from '@/lib/api/tentang-daerah';

export const metadata: Metadata = { title: 'Visi & Misi' };
export const revalidate = 600;

export default async function VisiMisiPage() {
  const data = await fetchVisiMisi().catch(() => null);
  if (!data) return <div className="container-page py-12">Gagal memuat data.</div>;

  return (
    <main>
      <section className="bg-gradient-to-br from-primary-700 to-primary-800 text-white">
        <div className="container-page py-12 md:py-16">
          <nav className="text-sm text-primary-100">
            <a href="/" className="hover:text-white">Beranda</a> <span className="mx-2">/</span>
            <a href="/tentang-daerah" className="hover:text-white">Tentang Daerah</a> <span className="mx-2">/</span>
            <span className="text-white">Visi & Misi</span>
          </nav>
          <h1 className="heading-xl mt-4">Visi & Misi</h1>
        </div>
      </section>
      <section className="section-padding bg-slate-50">
        <div className="container-page">
          <VisiMisiSection data={data} />
        </div>
      </section>
    </main>
  );
}
