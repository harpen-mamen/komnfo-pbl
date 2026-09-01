'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';
import { Menu, X, ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

const NAV_ITEMS = [
  { href: '/', label: 'Beranda' },
  {
    label: 'Tentang Daerah',
    children: [
      { href: '/tentang-daerah/sambutan', label: 'Sambutan Bupati' },
      { href: '/tentang-daerah/visi-misi', label: 'Visi & Misi' },
      { href: '/tentang-daerah/pejabat', label: 'Profil Pejabat' },
      { href: '/tentang-daerah/sejarah', label: 'Sejarah' },
      { href: '/tentang-daerah/opd', label: 'Dinas & OPD' },
      { href: '/tentang-daerah/geografi', label: 'Geografi' },
      { href: '/tentang-daerah/lambang', label: 'Lambang' },
    ],
  },
  { href: '/berita', label: 'Berita' },
  { href: '/statistik', label: 'Statistik' },
  { href: '/peta', label: 'Peta Publik' },
];

export default function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);
  const [openSubmenu, setOpenSubmenu] = useState<string | null>(null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    onScroll();
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <header
      className={cn(
        'sticky top-0 z-50 transition-all duration-300',
        scrolled ? 'bg-white/95 shadow-md backdrop-blur' : 'bg-white'
      )}
    >
      <div className="container-page">
        <div className="flex h-16 items-center justify-between lg:h-20">
          <Link href="/" className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-600 to-primary-700 text-white font-bold text-lg shadow-md">
              S
            </div>
            <div className="hidden sm:block">
              <div className="font-heading text-base font-bold text-slate-900 leading-tight">
                Kab. Kepulauan Sangihe
              </div>
              <div className="text-xs text-slate-500">Portal Resmi Pemerintah Daerah</div>
            </div>
          </Link>

          <nav className="hidden items-center gap-1 lg:flex">
            {NAV_ITEMS.map((item) => (
              <div key={item.label} className="relative group">
                {item.children ? (
                  <>
                    <button
                      type="button"
                      className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-primary-50 hover:text-primary-700"
                      onMouseEnter={() => setOpenSubmenu(item.label)}
                    >
                      {item.label}
                      <ChevronDown className="h-4 w-4" />
                    </button>
                    <div
                      className={cn(
                        'absolute left-0 top-full min-w-[14rem] origin-top rounded-xl border border-slate-200 bg-white p-2 shadow-xl transition',
                        openSubmenu === item.label ? 'scale-100 opacity-100 visible' : 'scale-95 opacity-0 invisible'
                      )}
                      onMouseLeave={() => setOpenSubmenu(null)}
                    >
                      {item.children.map((c) => (
                        <Link
                          key={c.href}
                          href={c.href}
                          className="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-primary-50 hover:text-primary-700"
                        >
                          {c.label}
                        </Link>
                      ))}
                    </div>
                  </>
                ) : (
                  <Link
                    href={item.href ?? '#'}
                    className="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-primary-50 hover:text-primary-700"
                  >
                    {item.label}
                  </Link>
                )}
              </div>
            ))}

            <Link
              href="/admin"
              className="ml-3 rounded-lg border border-primary-600 px-4 py-2 text-sm font-semibold text-primary-700 transition hover:bg-primary-600 hover:text-white"
            >
              Login Admin
            </Link>
          </nav>

          <button
            type="button"
            className="rounded-lg p-2 text-slate-700 lg:hidden"
            onClick={() => setOpen(!open)}
            aria-label="Toggle menu"
          >
            {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>
      </div>

      {open && (
        <div className="border-t border-slate-200 bg-white lg:hidden">
          <nav className="container-page flex flex-col gap-1 py-2">
            {NAV_ITEMS.map((item) => (
              <div key={item.label}>
                {item.children ? (
                  <details className="group">
                    <summary className="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-primary-50">
                      {item.label}
                      <ChevronDown className="h-4 w-4 group-open:rotate-180 transition-transform" />
                    </summary>
                    <div className="ml-4 mt-1 space-y-1 border-l-2 border-primary-200 pl-2">
                      {item.children.map((c) => (
                        <Link
                          key={c.href}
                          href={c.href}
                          className="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-primary-50"
                          onClick={() => setOpen(false)}
                        >
                          {c.label}
                        </Link>
                      ))}
                    </div>
                  </details>
                ) : (
                  <Link
                    href={item.href ?? '#'}
                    className="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-primary-50"
                    onClick={() => setOpen(false)}
                  >
                    {item.label}
                  </Link>
                )}
              </div>
            ))}
            <Link
              href="/admin"
              className="mt-2 rounded-lg border border-primary-600 px-3 py-2 text-center text-sm font-semibold text-primary-700"
            >
              Login Admin
            </Link>
          </nav>
        </div>
      )}
    </header>
  );
}
