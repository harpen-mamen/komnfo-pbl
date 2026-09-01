import axios, { type AxiosInstance } from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';

export const api: AxiosInstance = axios.create({
  baseURL: `${API_BASE_URL}/api/v1`,
  timeout: 15000,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
});

api.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err.response?.status === 404) {
      console.warn('[API] 404:', err.config?.url);
    }
    return Promise.reject(err);
  }
);
