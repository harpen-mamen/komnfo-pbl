import Link from 'next/link';
import { MapPin, Phone, Mail, Globe } from 'lucide-react';

const QUICK_LINKS = [
  { href: '/tentang-daerah/sambutan', label: 'Sambutan Bupati' },
  { href: '/tentang-daerah/visi-misi', label: 'Visi & Misi' },
  { href: '/tentang-daerah/pejabat', label: 'Profil Pejabat' },
  { href: '/tentang-daerah/sejarah', label: 'Sejarah' },
  { href: '/tentang-daerah/geografi', label: 'Geografi' },
  { href: '/tentang-daerah/lambang', label: 'Lambang' },
  { href: '/tentang-daerah/opd', label: 'Dinas & OPD' },
  { href: '/berita', label: 'Berita' },
];

const SERVICE_LINKS = [
  { href: '/statistik', label: 'Statistik Publik' },
  { href: '/peta', label: 'Peta Sebaran' },
  { href: '/register', label: 'Pendaftaran Akun' },
  { href: '/layanan', label: 'Layanan Publik' },
];

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="bg-slate-900 text-slate-300">
      <div className="container-page py-12 md:py-16">
        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
          <div>
            <div className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white font-bold text-xl shadow-md">
                S
              </div>
              <div>
                <div className="font-heading text-lg font-bold text-white">Kab. Kepulauan Sangihe</div>
                <div className="text-xs text-slate-400">Tanah Tona&apos;a, Tubuh Adat</div>
              </div>
            </div>
            <p className="mt-4 text-sm leading-relaxed text-slate-400">
              Portal resmi Pemerintah Kabupaten Kepulauan Sangihe - Sulawesi Utara. Melayani masyarakat dengan transparan dan akuntabel.
            </p>
          </div>

          <div>
            <h3 className="font-heading text-sm font-semibold text-white uppercase tracking-wider">Tentang Daerah</h3>
            <ul className="mt-4 space-y-2 text-sm">
              {QUICK_LINKS.map((l) => (
                <li key={l.href}>
                  <Link href={l.href} className="text-slate-400 transition hover:text-primary-300">
                    {l.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-heading text-sm font-semibold text-white uppercase tracking-wider">Layanan</h3>
            <ul className="mt-4 space-y-2 text-sm">
              {SERVICE_LINKS.map((l) => (
                <li key={l.href}>
                  <Link href={l.href} className="text-slate-400 transition hover:text-primary-300">
                    {l.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-heading text-sm font-semibold text-white uppercase tracking-wider">Kontak</h3>
            <ul className="mt-4 space-y-3 text-sm">
              <li className="flex items-start gap-3 text-slate-400">
                <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-400" />
                <span>Jl. Kantor Bupati No. 1, Kota Baru, Tahuna, Sulawesi Utara</span>
              </li>
              <li className="flex items-center gap-3 text-slate-400">
                <Phone className="h-4 w-4 flex-shrink-0 text-primary-400" />
                <span>(0432) 12345</span>
              </li>
              <li className="flex items-center gap-3 text-slate-400">
                <Mail className="h-4 w-4 flex-shrink-0 text-primary-400" />
                <span>kominfo@sangihekab.go.id</span>
              </li>
              <li className="flex items-center gap-3 text-slate-400">
                <Globe className="h-4 w-4 flex-shrink-0 text-primary-400" />
                <a href="https://sangihekab.go.id" className="hover:text-primary-300">sangihekab.go.id</a>
              </li>
            </ul>
          </div>
        </div>

        <div className="mt-10 border-t border-slate-800 pt-6">
          <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <div className="text-sm text-slate-500">
              &copy; {year} Pemerintah Kabupaten Kepulauan Sangihe. Hak cipta dilindungi.
            </div>
            <div className="text-sm text-slate-500">
              Dikembangkan oleh <span className="text-primary-400">Diskominfo Kab. Kepulauan Sangihe</span>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
