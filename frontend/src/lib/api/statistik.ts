import { api } from './client';

export interface StatistikPayload {
  total: { penduduk: number; keluarga: number; rumah: number };
  by_gender: Record<string, number>;
  by_age: Record<string, number>;
  diseases: Record<string, number>;
}

export const fetchStatistik = () =>
  api.get<StatistikPayload>('/public/statistik').then((r) => r.data);

export const fetchStats = () =>
  api.get('/stats').then((r) => r.data);

export const fetchPenduduk = () =>
  api.get('/penduduk').then((r) => r.data);
