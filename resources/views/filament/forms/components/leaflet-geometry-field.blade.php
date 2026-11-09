<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="datasetGeometryPicker({ state: @entangle($getStatePath()).defer, geometryType: @js($geometryType), mapId: @js($mapId) })"
        x-init="init()"
        class="space-y-2"
    >
        <div :id="mapId" style="width: 100%; height: clamp(420px, 55vh, 560px); border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden;"></div>
        <div class="flex flex-wrap gap-2">
            <button type="button" x-show="allows('linestring')" x-on:click="startDrawing('line')" class="rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white">Gambar garis jalan</button>
            <button type="button" x-show="allows('polygon')" x-on:click="startDrawing('area')" class="rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white">Gambar area</button>
            <button type="button" x-show="allows('point')" x-on:click="startDrawing('point')" class="rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white">Tandai titik</button>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
            <span x-text="geometryLabel"></span>
            <button type="button" x-on:click="clearGeometry()" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold hover:bg-gray-50">
                Hapus gambar
            </button>
        </div>
        <p class="text-xs text-gray-500">Pilih Jalan atau Satelit melalui kontrol lapisan di kanan atas peta. Untuk jalan: tekan Gambar garis, klik titik-titik jalur, lalu klik dua kali untuk selesai. Data lokasi ikut tersimpan bersama kiriman OPD.</p>
        <p x-show="loadError" x-text="loadError" class="text-sm text-danger-600" role="alert"></p>
    </div>
</x-dynamic-component>

<script>
window.datasetGeometryPicker = window.datasetGeometryPicker || ((config) => ({
    state: config.state,
    geometryType: config.geometryType,
    mapId: config.mapId,
    map: null,
    drawn: null,
    resizeObserver: null,
    drawHandlers: {},
    loadError: null,
    geometryLabel: 'Belum ada lokasi yang dipilih',
    async init() {
        try { await this.loadLeaflet(); } catch (_) { this.loadError = 'Peta gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.'; return; }
        const mapElement = document.getElementById(this.mapId);
        if (!mapElement || this.map) return;

        this.map = L.map(mapElement).setView([3.6247, 125.5], 10);
        this.resizeObserver = new ResizeObserver(() => this.map?.invalidateSize());
        this.resizeObserver.observe(mapElement);
        const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(this.map);
        const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
        });
        L.control.layers({ 'Jalan': street, 'Satelit': satellite }, {}, {
            position: 'topright',
            collapsed: false
        }).addTo(this.map);
        this.drawn = new L.FeatureGroup().addTo(this.map);
        this.map.on(L.Draw.Event.CREATED, (event) => {
            this.drawn.clearLayers();
            this.drawn.addLayer(event.layer);
            this.saveLayer(event.layer);
        });
        this.map.on(L.Draw.Event.EDITED, () => this.saveCurrentLayer());
        this.map.on(L.Draw.Event.DELETED, () => {
            this.state = null;
            this.geometryLabel = 'Belum ada lokasi yang dipilih';
        });
        this.map.on('click', (event) => {
            if (this.geometryType !== 'point' && this.geometryType !== 'any') return;
            this.drawn.clearLayers();
            const marker = L.marker(event.latlng);
            this.drawn.addLayer(marker);
            this.saveLayer(marker);
        });

        const draw = {
            polyline: this.allows('linestring') ? { shapeOptions: { color: '#0284c7', weight: 5 } } : false,
            polygon: this.allows('polygon') ? { allowIntersection: false, shapeOptions: { color: '#0f766e', fillOpacity: 0.22 } } : false,
            marker: this.allows('point') ? {} : false,
            rectangle: false,
            circle: false,
            circlemarker: false
        };
        new L.Control.Draw({ draw, edit: { featureGroup: this.drawn, remove: true } }).addTo(this.map);
        this.drawHandlers = {
            line: new L.Draw.Polyline(this.map, { shapeOptions: { color: '#0f766e', weight: 5 } }),
            area: new L.Draw.Polygon(this.map, { allowIntersection: false, shapeOptions: { color: '#0f766e', fillOpacity: 0.22 } }),
            point: new L.Draw.Marker(this.map)
        };

        if (this.state) {
            try {
                const geometry = this.state.type === 'Feature' ? this.state : {type: 'Feature', properties: {}, geometry: this.state};
                const layer = L.geoJSON(geometry).getLayers()[0];
                if (layer) {
                    if (layer.eachLayer) layer.eachLayer(child => this.drawn.addLayer(child));
                    else this.drawn.addLayer(layer);
                    const bounds = layer.getBounds?.();
                    if (bounds?.isValid()) this.map.fitBounds(bounds, { padding: [28, 28] });
                    else if (layer.getLatLng) this.map.setView(layer.getLatLng(), 14);
                    this.updateLabel(this.state);
                }
            } catch (_) { this.geometryLabel = 'Geometri tersimpan tidak dapat ditampilkan; gambar ulang bila perlu'; }
        }
        setTimeout(() => this.map.invalidateSize(), 250);
    },
    allows(type) { return this.geometryType === 'any' || this.geometryType === type; },
    startDrawing(type) { this.drawHandlers[type]?.enable(); },
    saveLayer(layer) {
        this.state = layer.toGeoJSON();
        this.updateLabel(this.state);
    },
    saveCurrentLayer() {
        const layers = this.drawn.getLayers();
        if (!layers.length) return;
        if (layers.length === 1) return this.saveLayer(layers[0]);
        const features = layers.map(layer => layer.toGeoJSON());
        const types = [...new Set(features.map(feature => feature.geometry.type))];
        if (types.length !== 1 || !['Point', 'LineString', 'Polygon'].includes(types[0])) return;
        this.state = { type: 'Feature', properties: {}, geometry: { type: 'Multi' + types[0], coordinates: features.map(feature => feature.geometry.coordinates) } };
        this.updateLabel(this.state);
    },
    updateLabel(feature) {
        const type = feature?.geometry?.type || 'Geometri';
        this.geometryLabel = type.includes('Point') ? 'Lokasi titik tersimpan' : type.includes('LineString') ? 'Jalur garis tersimpan' : 'Area peta tersimpan';
    },
    clearGeometry() {
        this.drawn?.clearLayers();
        this.state = null;
        this.geometryLabel = 'Belum ada lokasi yang dipilih';
    },
    loadLeaflet() {
        if (!document.querySelector('link[data-dataset-leaflet]')) {
            const leafletCss = document.createElement('link');
            leafletCss.rel = 'stylesheet';
            leafletCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            leafletCss.dataset.datasetLeaflet = 'true';
            document.head.appendChild(leafletCss);
            const drawCss = document.createElement('link');
            drawCss.rel = 'stylesheet';
            drawCss.href = 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css';
            document.head.appendChild(drawCss);
        }
        if (window.L?.Control?.Draw) return Promise.resolve();
        if (window.__datasetLeafletPromise) return window.__datasetLeafletPromise;
        window.__datasetLeafletPromise = new Promise((resolve, reject) => {
            const loadScript = (src, done) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = done;
                script.onerror = reject;
                document.head.appendChild(script);
            };
            const startDraw = () => loadScript('https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', resolve);
            if (window.L) startDraw();
            else loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', startDraw);
        });
        return window.__datasetLeafletPromise;
    }
}));
</script>
