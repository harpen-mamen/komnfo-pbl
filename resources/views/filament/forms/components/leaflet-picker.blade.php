<div class="filament-forms-field-wrapper">
    <div class="space-y-2">
        <label class="filament-forms-field-wrapper-label">
            <span class="text-sm font-medium leading-4 text-gray-700">Pilih Lokasi di Peta</span>
        </label>
        <div class="flex items-center gap-2">
            <input type="text" id="lp-display-{{ $lpId }}" readonly placeholder="Klik tombol untuk pilih lokasi" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 cursor-pointer" style="background:#f9fafb;" />
            <button type="button" id="lp-btn-{{ $lpId }}" data-lp-id="{{ $lpId }}" class="lp-open-btn inline-flex items-center justify-center px-3 py-2 text-sm font-medium tracking-wide text-white transition-colors border border-transparent rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none" style="background-color:#dc2626; white-space:nowrap;">&#128205; Pilih di Peta</button>
        </div>
        <p class="text-xs text-gray-500">Klik tombol untuk memilih titik koordinat rumah di peta interaktif.</p>
    </div>
</div>

<input type="hidden" wire:model="koordinat.latitude" />
<input type="hidden" wire:model="koordinat.longitude" />

<div id="lp-modal-{{ $lpId }}" class="lp-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:99999; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:90%; max-width:900px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#f9fafb;">
            <h3 style="margin:0; font-size:16px; font-weight:600; color:#111827;">Pilih Titik Koordinat</h3>
            <button type="button" class="lp-close-btn" data-lp-id="{{ $lpId }}" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
        </div>
        <div id="lp-map-{{ $lpId }}" style="height:500px; width:100%;"></div>
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-top:1px solid #e5e7eb; background:#f9fafb;">
            <div style="display:flex; gap:12px; font-size:13px;">
                <span style="padding:4px 10px; background:#dbeafe; color:#1e40af; border-radius:9999px; font-weight:500;">Lat: <span id="lp-lat-display-{{ $lpId }}">-</span></span>
                <span style="padding:4px 10px; background:#dcfce7; color:#166534; border-radius:9999px; font-weight:500;">Lng: <span id="lp-lng-display-{{ $lpId }}">-</span></span>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="lp-close-btn" data-lp-id="{{ $lpId }}" style="padding:8px 16px; border-radius:6px; border:1px solid #d1d5db; background:white; font-size:13px; cursor:pointer;">Batal</button>
                <button type="button" class="lp-confirm-btn" data-lp-id="{{ $lpId }}" style="padding:8px 16px; border-radius:6px; border:none; background:#dc2626; color:white; font-size:13px; cursor:pointer; font-weight:500;">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
console.log("[LP] LeafletPicker script loaded");
function lpGetVals(){
var la="",ln="";
var all=document.querySelectorAll("input[wire\\:model]");
for(var i=0;i<all.length;i++){
var w=all[i].getAttribute("wire:model")||"";
if(w.indexOf("latitude")!==-1)la=all[i].value||"";
if(w.indexOf("longitude")!==-1)ln=all[i].value||"";
}
return{lat:la,lng:ln};
}
function lpSetVals(la,ln){
var all=document.querySelectorAll("input[wire\\:model]");
for(var i=0;i<all.length;i++){
var w=all[i].getAttribute("wire:model")||"";
if(w.indexOf("latitude")!==-1){all[i].value=la;all[i].dispatchEvent(new Event("input",{bubbles:true}));}
if(w.indexOf("longitude")!==-1){all[i].value=ln;all[i].dispatchEvent(new Event("input",{bubbles:true}));}
}
}
function lpInit(id,la,ln){
console.log("[LP] lpInit id="+id+" lat="+la+" lng="+ln);
var me=document.getElementById("lp-map-"+id);if(!me){console.error("[LP] map el not found");return;}
var inst=window.__lpi=window.__lpi||{};inst[id]=inst[id]||{map:null,marker:null,lat:"",lng:""};
inst[id].lat=parseFloat(la).toFixed(7)||-3.0;inst[id].lng=parseFloat(ln).toFixed(7)||121.0;
if(inst[id].map){inst[id].map.invalidateSize();return;}
var sl=parseFloat(la)||-3.0;var sn=parseFloat(ln)||121.0;
try{inst[id].map=L.map(me,{center:[sl,sn],zoom:14}).on("click",function(e){lpClick(id,e.latlng.lat,e.latlng.lng);});}
catch(e){console.error("Leaflet error:",e);return;}
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19,attribution:"OSM"}).addTo(inst[id].map);
L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",{maxZoom:19}).addTo(inst[id].map);
L.control.layers({"Jalan":inst[id].map._layers[Object.keys(inst[id].map._layers)[0]],"Satelit":inst[id].map._layers[Object.keys(inst[id].map._layers)[1]]||inst[id].map._layers[Object.keys(inst[id].map._layers)[0]]},{},{collapsed:false}).addTo(inst[id].map);
lpClick(id,la,ln);
setTimeout(function(){if(inst[id].map)inst[id].map.invalidateSize();},300);
}
function lpClick(id,la,ln){
var inst=window.__lpi&&window.__lpi[id];if(!inst)return;
la=parseFloat(la).toFixed(7);ln=parseFloat(ln).toFixed(7);
inst.lat=la;inst.lng=ln;
var ld=document.getElementById("lp-lat-display-"+id);
var lg=document.getElementById("lp-lng-display-"+id);
if(ld)ld.textContent=la;if(lg)lg.textContent=ln;
if(!inst.map)return;
if(inst.marker)inst.marker.setLatLng([la,ln]);
else inst.marker=L.marker([la,ln],{draggable:true}).addTo(inst.map);
inst.marker.bindPopup("<b>Koordinat<\/b><br>Lat: "+la+"<br>Lng: "+ln).openPopup();
inst.marker.on("dragend",function(){var ll=inst.marker.getLatLng();lpClick(id,ll.lat,ll.lng);});
inst.map.setView([la,ln],Math.max(inst.map.getZoom(),15));
}
function lpAttachAll(){
var btns=document.querySelectorAll(".lp-open-btn");
console.log("[LP] lpAttachAll, buttons:", btns.length);
btns.forEach(function(b){if(b.dataset.lpBound)return;b.dataset.lpBound="1";b.addEventListener("click",function(){var id=b.dataset.lpId;var m=document.getElementById("lp-modal-"+id);if(!m)return;m.style.display="flex";window.__lpi=window.__lpi||{};window.__lpi[id]=window.__lpi[id]||{map:null,marker:null,lat:"",lng:""};var v=lpGetVals();window.__lpi[id].lat=v.lat;window.__lpi[id].lng=v.lng;var ld=document.getElementById("lp-lat-display-"+id);var lg=document.getElementById("lp-lng-display-"+id);if(ld)ld.textContent=v.lat||"-";if(lg)lg.textContent=v.lng||"-";if(window.L)lpInit(id,v.lat,v.lng);else{var le=document.createElement("link");le.rel="stylesheet";le.href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";document.head.appendChild(le);var s=document.createElement("script");s.src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";s.onload=function(){lpInit(id,v.lat,v.lng)};document.body.appendChild(s);}});});document.querySelectorAll(".lp-close-btn").forEach(function(b){b.addEventListener("click",function(){var id=b.dataset.lpId;var m=document.getElementById("lp-modal-"+id);if(m)m.style.display="none";});});document.querySelectorAll(".lp-confirm-btn").forEach(function(b){b.addEventListener("click",function(){var id=b.dataset.lpId;var inst=window.__lpi&&window.__lpi[id];if(!inst)return;lpSetVals(inst.lat,inst.lng);var inp=document.getElementById("lp-display-"+id);if(inp)inp.value=(inst.lat&&inst.lng)?inst.lat+", "+inst.lng:"";var m=document.getElementById("lp-modal-"+id);if(m)m.style.display="none";});});document.querySelectorAll(".lp-modal").forEach(function(m){m.addEventListener("click",function(e){if(e.target===m)m.style.display="none";});});
}
lpAttachAll();
})();
</script>
