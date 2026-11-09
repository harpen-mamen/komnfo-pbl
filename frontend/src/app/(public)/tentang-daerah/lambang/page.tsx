import { fetchLambang } from "@/lib/api/tentang-daerah";
import { ShieldCheck } from "lucide-react";

export const revalidate = 60;
export const metadata = { title: "Lambang Daerah", description: "Lambang dan makna filosofi Kabupaten Kepulauan Sangihe." };

export default async function LambangPage() {
  let data = null;
  try { data = await fetchLambang(); } catch {}

  const section = data?.data;

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mb-10">
          <h1 className="heading-xl">Lambang Daerah</h1>
          <p className="mt-2 text-slate-600">Lambang dan makna filosofi Kabupaten Kepulauan Sangihe</p>
        </div>
        <div className="mx-auto max-w-2xl">
          {section?.konten ? (
            <div className="space-y-8">
              <div className="rounded-2xl border border-slate-200 bg-white p-8 leading-8 text-slate-700 shadow-sm">
                <h2 className="text-xl font-semibold text-slate-900">{section.judul}</h2>
                {section.subjudul && <p className="mt-2 text-sm text-slate-500">{section.subjudul}</p>}
                <p className="mt-5">{section.konten}</p>
              </div>
              {(section.unsur?.length ?? 0) > 0 && (
                <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                  <h2 className="text-xl font-semibold text-slate-900">Makna unsur lambang</h2>
                  <div className="mt-5 grid gap-3 sm:grid-cols-2">
                    {section.unsur?.map((item) => <div key={item.nama} className="rounded-xl bg-slate-50 p-4"><h3 className="font-semibold text-slate-900">{item.nama}</h3><p className="mt-1 text-sm leading-6 text-slate-600">{item.makna}</p></div>)}
                  </div>
                </div>
              )}
              {(section.warna?.length ?? 0) > 0 && (
                <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                  <h2 className="text-xl font-semibold text-slate-900">Makna warna</h2>
                  <div className="mt-5 grid gap-3 sm:grid-cols-2">
                    {section.warna?.map((item) => <div key={item.nama} className="rounded-xl border border-slate-100 p-4"><h3 className="font-semibold text-slate-900">{item.nama}</h3><p className="mt-1 text-sm leading-6 text-slate-600">{item.makna}</p></div>)}
                  </div>
                </div>
              )}
              <p className="text-center text-xs text-slate-500">Sumber: <a className="underline hover:text-primary-700" href="https://sangihekab.go.id/tentang-daerah/lambang-dan-moto/" target="_blank" rel="noreferrer">Lambang dan Moto · sangihekab.go.id</a></p>
            </div>
          ) : (
            <div className="flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
              <div className="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100">
                <ShieldCheck className="h-10 w-10 text-slate-400" />
              </div>
              <p className="text-slate-600">Lambang daerah sedang dalam tahap penyusunan oleh administrator.</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
