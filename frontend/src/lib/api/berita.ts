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

export const fetchBeritaList = (params: { kategori?: string; per_page?: number; page?: number } = {}) =>
  api.get<{ data: PublicNews[]; meta?: any }>('/public/berita', { params }).then((r) => r.data);

export const fetchBeritaDetail = (slug: string) =>
  api.get<{ data: PublicNews }>(`/public/berita/${slug}`).then((r) => r.data);
