import Link from 'next/link';
import type { Metadata } from 'next';
import SambutanSection from '@/components/tentang/SambutanSection';

export const metadata: Metadata = { title: 'Sambutan Bupati' };
export const revalidate = 60;

export default function SambutanPage() {
  return (
    <main>
      <section className="bg-linear-to-br from-primary-700 to-primary-800 text-white">
        <div className="container-page py-12 md:py-16">
          <nav className="text-sm text-primary-100">
            <Link href="/" className="hover:text-white">Beranda</Link> <span className="mx-2">/</span>
            <Link href="/tentang-daerah" className="hover:text-white">Tentang Daerah</Link> <span className="mx-2">/</span>
            <span className="text-white">Sambutan</span>
          </nav>
          <h1 className="heading-xl mt-4">Sambutan Bupati</h1>
        </div>
      </section>
      <section className="section-padding">
        <div className="container-page">
          <SambutanSection />
        </div>
      </section>
    </main>
  );
}
