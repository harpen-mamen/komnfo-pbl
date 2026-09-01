import Image from 'next/image';
import { fetchSambutanBupati } from '@/lib/api/tentang-daerah';

const API_BASE = (process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000').replace(/\/$/, '');

export default async function SambutanSection() {
  const data = await fetchSambutanBupati().catch(() => null);
  const section = data?.section;
  const pejabat = data?.pejabat ?? [];
  const bupati = pejabat.find((p) => p.tipe_pejabat === 'bupati');
  const wakil = pejabat.find((p) => p.tipe_pejabat === 'wakil_bupati');

  return (
    <div>
      <div className="text-center">
        <span className="inline-block rounded-full bg-primary-100 px-4 py-1 text-sm font-medium text-primary-700">
          Sambutan
        </span>
        <h2 className="heading-lg mt-4">{section?.judul ?? 'Sambutan Bupati'}</h2>
        {section?.subjudul && <p className="mt-2 text-slate-600">{section.subjudul}</p>}
      </div>

      <div className="mt-12 grid items-start gap-12 lg:grid-cols-3">
        {bupati && (
          <div className="lg:col-span-1">
            <div className="sticky top-8 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-50 to-white p-6 shadow-lg ring-1 ring-primary-100">
              {bupati.foto ? (
                <div className="relative mx-auto h-56 w-44 overflow-hidden rounded-xl bg-slate-200">
                  <Image src={`${API_BASE}/storage/${bupati.foto}`} alt={bupati.nama_lengkap} fill className="object-cover" />
                </div>
              ) : (
                <div className="mx-auto flex h-56 w-44 items-center justify-center rounded-xl bg-gradient-to-br from-primary-200 to-primary-300 text-6xl text-white">U</div>
              )}
              <div className="mt-4 text-center">
                <h3 className="font-semibold text-lg text-slate-900">
                  {bupati.gelar_depan ? bupati.gelar_depan + ' ' : ''}{bupati.nama_lengkap}{bupati.gelar_belakang ? ', ' + bupati.gelar_belakang : ''}
                </h3>
                <p className="mt-1 text-sm text-primary-700">{bupati.jabatan}</p>
              </div>
            </div>
          </div>
        )}

        <div className={bupati ? 'lg:col-span-2' : 'lg:col-span-3'}>
          <article
            className="prose prose-slate max-w-none prose-headings:font-heading prose-p:leading-relaxed prose-p:text-slate-700"
            dangerouslySetInnerHTML={{ __html: section?.konten ?? '<p>Konten sambutan akan diperbarui oleh administrator.</p>' }}
          />

          {wakil && (
            <div className="mt-10 flex items-center gap-4 rounded-xl bg-slate-50 p-5 ring-1 ring-slate-200">
              <div className="flex h-14 w-14 items-center justify-center rounded-full bg-primary-100 text-2xl text-primary-700">U</div>
              <div>
                <div className="text-sm font-medium text-slate-500">Wakil Bupati</div>
                <div className="font-semibold text-slate-900">{wakil.nama_lengkap}</div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
