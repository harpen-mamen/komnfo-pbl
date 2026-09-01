# SIPDT Frontend (Next.js)

Portal publik resmi Pemerintah Kabupaten Kepulauan Sangihe, dibangun dengan **Next.js 14 App Router**.

## Stack
- **Framework**: Next.js 14 (App Router) + React 18
- **Bahasa**: TypeScript (strict)
- **Styling**: Tailwind CSS + tailwindcss-animate
- **HTTP Client**: Axios (data fetching dengan caching otomatis via Next.js)
- **Icons**: Lucide React

## Struktur
```
src/
├── app/                       # App Router (file = route)
│   ├── (public)/              # Group route untuk halaman publik
│   │   └── tentang-daerah/    # Halaman Tentang Daerah
│   │       ├── page.tsx       # Overview / landing tentang daerah
│   │       ├── sambutan/      # Sambutan bupati
│   │       ├── pejabat/       # Profil pejabat
│   │       ├── opd/           # List & detail OPD
│   │       ├── sejarah/       # Timeline sejarah
│   │       └── visi-misi/     # Visi & misi
│   ├── layout.tsx             # Root layout
│   ├── page.tsx               # Halaman beranda
│   └── globals.css            # Tailwind + global styles
├── components/
│   ├── tentang/               # Section components untuk halaman Tentang Daerah
│   ├── layout/                # Header, Footer (tempat untuk nanti)
│   └── ui/                    # Reusable UI primitives
├── lib/
│   ├── api/
│   │   ├── client.ts          # Axios instance
│   │   └── tentang-daerah.ts  # API calls + TypeScript types
│   └── utils.ts               # cn(), formatters, helpers
├── hooks/                     # Custom React hooks
└── types/                     # Shared TypeScript types
```

## Konfigurasi
Salin `.env.example` ke `.env.local` dan sesuaikan:
```bash
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
NEXT_PUBLIC_SITE_NAME=Portal Kabupaten Kepulauan Sangihe
```

## Commands
```bash
npm install              # Install dependencies
npm run dev              # Dev server di http://localhost:3000
npm run build            # Production build
npm start                # Run production server
npm run lint             # ESLint
npm run type-check       # tsc --noEmit
```

## Backend (Laravel)
Frontend ini menggunakan backend Laravel sebagai API. Endpoint utama:
- `GET /api/v1/public/tentang-daerah` - Index tentang daerah
- `GET /api/v1/public/tentang-daerah/sambutan-bupati`
- `GET /api/v1/public/tentang-daerah/pejabat`
- `GET /api/v1/public/tentang-daerah/pejabat/{id}`
- `GET /api/v1/public/tentang-daerah/opd`
- `GET /api/v1/public/tentang-daerah/opd/{slug}`
- `GET /api/v1/public/tentang-daerah/sejarah`
- `GET /api/v1/public/tentang-daerah/visi-misi`
- `GET /api/v1/public/tentang-daerah/geografi`
- `GET /api/v1/public/tentang-daerah/lambang`

Lihat dokumentasi lengkap di `../docs/API.md`.

## Caching
Halaman-halaman publik menggunakan Next.js ISR (`revalidate: 600s`).
Data statis (sejarah, visi-misi) di-cache selama 10 menit di backend.
