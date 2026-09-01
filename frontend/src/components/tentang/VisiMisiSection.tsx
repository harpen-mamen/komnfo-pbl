import type { VisiMisi } from '@/lib/api/tentang-daerah';

interface Props { data: VisiMisi }

export default function VisiMisiSection({ data }: Props) {
  return (
    <div>
      <div className="text-center">
        <span className="inline-block rounded-full bg-primary-100 px-4 py-1 text-sm font-medium text-primary-700">Visi & Misi</span>
        <h2 className="heading-lg mt-4">Arah Pembangunan Daerah</h2>
        {data.periode && <p className="mt-2 text-slate-600">Periode {data.periode}</p>}
      </div>

      <div className="mt-12 grid gap-8 lg:grid-cols-2">
        {data.visi && (
          <div className="rounded-2xl bg-gradient-to-br from-primary-700 to-primary-800 p-8 text-white shadow-xl">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-xl">V</div>
              <h3 className="text-xl font-semibold">Visi</h3>
            </div>
            <p className="mt-4 text-lg leading-relaxed">{data.visi}</p>
          </div>
        )}

        {data.misi.length > 0 && (
          <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-xl text-primary-700">M</div>
              <h3 className="text-xl font-semibold text-slate-900">Misi</h3>
            </div>
            <ol className="mt-4 space-y-3">
              {data.misi.map((m, i) => (
                <li key={i} className="flex gap-3 text-slate-700">
                  <span className="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">{i + 1}</span>
                  <span>{m}</span>
                </li>
              ))}
            </ol>
          </div>
        )}
      </div>

      {data.tujuan.length > 0 && (
        <div className="mt-10 rounded-2xl border border-slate-200 bg-white p-8">
          <h3 className="text-xl font-semibold text-slate-900">Tujuan</h3>
          <ul className="mt-4 space-y-2">
            {data.tujuan.map((t, i) => (
              <li key={i} className="flex gap-3 text-slate-700">
                <span className="text-primary-600">{'»'}</span>
                <span>{t}</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
