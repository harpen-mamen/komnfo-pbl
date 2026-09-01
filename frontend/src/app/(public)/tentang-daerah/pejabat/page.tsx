import type { Metadata } from 'next';
import PejabatSection from '@/components/tentang/PejabatSection';

export const metadata: Metadata = { title: 'Profil Pejabat' };
export const revalidate = 600;

export default function PejabatPage() {
  return (
    <main>
      <section className="bg-gradient-to-br from-primary-700 to-primary-800 text-white">
        <div className="container-page py-12 md:py-16">
          <nav className="text-sm text-primary-100">
            <a href="/" className="hover:text-white">Beranda</a> <span className="mx-2">/</span>
            <a href="/tentang-daerah" className="hover:text-white">Tentang Daerah</a> <span className="mx-2">/</span>
            <span className="text-white">Profil Pejabat</span>
          </nav>
          <h1 className="heading-xl mt-4">Profil Pejabat</h1>
        </div>
      </section>
      <section className="section-padding bg-slate-50">
        <div className="container-page">
          <PejabatSection />
        </div>
      </section>
    </main>
  );
}
