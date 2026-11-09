import { api } from './client';

export interface PublicNews {
  id: number;
  judul: string;
  slug: string;
  ringkasan: string | null;
  gambar: string | null;
  kategori: string | null;
  terbit_at: string | null;
  isi: string;
  created_at: string;
  updated_at: string;
}

export interface NewsListResponse {
  data: PublicNews[];
  meta?: { last_page: number; current_page?: number };
  last_page?: number;
  current_page?: number;
}

export const fetchBeritaList = (params: { kategori?: string; per_page?: number; page?: number } = {}) =>
  api.get<NewsListResponse>('/public/berita', { params }).then(({ data }) => ({
    ...data,
    meta: data.meta ?? (data.last_page ? { last_page: data.last_page, current_page: data.current_page } : undefined),
  }));

export const fetchBeritaDetail = (slug: string) =>
  api.get<{ data: PublicNews }>(`/public/berita/${slug}`).then((r) => r.data);
