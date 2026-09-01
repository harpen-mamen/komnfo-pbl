import Link from "next/link";
import { FileText, Megaphone, Database, MessageCircle, ChevronRight } from "lucide-react";

export const metadata = { title: "Layanan Publik", description: "Daftar layanan publik Pemerintah Kabupaten Kepulauan Sangihe." };

const layanan = [
  { icon: FileText, title: "Penerbitan Surat Keterangan", desc: "Surat keterangan domisili, usaha, dan keperluan administratif lainnya.", color: "bg-blue-50 text-blue-600" },
  { icon: Megaphone, title: "Pengaduan Masyarakat", desc: "Sampaikan keluhan dan aspirasi masyarakat secara online.", color: "bg-amber-50 text-amber-600" },
  { icon: Database, title: "Permohonan Data", desc: "Akses data terbuka pemerintah daerah sesuai regulasi.", color: "bg-emerald-50 text-emerald-600" },
  { icon: MessageCircle, title: "Konsultasi Publik", desc: "Tanya jawab dan konsultasi dengan OPD terkait.", color: "bg-violet-50 text-violet-600" },
];

const link = [
  { href: "/statistik", label: "Data Statistik", desc: "Lihat data demografi dan kesehatan" },
  { href: "/peta", label: "Peta Sebaran", desc: "Jelajahi peta interaktif" },
  { href: "/berita", label: "Berita", desc: "Informasi dan pengumuman" },
  { href: "/tentang-daerah/opd", label: "Daftar OPD", desc: "Kontak perangkat daerah" },
];

export default function LayananPage() {
  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10"><h1 className="heading-xl">Layanan Publik</h1><p className="mt-2 text-slate-600">Daftar layanan publik yang tersedia di lingkungan Pemerintah Kabupaten Kepulauan Sangihe.</p></div>
        <div className="mb-12 grid gap-6 md:grid-cols-2">
          {layanan.map((l) => (
            <div key={l.title} className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">
              <div className={"flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl " + l.color}><l.icon className="h-6 w-6" /></div>
              <div><h3 className="font-semibold text-slate-900">{l.title}</h3><p className="mt-1 text-sm text-slate-600">{l.desc}</p></div>
            </div>
          ))}
        </div>
        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-semibold text-slate-900">Tautan Cepat</h2>
          <div className="grid gap-3 md:grid-cols-2">
            {link.map((l) => (
              <Link key={l.href} href={l.href} className="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
                <span>{l.label}</span><span className="text-xs text-slate-400">{l.desc}</span><ChevronRight className="h-4 w-4 flex-shrink-0" />
              </Link>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
