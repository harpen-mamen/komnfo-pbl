import { api } from './client';

export interface StatistikPayload {
  total: { penduduk: number; keluarga: number };
  by_gender: Record<string, number>;
  by_age: Record<string, number>;
  diseases: Record<string, number>;
  tahun?: number | null;
  catatan?: string;
}

export interface PublicHome {
  stats: Array<{ label: string; value: number }>;
  hero: { judul: string; deskripsi: string | null; konten: string | null; gambar: string | null; video?: string | null } | null;
  berita: Array<{ id: number; judul: string; slug: string; ringkasan: string | null; gambar: string | null; kategori: string | null; terbit_at: string | null }>;
}

export const fetchPublicHome = () => api.get<PublicHome>('/public/home').then((r) => r.data);

export const fetchStatistik = () =>
  api.get<StatistikPayload>('/public/statistik').then((r) => r.data);

export const fetchStats = () =>
  api.get('/stats').then((r) => r.data);

export const fetchPenduduk = () =>
  api.get('/penduduk').then((r) => r.data);
