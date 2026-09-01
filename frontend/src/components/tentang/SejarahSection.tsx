import { fetchSejarah } from '@/lib/api/tentang-daerah';

export default async function SejarahSection() {
  const data = await fetchSejarah().catch(() => ({ data: [] }));
  const events = data?.data ?? [];
  if (events.length === 0) return null;

  return (
    <div>
      <div className="text-center">
        <span className="inline-block rounded-full bg-primary-100 px-4 py-1 text-sm font-medium text-primary-700">Sejarah</span>
        <h2 className="heading-lg mt-4">Sejarah Singkat</h2>
        <p className="mt-2 text-slate-600">Perjalanan Kabupaten Kepulauan Sangihe dari masa ke masa.</p>
      </div>

      <div className="relative mx-auto mt-12 max-w-4xl">
        <div className="absolute left-1/2 hidden h-full w-0.5 -translate-x-1/2 bg-primary-200 md:block"></div>
        <div className="space-y-8">
          {events.map((e, i) => (
            <div key={e.id} className={'flex flex-col gap-4 md:flex-row md:items-center ' + (i % 2 === 1 ? 'md:flex-row-reverse' : '')}>
              <div className="md:w-1/2">
                <div className={'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ' + (i % 2 === 1 ? 'md:text-right' : '')}>
                  <div className="text-2xl font-bold text-primary-700">{e.tahun}</div>
                  <h3 className="mt-2 font-semibold text-slate-900">{e.judul_peristiwa}</h3>
                  <p className="mt-2 text-sm text-slate-600">{e.deskripsi}</p>
                </div>
              </div>
              <div className="hidden md:flex md:w-12 md:justify-center">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg ring-4 ring-primary-100">P</div>
              </div>
              <div className="hidden md:block md:w-1/2"></div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
