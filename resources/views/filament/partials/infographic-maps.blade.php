<script>
(() => {
    const maps = () => Array.from(document.querySelectorAll('[data-map-features]')).map(element => ({id:element.id,features:JSON.parse(element.dataset.mapFeatures)}));
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const render = () => maps().forEach(item => {
        const element = document.getElementById(item.id);
        if (!window.L || !element || element.dataset.ready) return;
        element.dataset.ready = '1';
        element.replaceChildren();
        const map = L.map(element).setView([3.6247,125.5],10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
        const layer = L.geoJSON(item.features,{
            style: {color:'#194c48',weight:4,fillOpacity:.2},
            onEachFeature: (feature, target) => {
                const props=feature.properties||{};
                const attrs=Object.entries(props.attributes||{}).map(([key,value])=>`<p><strong>${escape(key)}:</strong> ${escape(value)}</p>`).join('');
                target.bindPopup(`<strong>${escape(props.name||'Lokasi')}</strong><p>${escape(props.opd)} &middot; ${escape(props.year)}</p>${attrs}`);
            }
        }).addTo(map);
        if (layer.getBounds().isValid()) map.fitBounds(layer.getBounds(),{padding:[24,24],maxZoom:15});
        setTimeout(()=>map.invalidateSize(),100);
    });
    const run = () => {
    if (!maps().length) return;
    if (window.L) { render(); return; }
    if (!document.querySelector('link[data-infographic-leaflet]')) {
        const css=document.createElement('link'); css.rel='stylesheet'; css.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; css.dataset.infographicLeaflet='1'; document.head.appendChild(css);
    }
    let script=document.querySelector('script[data-infographic-leaflet]');
    if (script) { script.addEventListener('load',render,{once:true}); return; }
    script=document.createElement('script'); script.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'; script.dataset.infographicLeaflet='1'; script.addEventListener('load',render,{once:true}); document.head.appendChild(script);
    };
    run();
    if (window.Livewire) Livewire.hook('message.processed', run);
})();
</script>
