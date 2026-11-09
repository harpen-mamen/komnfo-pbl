import { api } from './client';

export interface PetaPayload {
  area_stats: Record<string, { indicators?: Array<{ label: string; value: number; unit: string | null; year: number }> }>;
  indicators: Array<{ id: string; dataset: string; opd: string; label: string; unit: string | null; year: number; values: Record<string, number> }>;
  kecamatans: Array<{ id: number; name: string; code: string | null }>;
  features: { type: 'FeatureCollection'; features: Array<{ type: 'Feature'; geometry: unknown; properties: Record<string, unknown> }> };
  summary: { penduduk: number; keluarga: number; tahun: number | null; kecamatan: number; desa: number };
}

export const fetchPeta = () =>
  api.get<PetaPayload>('/public/peta').then((r) => r.data);

export const fetchPetaSearch = (q: string) =>
  api.get<{ data: PetaSearchResult[] }>('/public/peta/cari', { params: { q } }).then((r) => r.data);

export interface PetaSearchResult { type: 'Feature'; geometry: unknown; properties: Record<string, unknown> & { name: string; opd: string; dataset: string } }
