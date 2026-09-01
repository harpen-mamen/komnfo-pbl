"use client";
import { useEffect, useRef, useState } from "react";
import { MapPin, Search, Layers } from "lucide-react";

interface AreaStats { rumah: number; penduduk: number; }
interface PetaData { area_stats: Record<string, AreaStats>; summary: { penduduk: number; rumah: number; kecamatan: number }; }

export default function PetaMap({ data }: { data: PetaData }) {
  const mapRef = useRef<HTMLDivElement>(null);
  const [selected, setSelected] = useState<string>("all");
  const [search, setSearch] = useState("");
  const [searchResults, setSearchResults] = useState<any[]>([]);
  const [searching, setSearching] = useState(false);

  useEffect(() => {
    if (!mapRef.current || (window as any).L) return;
    const link = document.createElement("link");
    link.rel = "stylesheet"; link.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
    document.head.appendChild(link);
    const script = document.createElement("script");
    script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
    script.onload = () => initMap();
    document.body.appendChild(script);
  }, []);

  const initMap = () => {
    if (!mapRef.current || !(window as any).L) return;
    const L = (window as any).L;
    const map = L.map(mapRef.current).setView([3.55, 125.5], 10);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
      maxZoom: 18,
    }).addTo(map);
  };

  const handleSearch = async () => {
    if (search.length < 3) return;
    setSearching(true);
    try {
      const res = await fetch(`http://localhost:8000/api/v1/public/peta/cari?q=${encodeURIComponent(search)}`);
      const json = await res.json();
      setSearchResults(json.data ?? []);
    } catch { setSearchResults([]); }
    setSearching(false);
  };

  const fmt = (n: number) => n.toLocaleString("id-ID");

  return (
    <div>
      <div ref={mapRef} className="h-[500px] w-full rounded-2xl border border-slate-200" />
      <div className="mt-4 grid gap-4 md:grid-cols-2">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700"><Layers className="h-4 w-4" />Cari Keluarga</h3>
          <div className="flex gap-2">
            <input type="text" placeholder="Nama kepala keluarga..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === "Enter" && handleSearch()} className="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
            <button onClick={handleSearch} disabled={searching} className="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-700 disabled:opacity-50"><Search className="h-4 w-4" /></button>
          </div>
          {searchResults.length > 0 && (
            <div className="mt-3 space-y-2">
              {searchResults.map((r, i) => (
                <div key={i} className="flex items-center gap-2 rounded-lg bg-slate-50 p-3 text-sm">
                  <MapPin className="h-4 w-4 flex-shrink-0 text-primary-600" />
                  <div>
                    <div className="font-medium">{r.nama}</div>
                    <div className="text-xs text-slate-500">{r.desa}, {r.kecamatan}</div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h3 className="mb-3 text-sm font-semibold text-slate-700">Rekapitulasi per Kecamatan</h3>
          <div className="space-y-2 text-sm">
            <div className="flex justify-between border-b border-slate-100 pb-2 font-semibold text-slate-700">
              <span>Kecamatan</span><span>Rumah</span><span>Penduduk</span>
            </div>
            {Object.entries(data.area_stats).map(([kec, stats]) => (
              <div key={kec} className="flex justify-between text-slate-600">
                <span className="capitalize">{kec}</span><span>{fmt(stats.rumah)}</span><span>{fmt(stats.penduduk)}</span>
              </div>
            ))}
            <div className="flex justify-between border-t border-slate-200 pt-2 font-semibold text-slate-900">
              <span>Total</span><span>{fmt(data.summary.rumah)}</span><span>{fmt(data.summary.penduduk)}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
