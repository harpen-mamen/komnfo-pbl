import { api } from './client';

// Types
export interface Infografis {
  id: number;
  title: string;
  category: string;
  slug: string;
  description: string;
  image_url?: string | null;
  published_at: string;
  code: string;
  opd: string | null;
  years: number[];
  source: string;
  source_url: string | null;
  charts: PublicInfographicChart[];
}

export interface PublicInfographicChart {
  title: string;
  unit: string;
  labels: string[];
  values: number[];
  description: string;
  aggregation_note: string;
}

export interface InfografisDetail extends Infografis {}

export interface InfografisCategory {
  id: string;
  name: string;
  count: number;
}

// API Functions
export async function getInfografisList(): Promise<Infografis[]> {
  const response = await api.get('/public/infografis');
  return response.data.data as Infografis[];
}

export async function getInfografisCategories(): Promise<InfografisCategory[]> {
  const response = await api.get('/public/infografis/categories');
  return response.data.data as InfografisCategory[];
}

export async function getInfografisDetail(slug: string): Promise<InfografisDetail | null> {
  try {
    const response = await api.get(`/public/infografis/${slug}`);
    return response.data.data as InfografisDetail;
  } catch (error) {
    console.error('Failed to fetch infografis detail:', error);
    return null;
  }
}
