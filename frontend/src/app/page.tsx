import Link from "next/link";
import { Megaphone, Users, Target, ScrollText, Building, Map, ShieldCheck } from "lucide-react";

export const revalidate = 60;

const CARDS = [
  { href: "/tentang-daerah/sambutan", title: "Sambutan Bupati", desc: "Sambutan resmi Bupati Kepulauan Sangihe.", icon: Megaphone, color: "text-primary-600 bg-primary-50" },
  { href: "/tentang-daerah/pejabat", title: "Profil Pejabat", desc: "Profil pejabat struktural daerah.", icon: Users, color: "text-violet-600 bg-violet-50" },
  { href: "/tentang-daerah/visi-misi", title: "Visi & Misi", desc: "Arah pembangunan daerah.", icon: Target, color: "text-emerald-600 bg-emerald-50" },
  { href: "/tentang-daerah/sejarah", title: "Sejarah", desc: "Perjalanan kabupaten dari masa ke masa.", icon: ScrollText, color: "text-amber-600 bg-amber-50" },
  { href: "/tentang-daerah/opd", title: "Dinas & OPD", desc: "Daftar perangkat daerah.", icon: Building, color: "text-sky-600 bg-sky-50" },
  { href: "/tentang-daerah/geografi", title: "Geografi", desc: "Kondisi geografis dan demografis.", icon: Map, color: "text-teal-600 bg-teal-50" },
  { href: "/tentang-daerah/lambang", title: "Lambang", desc: "Makna dan filosofi lambang daerah.", icon: ShieldCheck, color: "text-rose-600 bg-rose-50" },
];

const QUICK_LINKS = [
  { href: "/berita", title: "Berita", desc: "Informasi dan pengumuman terbaru", color: "border-l-blue-500" },
  { href: "/statistik", title: "Statistik", desc: "Data demografi dan kesehatan", color: "border-l-emerald-500" },
  { href: "/peta", title: "Peta Sebaran", desc: "Peta interaktif wilayah", color: "border-l-amber-500" },
];

export default function HomePage() {
  return (
    <main className="min-h-screen">
      {/* Hero */}
      <section className="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 text-white">
          <div className="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent" />
        <div className="container-page relative py-20 md:py-32">
          <div className="max-w-3xl">
            <span className="inline-block rounded-full bg-white/10 px-4 py-1 text-sm text-white/80 ring-1 ring-white/20 backdrop-blur">
              Portal Resmi Pemerintah Daerah
            </span>
            <h1 className="heading-xl mt-6">
              Kabupaten Kepulauan Sangihe
            </h1>
            <p className="mt-4 text-lg text-white/80">Tanah Tona&apos;a, Tubuh Adat</p>
            <p className="mt-6 max-w-2xl text-white/70">
              Akses data publik, peta sebaran, berita, dan informasi resmi Pemerintah Kabupaten Kepulauan Sangihe - Sulawesi Utara.
            </p>
            <div className="mt-10 flex flex-wrap gap-4">
              <Link href="/tentang-daerah" className="rounded-xl bg-white px-6 py-3 font-semibold text-primary-700 shadow-lg transition hover:bg-primary-50 hover:shadow-xl">
                Tentang Daerah
              </Link>
              <Link href="/tentang-daerah/sejarah" className="rounded-xl border border-white/30 bg-white/10 px-6 py-3 font-semibold text-white backdrop-blur transition hover:bg-white/20">
                Sejarah
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Cards Grid */}
      <section className="section-padding bg-slate-50">
        <div className="container-page">
          <h2 className="heading-lg text-center">Jelajahi Tentang Daerah</h2>
          <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {CARDS.map((c) => (
              <Link key={c.href} href={c.href} className="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary-300 hover:shadow-xl">
                <div className={"inline-flex h-12 w-12 items-center justify-center rounded-xl " + c.color}>
                  <c.icon className="h-6 w-6" />
                </div>
                <h3 className="mt-4 heading-md group-hover:text-primary-700">{c.title}</h3>
                <p className="mt-2 text-sm text-slate-600">{c.desc}</p>
                <div className="mt-4 flex items-center gap-1 text-sm font-medium text-primary-600 group-hover:underline">
                Buka →
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Quick Links */}
      <section className="section-padding">
        <div className="container-page">
          <h2 className="heading-lg mb-8">Layanan Lainnya</h2>
          <div className="grid gap-4 md:grid-cols-3">
            {QUICK_LINKS.map((c) => (
              <Link key={c.href} href={c.href} className={"group rounded-xl border border-slate-200 bg-white p-5 pl-6 shadow-sm transition hover:shadow-md hover:border-l-4 " + c.color}>
                <h3 className="font-semibold text-slate-900 group-hover:text-primary-700">{c.title}</h3>
                <p className="mt-1 text-sm text-slate-500">{c.desc}</p>
              </Link>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}
