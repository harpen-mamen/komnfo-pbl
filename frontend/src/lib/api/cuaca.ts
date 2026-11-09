import { api } from './client';

export type CuacaPublik = {
  location: { village: string; district: string; regency: string; timezone: string };
  forecast: {
    datetime: string;
    description: string;
    temperature: number | null;
    humidity: number | null;
    wind_speed: number | null;
    wind_direction: string | null;
    icon: string | null;
  };
  updated_at: string | null;
  source: string;
  source_url: string;
};

export async function fetchCuaca(): Promise<CuacaPublik> {
  const response = await api.get<CuacaPublik>('/public/cuaca', { timeout: 10000 });
  return response.data;
}
