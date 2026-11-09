@php
    $infrastructure=$monitoring['infrastructure'];
    $demoRows=collect($monitoring['demo']['infrastructure']);
    $demoPoints=$demoRows->map(function($row){$v=$row['values'];$coordinates=data_get($v,'geometri_peta.geometry.coordinates',[]);return ['name'=>$v['nama']??'SIMULASI','type'=>$v['jenis']??'Simulasi','status'=>$v['status']??'','lat'=>$coordinates[1]??null,'lng'=>$coordinates[0]??null,'demo'=>true];})->filter(fn($p)=>is_numeric($p['lat'])&&is_numeric($p['lng']))->values();
    $realPoints=collect($infrastructure['map_points'])->map(fn($p)=>$p+['status'=>'Rujukan publik terbit','demo'=>false])->values();
    $inventory=collect($infrastructure['inventory']);
@endphp
<div class="leadership-content">
    @include('filament.partials.leadership-page-header', ['title'=>'Infrastruktur Digital','description'=>'Pemetaan BTS, blankspot, penyedia internet, BAKTI/Starlink, fiber optik, gangguan jaringan, dan titik layanan publik.','sourceNote'=>'Inventaris jaringan harus berasal dari data terverifikasi Diskominfo/operator; peta layanan publik bukan peta cakupan sinyal.'])
    <section class="leadership-card">
        <h2>Status inventaris resmi</h2>
        <p class="leadership-muted">Status berikut berasal dari nilai yang sudah diterbitkan pada dataset aktif.</p>
        <div class="leadership-kpis">
            @foreach ($inventory as $item)
                <div class="leadership-kpi"><span>{{ $item['label'] }}</span><strong>{{ $item['published_values'] ? number_format($item['published_values'],0,',','.') : '—' }}</strong><small>{{ $item['published_values'] ? 'catatan resmi terbit' : 'belum ada data resmi terbit' }}</small></div>
            @endforeach
        </div>
        <div class="leadership-empty"><strong>Inventaris BTS, blankspot, ISP, BAKTI/Starlink, fiber optik, dan gangguan belum memiliki data terbit.</strong>Titik lapangan mobile tersimpan: {{ number_format($infrastructure['mobile_points'],0,',','.') }}. Kiriman titik mobile menunjukkan lokasi pengambilan petugas, bukan status konektivitas.</div>
    </section>
    @if ($demoPoints->isNotEmpty())
        <div class="leadership-demo"><strong>SIMULASI · Titik dan status jaringan fiktif</strong>Peta ungu di bawah menggunakan koordinat contoh untuk melihat tata letak. Titik-titik tersebut bukan lokasi BTS, bukan titik blankspot, dan tidak menggambarkan keadaan jaringan Sangihe.</div>
    @endif
    <section class="leadership-card" style="margin-top:14px">
        <h2>Peta GIS</h2>
        <p class="leadership-muted">Titik hijau adalah rujukan publik layanan kesehatan/transportasi. Titik ungu adalah simulasi dan tidak mewakili aset nyata.</p>
        @if ($realPoints->isNotEmpty() || $demoPoints->isNotEmpty())
            <div id="leadership-infrastructure-map" class="leadership-map" aria-label="Peta infrastruktur digital dan layanan publik"></div>
            <p class="leadership-source">{{ $realPoints->count() }} titik rujukan layanan publik terbit · {{ $demoPoints->count() }} titik simulasi. Lokasi fasilitas rujukan bukan verifikasi batas bangunan.</p>
        @else
            <div class="leadership-empty">Belum ada geometri yang layak ditampilkan.</div>
        @endif
    </section>
    @if ($demoRows->isNotEmpty())
        <section class="leadership-card" style="margin-top:14px">
            <h2>Contoh kategori untuk pengembangan inventaris</h2>
            <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Jenis</th><th>Nama contoh</th><th>Penyedia contoh</th><th>Kecamatan contoh</th><th>Status contoh</th></tr></thead><tbody>
                @foreach ($demoRows as $row)
                    @php $v=$row['values']; @endphp
                    <tr><td>{{ $v['jenis'] }}</td><td>{{ $v['nama'] }}</td><td>{{ $v['provider']??'—' }}</td><td>{{ $v['kecamatan'] }}</td><td>{{ $v['status'] }}</td></tr>
                @endforeach
            </tbody></table></div>
        </section>
    @endif
</div>
@if ($realPoints->isNotEmpty() || $demoPoints->isNotEmpty())
<script>
(function(){
    const points=[...@json($realPoints),...@json($demoPoints)];
    const start=()=>{const node=document.getElementById('leadership-infrastructure-map');if(!node||node.dataset.ready||!window.L)return;node.dataset.ready='1';const map=L.map(node,{scrollWheelZoom:false}).setView([3.62,125.5],9);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18,attribution:'&copy; OpenStreetMap'}).addTo(map);const bounds=[];points.forEach(p=>{const color=p.demo?'#7c3aed':'#14766f';const marker=L.circleMarker([p.lat,p.lng],{radius:p.demo?8:6,color,fillColor:color,fillOpacity:.9,weight:2}).addTo(map);marker.bindPopup('<strong>'+String(p.name).replace(/[<>]/g,'')+'</strong><br>'+String(p.type).replace(/[<>]/g,'')+'<br>'+String(p.status||'').replace(/[<>]/g,'')+(p.demo?'<br><b>SIMULASI · BUKAN DATA RESMI</b>':''));bounds.push([p.lat,p.lng]);});if(bounds.length>1)map.fitBounds(bounds,{padding:[20,20],maxZoom:11});setTimeout(()=>map.invalidateSize(),150);};
    if(window.L){start();return;}if(!document.querySelector('link[data-leadership-map-css]')){const css=document.createElement('link');css.rel='stylesheet';css.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';css.dataset.leadershipMapCss='1';document.head.appendChild(css);}const existing=document.querySelector('script[data-leadership-map-js]');if(existing){existing.addEventListener('load',start,{once:true});return;}const js=document.createElement('script');js.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';js.dataset.leadershipMapJs='1';js.onload=start;document.body.appendChild(js);
})();
</script>
@endif
