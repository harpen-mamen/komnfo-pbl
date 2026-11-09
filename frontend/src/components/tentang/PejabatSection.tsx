import Image from 'next/image';
import { fetchPejabat } from '@/lib/api/tentang-daerah';

const API_BASE = (process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

const TIPE_LABELS: Record<string, string> = {
  bupati: 'Bupati',
  wakil_bupati: 'Wakil Bupati',
  sekda: 'Sekretaris Daerah',
  asisten: 'Asisten',
  staf_ahli: 'Staf Ahli',
  kepala_dinas: 'Kepala Dinas',
  lainnya: 'Pejabat',
};

export default async function PejabatSection() {
  const data = await fetchPejabat().catch(() => ({ data: [] }));
  const pejabat = data?.data ?? [];
  if (pejabat.length === 0) return null;

  return (
    <div>
      <div className="text-center">
        <span className="inline-block rounded-full bg-primary-100 px-4 py-1 text-sm font-medium text-primary-700">Jajaran</span>
        <h2 className="heading-lg mt-4">Profil Pejabat</h2>
        <p className="mt-2 text-slate-600">Pejabat Pemerintah Kabupaten Kepulauan Sangihe.</p>
      </div>

      <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {pejabat.map((p) => (
          <div key={p.id} className="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
            <div className="relative aspect-[3/4] bg-linear-to-br from-primary-100 to-primary-200">
              {p.foto ? (
                <Image src={`${API_BASE}/storage/${p.foto}`} alt={p.nama_lengkap} fill className="object-cover" />
              ) : (
                <div className="flex h-full items-center justify-center text-7xl text-white/70">U</div>
              )}
              <div className="absolute top-3 left-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary-700 shadow">
                {TIPE_LABELS[p.tipe_pejabat] ?? p.tipe_pejabat}
              </div>
            </div>
            <div className="p-5">
              <h3 className="font-semibold text-slate-900">
                {p.gelar_depan ? p.gelar_depan + ' ' : ''}{p.nama_lengkap}{p.gelar_belakang ? ', ' + p.gelar_belakang : ''}
              </h3>
              <p className="mt-1 text-sm text-primary-700">{p.jabatan}</p>
              {p.biodata_singkat && <p className="mt-3 line-clamp-3 text-sm text-slate-600">{p.biodata_singkat}</p>}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
