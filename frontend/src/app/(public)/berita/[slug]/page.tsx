import Link from "next/link";
import { notFound } from "next/navigation";
import { fetchBeritaDetail } from "@/lib/api/berita";
import { Calendar, Tag, ArrowLeft, User, Clock } from "lucide-react";

interface Props { params: Promise<{ slug: string }>; }

export async function generateMetadata({ params }: Props) {
  const { slug } = await params;
  try {
    const data = await fetchBeritaDetail(slug);
    return { title: data.data.judul, description: data.data.ringkasan ?? "" };
  } catch { return { title: "Berita" }; }
}

export const revalidate = 300;

export default async function BeritaDetailPage({ params }: Props) {
  const { slug } = await params;
  let data;
  try { data = await fetchBeritaDetail(slug); } catch { notFound(); }
  const article = data.data;
  const formatDate = (d: string | null) => d ? new Date(d).toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" }) : "";
  const readTime = article.isi ? Math.max(1, Math.ceil(article.isi.split(" ").length / 200)) : 1;

  return (
    <div className="section-padding">
      <div className="container-page">
        <div className="mx-auto max-w-3xl">
          <Link href="/berita" className="mb-6 inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 transition">
            <ArrowLeft className="h-4 w-4" /> Kembali ke Berita
          </Link>
          {article.kategori && (
            <span className="mb-4 inline-flex items-center gap-1 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700">
              <Tag className="h-3 w-3" />{article.kategori}
            </span>
          )}
          <h1 className="heading-xl">{article.judul}</h1>
          <div className="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-500">
            <span className="flex items-center gap-1"><Calendar className="h-4 w-4" />{formatDate(article.terbit_at)}</span>
            <span className="flex items-center gap-1"><User className="h-4 w-4" />Admin</span>
            <span className="flex items-center gap-1"><Clock className="h-4 w-4" />{readTime} menit baca</span>
          </div>
          {article.gambar && (
            <div className="relative mt-8 aspect-video overflow-hidden rounded-2xl">
              <img src={article.gambar} alt={article.judul} className="absolute inset-0 h-full w-full object-cover" />
            </div>
          )}
          <div className="mt-8 max-w-none whitespace-pre-line leading-8 text-slate-700">{article.isi}</div>
        </div>
      </div>
    </div>
  );
}
