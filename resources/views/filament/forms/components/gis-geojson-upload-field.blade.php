<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="gisFileImporter({state:@entangle($getStatePath()).defer, inputId:@js($uploadId)})" class="space-y-3 rounded-xl border border-gray-200 bg-white p-4">
        <input :id="inputId" type="file" accept=".geojson,.json,.zip,application/geo+json,application/json,application/zip" class="block w-full text-sm" @change="read($event)">
        <p class="text-sm text-gray-600" x-text="message"></p>
        <template x-if="features.length">
            <div class="rounded-lg bg-gray-50 p-3 text-sm">
                <p class="font-semibold" x-text="features.length + ' objek siap diimpor (maksimal 500)' "></p>
                <p class="mt-1 text-xs text-gray-600">Contoh atribut: <span x-text="propertyNames.join(', ') || 'tidak ada' "></span></p>
                <p class="mt-1 text-xs text-gray-500">Preview: <span x-text="features.slice(0,3).map(f => f.geometry.type).join(', ') "></span></p>
            </div>
        </template>
    </div>
</x-dynamic-component>
<script>
window.gisFileImporter = window.gisFileImporter || (config => ({
    state: config.state, inputId: config.inputId, message: 'GeoJSON atau ZIP shapefile (.shp, .shx, .dbf, .prj) · maksimal 10 MB.', features: [], propertyNames: [],
    async read(event) {
        this.features = []; this.propertyNames = []; this.state = null;
        const file = event.target.files?.[0]; if (!file) return;
        if (file.size > 10 * 1024 * 1024) { this.message = 'File melebihi batas 10 MB.'; return; }
        try {
            let data;
            if (/\.zip$/i.test(file.name)) {
                this.message = 'Membaca shapefile dan mengubah koordinat ke WGS84…';
                const buffer = await file.arrayBuffer();
                const view = new DataView(buffer);
                let end = -1;
                for (let offset = view.byteLength - 22; offset >= Math.max(0, view.byteLength - 65557); offset--) {
                    if (view.getUint32(offset, true) === 0x06054b50 && offset + 22 + view.getUint16(offset + 20, true) === view.byteLength) { end = offset; break; }
                }
                if (end < 0) throw new Error('ZIP shapefile tidak terbaca.');
                const count = view.getUint16(end + 10, true), directory = view.getUint32(end + 16, true), names = [];
                let offset = directory, expandedBytes = 0;
                for (let i = 0; i < count; i++) {
                    if (view.getUint32(offset, true) !== 0x02014b50) throw new Error('Indeks ZIP shapefile tidak valid.');
                    const nameLength = view.getUint16(offset + 28, true), extraLength = view.getUint16(offset + 30, true), commentLength = view.getUint16(offset + 32, true);
                    names.push(new TextDecoder().decode(new Uint8Array(buffer, offset + 46, nameLength)).toLowerCase());
                    const uncompressed = view.getUint32(offset + 24, true);
                    if (uncompressed === 0xffffffff) throw new Error('ZIP64 belum didukung; simpan shapefile sebagai ZIP standar.');
                    expandedBytes += uncompressed;
                    if (expandedBytes > 30 * 1024 * 1024) throw new Error('Ukuran isi ZIP shapefile melebihi batas 30 MB.');
                    offset += 46 + nameLength + extraLength + commentLength;
                }
                const groups = new Map();
                for (const name of names) {
                    const match = name.match(/^(.*)\.(shp|shx|dbf|prj|cpg)$/i);
                    if (match) groups.set(match[1], [...(groups.get(match[1]) || []), match[2].toLowerCase()]);
                }
                const shapefiles = [...groups.values()].filter(parts => parts.includes('shp'));
                if (!shapefiles.length || shapefiles.some(parts => !['shp','shx','dbf','prj'].every(ext => parts.includes(ext)))) throw new Error('Setiap SHP harus memiliki SHX, DBF, dan PRJ dengan nama dasar yang sama.');
                const shp = await import('https://unpkg.com/shpjs@6.2.0/dist/shp.esm.js');
                const converted = await shp.default(buffer);
                data = Array.isArray(converted) ? {type:'FeatureCollection',features:converted.flatMap(layer => layer.features || [])} : converted;
            } else {
                this.message = 'Membaca GeoJSON…'; data = JSON.parse(await file.text());
            }
            const features = data?.type === 'Feature' ? [data] : data?.type === 'FeatureCollection' ? data.features : null;
            if (!Array.isArray(features) || !features.length || features.length > 500) throw new Error('File harus berisi 1–500 objek GeoJSON.');
            let vertices = 0;
            const count = value => { if (!Array.isArray(value)) return; if (value.length >= 2 && value.slice(0,2).every(Number.isFinite)) { vertices++; return; } value.forEach(count); };
            for (const feature of features) count(feature.geometry?.coordinates);
            if (vertices > 20000) throw new Error('Geometri melebihi batas 20.000 titik. Pecah berkas menjadi beberapa impor.');
            this.features = features;
            this.propertyNames = [...new Set(features.slice(0, 500).flatMap(f => Object.keys(f.properties || {})))].slice(0, 100);
            const payload = JSON.stringify({type:'FeatureCollection',features});
            if (new Blob([payload]).size > 5 * 1024 * 1024) throw new Error('Data hasil konversi melebihi 5 MB. Pecah berkas menjadi beberapa impor.');
            this.state = payload;
            this.message = 'File berhasil dibaca. Periksa pemetaan atribut sebelum menyimpan.';
        } catch (error) { this.state = null; this.message = error?.message || 'File tidak dapat dibaca. Periksa format dan komponen shapefile.'; }
    }
}));
</script>
