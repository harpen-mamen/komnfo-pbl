<div
    wire:ignore
    x-data="{
        map: null,
        marker: null,
        popup: null,
        lat: <?php if ((object) ('data.latitude') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('data.latitude'->value()); ?>')<?php echo e('data.latitude'->hasModifier('defer') ? '.defer' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('data.latitude'); ?>')<?php endif; ?>.defer,
        lng: <?php if ((object) ('data.longitude') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('data.longitude'->value()); ?>')<?php echo e('data.longitude'->hasModifier('defer') ? '.defer' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($_instance->id); ?>').entangle('<?php echo e('data.longitude'); ?>')<?php endif; ?>.defer,
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
        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button','data' => ['type' => 'button','size' => 'sm','xOn:click' => 'useCurrentPoint()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','size' => 'sm','x-on:click' => 'useCurrentPoint()']); ?>
            Gunakan Titik Ini
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

        <div class="text-sm text-gray-600">
            Latitude: <span class="font-medium" x-text="lat || '-'"></span>,
            Longitude: <span class="font-medium" x-text="lng || '-'"></span>
        </div>
    </div>
</div>
<?php /**PATH C:\pbl\resources\views\filament\forms\leaflet-picker.blade.php ENDPATH**/ ?>