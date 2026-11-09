import Image from 'next/image';
import Link from 'next/link';
import { fetchOpd } from '@/lib/api/tentang-daerah';

const API_BASE = (process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

const KATEGORI_COLORS: Record<string, string> = {
  dinas: 'bg-blue-100 text-blue-700',
  badan: 'bg-purple-100 text-purple-700',
  kantor: 'bg-green-100 text-green-700',
  sekretariat: 'bg-orange-100 text-orange-700',
  rsud: 'bg-red-100 text-red-700',
  lainnya: 'bg-slate-100 text-slate-700',
};

export default async function OpdSection() {
  const data = await fetchOpd().catch(() => ({ data: [] }));
  const opds = data?.data ?? [];
  if (opds.length === 0) return null;

  return (
    <div>
      <div className="text-center">
        <span className="inline-block rounded-full bg-primary-100 px-4 py-1 text-sm font-medium text-primary-700">Perangkat Daerah</span>
        <h2 className="heading-lg mt-4">Dinas & OPD</h2>
        <p className="mt-2 text-slate-600">Daftar organisasi perangkat daerah Kabupaten Kepulauan Sangihe.</p>
      </div>

      <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {opds.map((opd) => (
          <Link
            key={opd.id}
            href={'/tentang-daerah/opd/' + opd.slug}
            className="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-primary-300 hover:shadow-md"
          >
            <div className="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-primary-100 to-primary-200 text-primary-700">
              {opd.logo ? (
                <Image src={`${API_BASE}/storage/${opd.logo}`} alt={opd.nama_instansi} width={48} height={48} className="object-contain" />
              ) : (
                <span className="text-2xl">O</span>
              )}
            </div>
            <div className="min-w-0 flex-1">
              <h3 className="truncate text-sm font-semibold text-slate-900 group-hover:text-primary-700">{opd.nama_instansi}</h3>
              <div className="mt-1 flex items-center gap-2">
                <span className={'rounded px-2 py-0.5 text-xs font-medium ' + (KATEGORI_COLORS[opd.kategori] ?? KATEGORI_COLORS.lainnya)}>{opd.kategori}</span>
                {opd.singkatan && <span className="text-xs text-slate-500">{opd.singkatan}</span>}
              </div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
