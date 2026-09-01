import { fetchStatistik } from "@/lib/api/statistik";
import { Users, Home, UsersRound, Activity, Heart } from "lucide-react";

export const revalidate = 60;
export const metadata = { title: "Statistik", description: "Data statistik penduduk dan kesehatan Kabupaten Kepulauan Sangihe." };

function fmt(n: number) { return n.toLocaleString("id-ID"); }

function BarChart({ label, value, max, color }: { label: string; value: number; max: number; color: string }) {
  const pct = max > 0 ? Math.round((value / max) * 100) : 0;
  return (
    <div>
      <div className="mb-1 flex justify-between text-sm"><span className="text-slate-700">{label}</span><span className="font-semibold text-slate-900">{fmt(value)}</span></div>
      <div className="h-3 w-full overflow-hidden rounded-full bg-slate-100"><div className="h-full rounded-full transition-all duration-700" style={{ width: pct + "%", backgroundColor: color }} /></div>
    </div>
  );
}

const GENDER: Record<string, string> = { L: "Laki-laki", P: "Perempuan" };
const GENDER_COLOR: Record<string, string> = { L: "#0066CC", P: "#DB2777" };
const AGE_LABEL: Record<string, string> = { "0-4": "0-4 th", "5-11": "5-11 th", "12-17": "12-17 th", "18-59": "18-59 th", "60+": "60+ th" };
const AGE_COLOR: Record<string, string> = { "0-4": "#F59E0B", "5-11": "#10B981", "12-17": "#8B5CF6", "18-59": "#0066CC", "60+": "#DC2626" };
const DISEASE_LABEL: Record<string, string> = { ispa: "ISPA", dbd: "DBD", malaria: "Malaria", tbc: "TBC", hipertensi: "Hipertensi", diabetes: "Diabetes", stunting: "Stunting", gizi_buruk: "Gizi Buruk" };
const DISEASE_COLOR: Record<string, string> = { ispa: "#EF4444", dbd: "#F97316", malaria: "#EAB308", tbc: "#84CC16", hipertensi: "#06B6D4", diabetes: "#3B82F6", stunting: "#8B5CF6", gizi_buruk: "#EC4899" };

export default async function StatistikPage() {
  let stats = null;
  try { stats = await fetchStatistik(); } catch (e) {}

  const genderTotal = stats ? Object.values(stats.by_gender).reduce((a, b) => a + (b as number), 0) : 0;
  const ageTotal = stats ? Object.values(stats.by_age).reduce((a, b) => a + (b as number), 0) : 0;
  const diseaseTotal = stats ? Object.values(stats.diseases).reduce((a: number, b) => a + (b as number), 0) : 0;

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10"><h1 className="heading-xl">Statistik Data</h1><p className="mt-2 text-slate-600">Data statistik penduduk dan kesehatan Kabupaten Kepulauan Sangihe</p></div>
        {!stats ? (
          <div className="rounded-2xl border border-slate-200 bg-slate-50 py-20 text-center"><p className="text-slate-500">Data statistik belum tersedia.</p></div>
        ) : (
          <>
            <div className="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              {[
                { icon: Users, label: "Total Penduduk", value: fmt(stats.total.penduduk), color: "text-primary-600 bg-primary-50" },
                { icon: UsersRound, label: "Total Keluarga", value: fmt(stats.total.keluarga), color: "text-emerald-600 bg-emerald-50" },
                { icon: Home, label: "Rumah Terdata", value: fmt(stats.total.rumah), color: "text-amber-600 bg-amber-50" },
                { icon: Activity, label: "Data Kesehatan", value: fmt(diseaseTotal), color: "text-rose-600 bg-rose-50" },
              ].map((card) => (
                <div key={card.label} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                  <div className={"mb-3 inline-flex rounded-xl p-3 " + card.color}><card.icon className="h-6 w-6" /></div>
                  <div className="text-2xl font-bold text-slate-900">{card.value}</div>
                  <div className="mt-1 text-sm text-slate-500">{card.label}</div>
                </div>
              ))}
            </div>
            <div className="grid gap-8 lg:grid-cols-2">
              <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="heading-md mb-6 flex items-center gap-2"><Users className="h-5 w-5 text-primary-600" />Komposisi Penduduk</h2>
                <h3 className="mb-4 text-sm font-semibold text-slate-700">Jenis Kelamin</h3>
                <div className="mb-8 space-y-3">
                  {Object.entries(stats.by_gender).map(([k, v]) => <BarChart key={k} label={GENDER[k] ?? k} value={v as number} max={genderTotal} color={GENDER_COLOR[k] ?? "#6B7280"} />)}
                </div>
                <h3 className="mb-4 text-sm font-semibold text-slate-700">Kelompok Usia</h3>
                <div className="space-y-3">
                  {Object.entries(stats.by_age).map(([k, v]) => <BarChart key={k} label={AGE_LABEL[k] ?? k} value={v as number} max={ageTotal} color={AGE_COLOR[k] ?? "#6B7280"} />)}
                </div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="heading-md mb-6 flex items-center gap-2"><Heart className="h-5 w-5 text-rose-600" />Data Kesehatan</h2>
                <p className="mb-6 text-sm text-slate-500">Data penyakit dan kondisi kesehatan masyarakat</p>
                <div className="space-y-4">
                  {Object.entries(stats.diseases).filter(([k]) => typeof (stats.diseases as any)[k] === "number").sort(([,a],[,b]) => (b as number) - (a as number)).map(([k, v]) => <BarChart key={k} label={DISEASE_LABEL[k] ?? k} value={v as number} max={diseaseTotal} color={DISEASE_COLOR[k] ?? "#6B7280"} />)}
                </div>
              </div>
            </div>
            <div className="mt-8 overflow-hidden rounded-2xl border border-slate-200">
              <table className="w-full text-sm">
                <thead className="bg-slate-50"><tr><th className="px-4 py-3 text-left font-semibold text-slate-700">Indikator</th><th className="px-4 py-3 text-right font-semibold text-slate-700">Jumlah</th></tr></thead>
                <tbody className="divide-y divide-slate-100">
                  <tr><td className="px-4 py-3 text-slate-600">Total Penduduk</td><td className="px-4 py-3 text-right font-medium">{fmt(stats.total.penduduk)}</td></tr>
                  <tr><td className="px-4 py-3 text-slate-600">Total Keluarga</td><td className="px-4 py-3 text-right font-medium">{fmt(stats.total.keluarga)}</td></tr>
                  <tr><td className="px-4 py-3 text-slate-600">Total Rumah</td><td className="px-4 py-3 text-right font-medium">{fmt(stats.total.rumah)}</td></tr>
                  <tr><td className="px-4 py-3 text-slate-600">Laki-laki</td><td className="px-4 py-3 text-right font-medium">{fmt((stats.by_gender as any).L ?? 0)}</td></tr>
                  <tr><td className="px-4 py-3 text-slate-600">Perempuan</td><td className="px-4 py-3 text-right font-medium">{fmt((stats.by_gender as any).P ?? 0)}</td></tr>
                  {Object.entries(stats.by_age).map(([k, v]) => <tr key={k}><td className="px-4 py-3 text-slate-600">Usia {AGE_LABEL[k] ?? k}</td><td className="px-4 py-3 text-right font-medium">{fmt(v as number)}</td></tr>)}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
