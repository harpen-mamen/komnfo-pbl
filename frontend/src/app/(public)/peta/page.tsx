import PetaMap from "@/components/peta/PetaMap";
import { MapPin, Users, UsersRound, Building } from "lucide-react";
import { fetchPeta } from "@/lib/api/peta";

export const revalidate = 60;
export const metadata = { title: "Peta Sebaran", description: "Peta sebaran penduduk dan fasilitas kesehatan Kabupaten Kepulauan Sangihe." };

export default async function PetaPage() {
  let data = null;
  try { data = await fetchPeta(); } catch {}

  const fmt = (n: number) => n.toLocaleString("id-ID");

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-8">
          <h1 className="heading-xl">Peta Sebaran</h1>
          <p className="mt-2 text-slate-600">Peta interaktif dan data sebaran penduduk Kabupaten Kepulauan Sangihe</p>
        </div>

        {data ? (
          <>
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
              {[
                { icon: Users, label: "Total Penduduk", value: fmt(data.summary.penduduk), color: "text-primary-600 bg-primary-50" },
                { icon: UsersRound, label: "Keluarga", value: fmt(data.summary.keluarga), color: "text-emerald-600 bg-emerald-50" },
                { icon: Building, label: "Kecamatan", value: String(data.summary.kecamatan), color: "text-amber-600 bg-amber-50" },
              ].map((card) => (
                <div key={card.label} className="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                  <div className={"inline-flex rounded-xl p-3 " + card.color}><card.icon className="h-6 w-6" /></div>
                  <div><div className="text-xl font-bold text-slate-900">{card.value}</div><div className="text-sm text-slate-500">{card.label}</div></div>
                </div>
              ))}
            </div>
            <PetaMap data={data} />
          </>
        ) : (
          <div className="rounded-2xl border border-slate-200 bg-slate-50 py-20 text-center">
            <MapPin className="mx-auto h-12 w-12 text-slate-300" />
            <p className="mt-4 text-slate-500">Data peta belum tersedia saat ini.</p>
          </div>
        )}
      </div>
    </div>
  );
}
