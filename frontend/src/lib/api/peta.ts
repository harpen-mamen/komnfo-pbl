import { api } from './client';

export interface PetaPayload {
  area_stats: Record<string, { rumah: number; penduduk: number }>;
  disease_data: Record<string, Record<string, number>>;
  summary: { penduduk: number; rumah: number; kecamatan: number };
}

export const fetchPeta = () =>
  api.get<PetaPayload>('/public/peta').then((r) => r.data);

export const fetchPetaSearch = (q: string) =>
  api.get('/public/peta/cari', { params: { q } }).then((r) => r.data);
