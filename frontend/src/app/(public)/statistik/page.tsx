import { fetchStatistik } from "@/lib/api/statistik";
import { Users, UsersRound } from "lucide-react";

export const revalidate = 60;
export const metadata = { title: "Statistik", description: "Data statistik penduduk dan kesehatan Kabupaten Kepulauan Sangihe." };

function fmt(n: number) { return n.toLocaleString("id-ID"); }

export default async function StatistikPage() {
  let stats = null;
  try { stats = await fetchStatistik(); } catch {}

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10"><h1 className="heading-xl">Statistik Data</h1><p className="mt-2 text-slate-600">Data statistik penduduk dan kesehatan Kabupaten Kepulauan Sangihe</p></div>
        {!stats ? (
          <div className="rounded-2xl border border-slate-200 bg-slate-50 py-20 text-center"><p className="text-slate-500">Data statistik belum tersedia.</p></div>
        ) : (
          <>
            <div className="mb-10 grid gap-4 sm:grid-cols-2">
              {[
                { icon: Users, label: "Total Penduduk", value: fmt(stats.total.penduduk), color: "text-primary-600 bg-primary-50" },
                { icon: UsersRound, label: "Total Keluarga", value: fmt(stats.total.keluarga), color: "text-emerald-600 bg-emerald-50" },
              ].map((card) => (
                <div key={card.label} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                  <div className={"mb-3 inline-flex rounded-xl p-3 " + card.color}><card.icon className="h-6 w-6" /></div>
                  <div className="text-2xl font-bold text-slate-900">{card.value}</div>
                  <div className="mt-1 text-sm text-slate-500">{card.label}</div>
                </div>
              ))}
            </div>
            <div className="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
              Ringkasan ini menggunakan data agregat kependudukan yang telah diterbitkan.{" "}
              {stats.tahun ? `Tahun data: ${stats.tahun}. ` : "Data tahun terbaru belum tersedia. "}
              <a href="/infografis" className="font-semibold text-primary-700 underline">Lihat infografis dan sumber data.</a>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
