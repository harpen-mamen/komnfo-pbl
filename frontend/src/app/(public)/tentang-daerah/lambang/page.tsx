import { fetchLambang } from "@/lib/api/tentang-daerah";
import { ShieldCheck } from "lucide-react";

export const revalidate = 600;
export const metadata = { title: "Lambang Daerah", description: "Lambang dan makna filosofi Kabupaten Kepulauan Sangihe." };

export default async function LambangPage() {
  let data = null;
  try { data = await fetchLambang(); } catch (e) {}

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
            <div className="prose prose-slate max-w-none rounded-2xl border border-slate-200 bg-white p-8 shadow-sm" dangerouslySetInnerHTML={{ __html: section.konten }} />
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
