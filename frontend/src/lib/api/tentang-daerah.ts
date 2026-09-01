import { api } from './client';

// ==================== TYPES ====================
export interface AboutPageSetting {
  id: number;
  judul_halaman: string;
  tagline: string | null;
  banner_image: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  og_image: string | null;
}

export interface AboutSection {
  id: number;
  section_key: string;
  judul: string;
  subjudul: string | null;
  icon: string | null;
  warna_aksen: string | null;
  urutan: number;
}

export interface PejabatProfile {
  id: number;
  tipe_pejabat: 'bupati' | 'wakil_bupati' | 'sekda' | 'asisten' | 'staf_ahli' | 'kepala_dinas' | 'lainnya';
  nama_lengkap: string;
  gelar_depan: string | null;
  gelar_belakang: string | null;
  nip: string | null;
  jabatan: string;
  foto: string | null;
  biodata_singkat: string | null;
  tanda_tangan: string | null;
  riwayat_pendidikan: Array<{ jenjang: string; institusi: string; tahun: string }> | null;
  riwayat_jabatan: Array<{ jabatan: string; periode: string }> | null;
  tanggal_mulai: string | null;
  tanggal_selesai: string | null;
  urutan: number;
  status: 'aktif' | 'nonaktif';
}

export interface TentangOpd {
  id: number;
  nama_instansi: string;
  singkatan: string | null;
  slug: string;
  kategori: string;
  logo: string | null;
  alamat: string | null;
  telepon: string | null;
  email: string | null;
  website: string | null;
  profil_singkat: string | null;
  tupoksi: string | null;
}

export interface TentangOpdDetail extends TentangOpd {
  galleries: Array<{ id: number; file_path: string; caption: string | null }>;
  kepala: PejabatProfile | null;
}

export interface TentangSejarah {
  id: number;
  tahun: string;
  judul_peristiwa: string;
  deskripsi: string;
  gambar: string | null;
  icon: string | null;
  urutan: number;
}

export interface VisiMisi {
  visi: string | null;
  misi: string[];
  tujuan: string[];
  sasaran: string[];
  periode: string | null;
}

// ==================== API FUNCTIONS ====================

export const fetchTentangDaerah = () =>
  api.get<{ page: AboutPageSetting; sections: AboutSection[] }>('/public/tentang-daerah').then((r) => r.data);

export const fetchSambutanBupati = () =>
  api.get<{ section: any; pejabat: PejabatProfile[] }>('/public/tentang-daerah/sambutan-bupati').then((r) => r.data);

export const fetchPejabat = (tipe?: string) =>
  api.get<{ data: PejabatProfile[] }>('/public/tentang-daerah/pejabat', { params: tipe ? { tipe } : {} }).then((r) => r.data);

export const fetchPejabatDetail = (id: number | string) =>
  api.get<{ data: PejabatProfile }>(`/public/tentang-daerah/pejabat/${id}`).then((r) => r.data);

export const fetchOpd = (kategori?: string) =>
  api.get<{ data: TentangOpd[] }>('/public/tentang-daerah/opd', { params: kategori ? { kategori } : {} }).then((r) => r.data);

export const fetchOpdDetail = (slug: string) =>
  api.get<{ data: TentangOpdDetail }>(`/public/tentang-daerah/opd/${slug}`).then((r) => r.data);

export const fetchSejarah = () =>
  api.get<{ data: TentangSejarah[] }>('/public/tentang-daerah/sejarah').then((r) => r.data);

export const fetchVisiMisi = () =>
  api.get<VisiMisi>('/public/tentang-daerah/visi-misi').then((r) => r.data);

export const fetchGeografi = () =>
  api.get('/public/tentang-daerah/geografi').then((r) => r.data);

export const fetchLambang = () =>
  api.get('/public/tentang-daerah/lambang').then((r) => r.data);
