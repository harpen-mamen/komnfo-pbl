<div
    wire:ignore
    x-data="{
        map: null,
        marker: null,
        popup: null,
        lat: @entangle('data.latitude').defer,
        lng: @entangle('data.longitude').defer,
        initMap() {
            if (this.map) {
                return;
            }

            const load = () => {
                if (this.map) {
                    return;
                }

                const startLat = parseFloat(this.lat) || 3.6247;
                const startLng = parseFloat(this.lng) || 125.5000;
                this.map = L.map(this.$refs.map).setView([startLat, startLng], 14);

                const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);

                const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri'
                });

                const labels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Labels &copy; Esri'
                });

                L.control.layers(
                    { 'Peta Jalan': street, 'Satelit': satellite },
                    { 'Label Satelit': labels },
                    { collapsed: false }
                ).addTo(this.map);

                this.marker = L.marker([startLat, startLng], { draggable: true }).addTo(this.map);
                this.popup = L.popup();
                this.marker.on('dragend', () => this.setPoint(this.marker.getLatLng(), true));
                this.map.on('click', (event) => this.setPoint(event.latlng, true));
                this.setPoint({ lat: startLat, lng: startLng }, false);

                this.$watch('lat', () => this.syncMarkerFromInputs());
                this.$watch('lng', () => this.syncMarkerFromInputs());

                setTimeout(() => this.map.invalidateSize(), 250);
            };

            if (window.L) {
                load();
                return;
            }

            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = load;
            document.body.appendChild(script);
        },
        setPoint(point, persist = true) {
            const lat = Number(point.lat).toFixed(7);
            const lng = Number(point.lng).toFixed(7);
            const position = L.latLng(lat, lng);

            this.lat = lat;
            this.lng = lng;
            this.marker.setLatLng(position);
            this.popup
                .setLatLng(position)
                .setContent(`<strong>Koordinat dipilih</strong><br>Latitude: ${lat}<br>Longitude: ${lng}`)
                .openOn(this.map);

            if (persist) {
                this.$wire.set('data.latitude', lat);
                this.$wire.set('data.longitude', lng);
            }
        },
        syncMarkerFromInputs() {
            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);

            if (! this.map || ! this.marker || Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            const position = L.latLng(lat, lng);
            this.marker.setLatLng(position);
        },
        useCurrentPoint() {
            if (! this.marker) {
                return;
            }

            this.setPoint(this.marker.getLatLng(), true);
        }
    }"
    x-init="initMap()"
    class="space-y-3"
>
    <div x-ref="map" style="height: 360px; border-radius: 8px; overflow: hidden; border: 1px solid #d1d5db;"></div>
    <div class="flex flex-wrap items-center gap-3">
        <x-filament::button type="button" size="sm" x-on:click="useCurrentPoint()">
            Gunakan Titik Ini
        </x-filament::button>

        <div class="text-sm text-gray-600">
            Latitude: <span class="font-medium" x-text="lat || '-'"></span>,
            Longitude: <span class="font-medium" x-text="lng || '-'"></span>
        </div>
    </div>
</div>
