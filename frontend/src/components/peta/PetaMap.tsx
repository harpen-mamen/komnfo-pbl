"use client";
import { useEffect, useRef, useState } from "react";
import { MapPin, Search, Layers } from "lucide-react";
import { fetchPetaSearch, type PetaSearchResult } from "@/lib/api/peta";

interface AreaIndicator { label: string; value: number; unit: string | null; year: number; }
interface AreaStats { indicators?: AreaIndicator[]; }
interface PetaData { area_stats: Record<string, AreaStats>; indicators: Array<{ id: string; label: string; values: Record<string, number>; unit: string | null }>; kecamatans: Array<{ id: number; name: string }>; features: { features: Array<{ geometry: unknown; properties: Record<string, unknown> }> }; summary: { penduduk: number; keluarga: number; kecamatan: number }; }

interface LeafletMap { setView(center: [number, number], zoom: number): LeafletMap; remove(): void; }
interface LeafletMarker { addTo(map: LeafletMap): LeafletMarker; bindPopup(content: string): LeafletMarker; }
interface LeafletApi {
  map(element: HTMLElement): LeafletMap;
  tileLayer(url: string, options: { attribution: string; maxZoom: number }): { addTo(map: LeafletMap): void };
  circleMarker(location: [number, number], options: { radius: number; color: string; fillColor: string; fillOpacity: number; weight: number }): LeafletMarker;
}
declare global { interface Window { L?: LeafletApi; } }
let leafletPromise: Promise<LeafletApi> | undefined;

function loadLeaflet(): Promise<LeafletApi> {
  if (window.L) return Promise.resolve(window.L);
  if (leafletPromise) return leafletPromise;
  leafletPromise = new Promise((resolve, reject) => {
    if (!document.querySelector('[data-sipandu-leaflet-css]')) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
      link.dataset.sipanduLeafletCss = "true";
      document.head.appendChild(link);
    }
    const script = document.createElement("script");
    script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
    script.onload = () => window.L ? resolve(window.L) : reject(new Error("Leaflet tidak tersedia."));
    script.onerror = () => {
      script.remove();
      leafletPromise = undefined;
      reject(new Error("Leaflet gagal dimuat."));
    };
    document.body.appendChild(script);
  });
  return leafletPromise;
}

export default function PetaMap({ data }: { data: PetaData }) {
  const mapRef = useRef<HTMLDivElement>(null);
  const [search, setSearch] = useState("");
  const [searchResults, setSearchResults] = useState<PetaSearchResult[]>([]);
  const [searching, setSearching] = useState(false);
  const [mapError, setMapError] = useState(false);

  useEffect(() => {
    let cancelled = false;
    let map: LeafletMap | undefined;
    loadLeaflet().then((L) => {
      if (cancelled || !mapRef.current) return;
      map = L.map(mapRef.current).setView([3.55, 125.5], 10);
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors", maxZoom: 18,
      }).addTo(map);
      const escape = (value: string) => value.replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character] || character);
      data.features.features.forEach((feature) => {
        const geometry = feature.geometry as { type?: string; coordinates?: unknown } | null;
        if (geometry?.type !== 'Point' || !Array.isArray(geometry.coordinates)) return;
        const [longitude, latitude] = geometry.coordinates;
        if (typeof latitude !== 'number' || typeof longitude !== 'number') return;
        const popup = `<strong>${escape(String(feature.properties.name ?? 'Fasilitas publik'))}</strong><br>${escape(String(feature.properties.dataset ?? ''))}<br>${escape(String(feature.properties.opd ?? ''))}`;
        L.circleMarker([latitude, longitude], { radius: 7, color: '#ffffff', fillColor: '#0f766e', fillOpacity: 0.95, weight: 2 }).bindPopup(popup).addTo(map!);
      });
    }).catch(() => { if (!cancelled) setMapError(true); });
    return () => { cancelled = true; map?.remove(); };
  }, []);

  const handleSearch = async () => {
    if (search.length < 3) return;
    setSearching(true);
    try {
      const json = await fetchPetaSearch(search);
      setSearchResults(json.data ?? []);
    } catch { setSearchResults([]); }
    setSearching(false);
  };

  const fmt = (n: number) => n.toLocaleString("id-ID");

  return (
    <div>
      <div ref={mapRef} className="h-[500px] w-full rounded-2xl border border-slate-200" />
      {mapError && <p role="alert" className="mt-3 text-sm text-amber-700">Peta gagal dimuat. Periksa koneksi internet, lalu muat ulang halaman.</p>}
      <div className="mt-4 grid gap-4 md:grid-cols-2">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700"><Layers className="h-4 w-4" />Cari fasilitas publik</h3>
          <div className="flex gap-2">
            <input type="text" placeholder="Nama fasilitas publik..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === "Enter" && handleSearch()} className="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
            <button onClick={handleSearch} disabled={searching} className="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-700 disabled:opacity-50"><Search className="h-4 w-4" /></button>
          </div>
          {searchResults.length > 0 && (
            <div className="mt-3 space-y-2">
              {searchResults.map((r, i) => (
                <div key={i} className="flex items-center gap-2 rounded-lg bg-slate-50 p-3 text-sm">
                  <MapPin className="h-4 w-4 flex-shrink-0 text-primary-600" />
                  <div>
                    <div className="font-medium">{r.properties.name as string}</div>
                    <div className="text-xs text-slate-500">{r.properties.opd as string} · {r.properties.dataset as string}</div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-3 text-sm font-semibold text-slate-700">Indikator publik per Kecamatan</h3>
          {data.kecamatans.length && data.indicators.length ? <div className="space-y-5">{data.indicators.slice(0, 4).map((indicator) => <div key={indicator.id}><p className="mb-2 text-sm font-medium text-slate-800">{indicator.label}{indicator.unit ? ` (${indicator.unit})` : ''}</p><div className="space-y-2 text-sm">{data.kecamatans.map((district) => { const value = indicator.values[String(district.id)]; return value === undefined ? null : <div key={district.id} className="flex justify-between text-slate-600"><span>{district.name}</span><span>{fmt(value)}</span></div>; })}</div></div>)}</div> : <p className="text-sm text-slate-500">Belum ada indikator agregat yang disetujui untuk peta publik.</p>}
        </div>
      </div>
    </div>
  );
}
