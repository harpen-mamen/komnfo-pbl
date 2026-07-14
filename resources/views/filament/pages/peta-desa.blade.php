<x-filament::page>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <x-filament::button type="button" id="fit-bounds" icon="heroicon-o-arrows-expand">Fit Bounds</x-filament::button>
        <select id="zoom-marker" class="rounded-lg border-gray-300 text-sm shadow-sm">
            <option value="">Zoom To Marker</option>
        </select>
    </div>
    <div id="peta-desa" style="height: 72vh; min-height: 520px; border-radius: 8px; overflow: hidden; border: 1px solid #d1d5db;"></div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const markers = @json($this->markers);
            const first = markers[0] || { latitude: 3.6247, longitude: 125.5000 };
            const map = L.map('peta-desa').setView([first.latitude, first.longitude], markers.length ? 14 : 11);
            const markerLayer = L.layerGroup().addTo(map);
            const markerRefs = [];
            const bounds = L.latLngBounds();
            const zoomSelect = document.getElementById('zoom-marker');

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17,
                attribution: '&copy; OpenTopoMap contributors'
            });

            markers.forEach((rumah, index) => {
                const position = [rumah.latitude, rumah.longitude];
                const marker = L.marker(position).addTo(markerLayer).bindPopup(`
                    <strong>${escapeHtml(rumah.kode_rumah)}</strong><br>
                    ${escapeHtml(rumah.alamat)}<br>
                    Jumlah KK: ${rumah.jumlah_kk}<br>
                    Jumlah Penduduk: ${rumah.jumlah_penduduk}<br>
                    Status Rumah: ${escapeHtml(rumah.status_rumah)}
                `);
                markerRefs[index] = marker;
                bounds.extend(position);

                const option = document.createElement('option');
                option.value = index;
                option.textContent = rumah.kode_rumah;
                zoomSelect.appendChild(option);
            });

            L.control.layers({ 'OpenStreetMap': osm, 'Topografi': topo }, { 'Rumah': markerLayer }).addTo(map);

            const fitBounds = () => {
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [32, 32], maxZoom: 17 });
                }
            };

            document.getElementById('fit-bounds').addEventListener('click', fitBounds);
            zoomSelect.addEventListener('change', (event) => {
                const marker = markerRefs[event.target.value];

                if (marker) {
                    map.setView(marker.getLatLng(), 18);
                    marker.openPopup();
                }
            });

            fitBounds();

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, (match) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[match]));
            }
        });
    </script>
</x-filament::page>
