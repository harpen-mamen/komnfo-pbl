import Link from "next/link";
import Image from "next/image";
import { Megaphone, Users, Target, ScrollText, Building, Map, ShieldCheck } from "lucide-react";
import { fetchPublicHome } from "@/lib/api/statistik";
import { fetchCuaca } from "@/lib/api/cuaca";
import { CloudSun, Droplets, Wind, MapPin, Clock3 } from "lucide-react";

const CARDS = [
  { href: "/tentang-daerah/sambutan", title: "Sambutan Bupati", desc: "Sambutan resmi Bupati Kepulauan Sangihe.", icon: Megaphone, color: "text-primary-600 bg-primary-50" },
  { href: "/tentang-daerah/pejabat", title: "Profil Pejabat", desc: "Profil pejabat struktural daerah.", icon: Users, color: "text-primary-700 bg-primary-50" },
  { href: "/tentang-daerah/visi-misi", title: "Visi & Misi", desc: "Arah pembangunan daerah.", icon: Target, color: "text-primary-700 bg-primary-50" },
  { href: "/tentang-daerah/sejarah", title: "Sejarah", desc: "Perjalanan kabupaten dari masa ke masa.", icon: ScrollText, color: "text-accent-700 bg-accent-50" },
  { href: "/tentang-daerah/opd", title: "Dinas & OPD", desc: "Daftar perangkat daerah.", icon: Building, color: "text-primary-700 bg-primary-50" },
  { href: "/tentang-daerah/geografi", title: "Geografi", desc: "Kondisi geografis dan demografis.", icon: Map, color: "text-primary-700 bg-primary-50" },
  { href: "/tentang-daerah/lambang", title: "Lambang", desc: "Makna dan filosofi lambang daerah.", icon: ShieldCheck, color: "text-accent-700 bg-accent-50" },
];

const QUICK_LINKS = [
  { href: "/berita", title: "Berita", desc: "Informasi dan pengumuman terbaru", color: "border-l-primary-600" },
  { href: "/statistik", title: "Statistik", desc: "Data demografi dan kesehatan", color: "border-l-primary-600" },
  { href: "/peta", title: "Peta Sebaran", desc: "Peta interaktif wilayah", color: "border-l-accent-600" },
];

export const revalidate = 60;

export default async function HomePage() {
  const [home, weather] = await Promise.all([
    fetchPublicHome().catch(() => null),
    fetchCuaca().catch(() => null),
  ]);
  const hero = home?.hero;
  return (
    <main className="min-h-screen">
      {/* Hero */}
      <section className="relative isolate flex min-h-[calc(100svh-5rem)] items-center overflow-hidden bg-[#102e36] text-white">
        {hero?.video ? (
          <video aria-hidden="true" tabIndex={-1} autoPlay muted loop playsInline preload="metadata"
            poster={hero.gambar || "/images/sangihe-coast.svg"}
            className="absolute inset-0 z-0 h-full w-full object-cover">
            <source src={hero.video} />
          </video>
        ) : (
          <div className="absolute inset-0 z-0 bg-cover bg-center" style={{ backgroundImage: `url('${hero?.gambar || "/images/sangihe-coast.svg"}')` }} />
        )}
        <div className="absolute inset-0 z-10 bg-gradient-to-r from-[#0a1f25]/95 via-[#102e36]/75 to-[#102e36]/45" />
        <div className="container-page relative z-20 py-14 md:py-20 lg:py-24">
          <div className="grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(19rem,25rem)] lg:gap-14">
            <div className="max-w-3xl">
              <h1 className="heading-xl mt-6 !text-[2rem] md:!text-[2.75rem] lg:!text-5xl">{hero?.judul || "Kabupaten Kepulauan Sangihe"}</h1>
              {hero?.konten && <p className="mt-5 whitespace-pre-line text-lg font-medium text-[#d9c98f]">{hero.konten}</p>}
              <p className="mt-6 max-w-2xl whitespace-pre-line text-base leading-7 text-slate-200">
                {hero?.deskripsi || "Akses data publik, peta sebaran, berita, dan informasi resmi Pemerintah Kabupaten Kepulauan Sangihe - Sulawesi Utara."}
              </p>
              <div className="mt-10 flex flex-wrap gap-4">
                <Link href="/tentang-daerah" className="rounded-lg bg-[#d2bd79] px-6 py-3 font-semibold text-[#172f31] transition hover:bg-[#dfd09f]">Tentang Daerah</Link>
                <Link href="/tentang-daerah/sejarah" className="rounded-lg border border-white/30 bg-[#102e36]/50 px-6 py-3 font-semibold text-white transition hover:bg-white/10">Sejarah</Link>
              </div>
            </div>

            <div className="w-full max-w-md lg:justify-self-end">
              <div className="overflow-hidden rounded-3xl border border-white/20 bg-white/[.12] shadow-[0_24px_70px_rgba(0,0,0,.28)] backdrop-blur-xl">
                <div className="flex items-center justify-between border-b border-white/15 px-5 py-4 sm:px-6">
                  <div className="flex items-center gap-3">
                    <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#d8e9e5]/15 text-[#e7d69e]"><CloudSun className="h-6 w-6" /></div>
                    <div><p className="text-xs font-bold uppercase tracking-[.16em] text-[#e7d69e]">Informasi Cuaca</p><p className="mt-0.5 text-sm font-semibold text-white">Prakiraan BMKG</p></div>
                  </div>
                  <span className="rounded-full border border-emerald-200/25 bg-emerald-300/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100">3 Hari</span>
                </div>
                {weather ? (
                  <div className="p-5 sm:p-6">
                    <div className="flex items-start justify-between gap-4">
                      <div>
                        <p className="flex items-center gap-1.5 text-sm text-white/75"><MapPin className="h-4 w-4 text-[#e7d69e]" />{weather.location.district}, Sangihe</p>
                        <p className="mt-4 text-5xl font-semibold tracking-tight text-white">{weather.forecast.temperature ?? '—'}<span className="ml-1 text-2xl text-white/70">°C</span></p>
                        <p className="mt-2 text-base font-medium text-white">{weather.forecast.description}</p>
                        <p className="text-sm text-white/65">{weather.location.village}</p>
                      </div>
                      {weather.forecast.icon ? <Image src={weather.forecast.icon} alt={weather.forecast.description} width={72} height={72} unoptimized className="h-[4.5rem] w-[4.5rem] object-contain drop-shadow-lg" /> : <CloudSun className="mt-2 h-14 w-14 text-[#e7d69e]" />}
                    </div>
                    <div className="mt-6 grid grid-cols-2 gap-3">
                      <div className="rounded-2xl border border-white/10 bg-black/10 p-3.5"><p className="flex items-center gap-2 text-xs text-white/65"><Droplets className="h-4 w-4 text-sky-200" />Kelembapan</p><p className="mt-2 text-lg font-semibold text-white">{weather.forecast.humidity ?? '—'}%</p></div>
                      <div className="rounded-2xl border border-white/10 bg-black/10 p-3.5"><p className="flex items-center gap-2 text-xs text-white/65"><Wind className="h-4 w-4 text-sky-200" />Kecepatan angin</p><p className="mt-2 text-lg font-semibold text-white">{weather.forecast.wind_speed ?? '—'} <span className="text-xs font-normal text-white/65">km/j</span></p></div>
                    </div>
                    <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-white/15 pt-4">
                      <p className="flex items-center gap-1.5 text-[11px] text-white/60"><Clock3 className="h-3.5 w-3.5" />{new Date(`${weather.forecast.datetime.replace(' ', 'T')}+08:00`).toLocaleString('id-ID', { timeZone: weather.location.timezone, dateStyle: 'medium', timeStyle: 'short' })}</p>
                      <span className="text-xs font-semibold text-[#e7d69e]">Sumber data: BMKG</span>
                    </div>
                  </div>
                ) : (
                  <div className="p-6">
                    <p className="text-sm leading-6 text-white/80">Prakiraan cuaca Sangihe sedang tidak dapat dimuat.</p>
                    <p className="mt-3 text-xs text-white/60">Sumber data prakiraan: BMKG</p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>
      {home?.stats?.length ? <section className="border-b border-slate-200 bg-white py-8"><div className="container-page grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{home.stats.map((stat) => <div key={stat.label} className="rounded-xl border border-slate-200 bg-slate-50 p-5"><p className="text-sm text-slate-500">{stat.label}</p><p className="mt-1 text-2xl font-bold text-slate-900">{Number(stat.value).toLocaleString("id-ID")}</p></div>)}</div></section> : null}

      {/* Cards Grid */}
      <section className="section-padding bg-[#f3f5f2]">
        <div className="container-page">
          <div className="mx-auto max-w-2xl text-center"><p className="text-xs font-bold uppercase tracking-[.18em] text-accent-700">Mengenal Sangihe</p><h2 className="heading-lg mt-3 text-[#173b3b]">Jelajahi Tentang Daerah</h2><p className="mt-3 text-slate-600">Informasi resmi tentang pemerintahan, sejarah, dan kehidupan Kabupaten Kepulauan Sangihe.</p></div>
          <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {CARDS.map((c) => (
              <Link key={c.href} href={c.href} className="group flex flex-col rounded-xl border border-[#dce3df] bg-[#fbfcfa] p-6 transition duration-200 hover:-translate-y-1 hover:border-primary-300 hover:shadow-[0_14px_35px_rgba(18,43,45,.09)]">
                <div className={"inline-flex h-12 w-12 items-center justify-center rounded-xl " + c.color}>
                  <c.icon className="h-6 w-6" />
                </div>
                <h3 className="mt-4 heading-md group-hover:text-primary-700">{c.title}</h3>
                <p className="mt-2 text-sm text-slate-600">{c.desc}</p>
                <div className="mt-4 flex items-center gap-1 text-sm font-medium text-primary-600 group-hover:underline">
                  Buka
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {home?.berita?.length ? <section className="section-padding bg-white"><div className="container-page"><div className="flex items-end justify-between gap-4"><div><p className="text-xs font-bold uppercase tracking-[.18em] text-accent-700">Informasi terbaru</p><h2 className="heading-lg mt-2">Berita Pemerintah Daerah</h2></div><Link href="/berita" className="text-sm font-semibold text-primary-700 hover:underline">Semua berita</Link></div><div className="mt-8 grid gap-5 md:grid-cols-3">{home.berita.slice(0, 3).map((news) => <Link key={news.id} href={`/berita/${news.slug}`} className="rounded-xl border border-slate-200 p-5 hover:shadow-md"><span className="text-xs font-medium text-teal-800">{news.kategori || "Berita"}</span><h3 className="mt-2 font-semibold text-slate-900">{news.judul}</h3>{news.ringkasan && <p className="mt-2 line-clamp-3 text-sm text-slate-600">{news.ringkasan}</p>}</Link>)}</div></div></section> : null}

      {/* Quick Links */}
      <section className="section-padding">
        <div className="container-page">
          <h2 className="heading-lg mb-8">Layanan Lainnya</h2>
          <div className="grid gap-4 md:grid-cols-3">
            {QUICK_LINKS.map((c) => (
              <Link key={c.href} href={c.href} className={"group rounded-lg border border-slate-200 border-l-4 bg-white p-5 pl-6 transition hover:shadow-md " + c.color}>
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
