import { fetchGeografi } from "@/lib/api/tentang-daerah";
import { MapPin, Mountain, Waves, Globe } from "lucide-react";

export const revalidate = 60;
export const metadata = { title: "Geografi", description: "Kondisi geografis dan demografi Kabupaten Kepulauan Sangihe." };

export default async function GeografiPage() {
  let data = null;
  try { data = await fetchGeografi(); } catch {}

  const section = data?.section;
  const kab = data?.kabupaten;

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10">
          <h1 className="heading-xl">Geografi & Demografi</h1>
          <p className="mt-2 text-slate-600">Kondisi geografis dan demografi {kab?.nama ?? "Kabupaten Kepulauan Sangihe"}</p>
        </div>
        <div className="mb-10 grid gap-4 md:grid-cols-3">
          {[
            { icon: MapPin, label: "Wilayah", value: "Kepulauan", color: "text-primary-600 bg-primary-50", desc: "Bentang alam kepulauan" },
            { icon: Mountain, label: "Topografi", value: "Perbukitan", color: "text-emerald-600 bg-emerald-50", desc: "Berbukit dan bergelombang" },
            { icon: Waves, label: "Lokasi", value: "Sulawesi Utara", color: "text-sky-600 bg-sky-50", desc: "Wilayah Indonesia Timur" },
          ].map((card) => (
            <div key={card.label} className="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div className={"inline-flex rounded-xl p-3 " + card.color}><card.icon className="h-6 w-6" /></div>
              <div><div className="text-sm text-slate-500">{card.label}</div><div className="text-lg font-bold text-slate-900">{card.value}</div><div className="text-xs text-slate-400">{card.desc}</div></div>
            </div>
          ))}
        </div>
        {section?.konten ? (
          <div className="rounded-2xl border border-slate-200 bg-white p-8 leading-8 text-slate-700 shadow-sm">
            <h2 className="text-xl font-semibold text-slate-900">{section.judul}</h2>
            {section.subjudul && <p className="mt-2 text-sm text-slate-500">{section.subjudul}</p>}
            <p className="mt-5 whitespace-pre-line">{section.konten}</p>
            <p className="mt-6 border-t border-slate-100 pt-4 text-xs text-slate-500">Sumber: <a className="underline hover:text-primary-700" href="https://sangihekab.go.id/tentang-daerah/" target="_blank" rel="noreferrer">sangihekab.go.id</a></p>
          </div>
        ) : (
          <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <p className="text-slate-600">Informasi geografi sedang dalam pengembangan oleh administrator.</p>
          </div>
        )}
        <div className="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
          <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700"><Globe className="h-4 w-4" />Sumber Data</h3>
          <p className="text-sm text-slate-500">Data geografi bersumber dari {kab?.nama ?? "Kabupaten Kepulauan Sangihe"}. Untuk data terbaru silakan hubungi Dinas terkait.</p>
        </div>
      </div>
    </div>
  );
}
