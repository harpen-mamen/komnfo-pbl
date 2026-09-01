import type { Metadata } from 'next';
import './globals.css';
import Header from '@/components/layout/Header';
import Footer from '@/components/layout/Footer';

export const metadata: Metadata = {
  title: {
    default: 'Portal Kabupaten Kepulauan Sangihe',
    template: '%s | Kabupaten Kepulauan Sangihe',
  },
  description: 'Portal informasi resmi Pemerintah Kabupaten Kepulauan Sangihe - Sulawesi Utara.',
  keywords: ['Sangihe', 'Kabupaten', 'Sulawesi Utara', 'pemerintah daerah'],
  authors: [{ name: 'Diskominfo Kab. Kepulauan Sangihe' }],
  openGraph: {
    type: 'website',
    locale: 'id_ID',
    siteName: 'Portal Kabupaten Kepulauan Sangihe',
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="id" className="scroll-smooth">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="flex min-h-screen flex-col">
        <Header />
        <div className="flex-1">{children}</div>
        <Footer />
      </body>
    </html>
  );
}
