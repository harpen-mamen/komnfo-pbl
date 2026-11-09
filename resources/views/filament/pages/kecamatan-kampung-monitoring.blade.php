@php
    $summary=$monitoring['summary'];
    $datasetsByCode=collect($officialInfographics)->keyBy(fn($item)=>(string)$item['dataset']->kode);
    $number=fn($value)=>number_format((float)$value,0,',','.');
@endphp
<div class="leadership-content">
    @include('filament.partials.leadership-page-header', ['title'=>'Kecamatan dan Kampung','description'=>'Lihat profil agregat wilayah, penduduk, keluarga, pendidikan, kesehatan, infrastruktur dasar, ekonomi, dan pembangunan kampung.','sourceNote'=>'Tabel menggabungkan master wilayah dengan publikasi sektoral; tahun tiap indikator ditampilkan pada grafik sumber.'])
    <div class="leadership-kpis">
        <div class="leadership-kpi"><span>Kecamatan</span><strong>{{ $number($summary['district_count']) }}</strong><small>master wilayah</small></div>
        <div class="leadership-kpi"><span>Kampung / desa</span><strong>{{ $number($summary['village_count']) }}</strong><small>wilayah tingkat desa terdaftar</small></div>
        <div class="leadership-kpi"><span>Penduduk</span><strong>{{ $summary['population_total'] ? $number($summary['population_total']) : '—' }}</strong><small>jiwa · data {{ $summary['population_year'] ?? '—' }}</small></div>
        <div class="leadership-kpi"><span>Keluarga</span><strong>{{ $summary['household_total'] ? $number($summary['household_total']) : '—' }}</strong><small>KK · data {{ $summary['population_year'] ?? '—' }}</small></div>
    </div>
    <section class="leadership-card">
        <h2>Profil kecamatan</h2>
        <p class="leadership-muted">Nilai di bawah adalah jumlah anggota keluarga/penduduk agregat dan catatan sektoral terbit per kecamatan. Data keluarga perorangan tidak ditampilkan.</p>
        <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Kecamatan</th><th class="right">Penduduk</th><th class="right">KK</th><th class="right">Pendidikan</th><th class="right">Kesehatan</th><th class="right">Infrastruktur</th><th class="right">Ekonomi / potensi</th><th class="right">Pembangunan / sosial</th></tr></thead><tbody>
            @foreach ($monitoring['territory']['districts'] as $district)
                <tr><td><strong>{{ $district['nama'] }}</strong><div class="leadership-muted">{{ $number($district['village_count']) }} kampung/desa</div></td><td class="right">{{ $district['population']===null?'—':$number($district['population']) }}</td><td class="right">{{ $district['households']===null?'—':$number($district['households']) }}</td><td class="right">{{ $number($district['education_values']) }}</td><td class="right">{{ $number($district['health_values']) }}</td><td class="right">{{ $number($district['infrastructure_values']) }}</td><td class="right">{{ $number($district['economic_values']) }}</td><td class="right">{{ $number($district['community_development_values']) }}</td></tr>
            @endforeach
        </tbody></table></div>
        <div class="leadership-demo" style="border-color:#91c6b1;background:#f0f8f5;color:#42665a"><strong>Perlindungan data</strong>Halaman ini hanya memakai angka agregat dan data publik. Identitas keluarga, NIK, alamat rumah, serta catatan individual tidak ditampilkan.</div>
    </section>
    <section class="leadership-card" style="margin-top:14px">
        <h2>Infografis indikator wilayah</h2>
        <p class="leadership-muted">Data sektoral untuk melihat kondisi penduduk, layanan dasar, dan potensi ekonomi.</p>
        <div class="leadership-grid" style="align-items:start">
            @foreach ($officialInfographics as $item)
                <article class="rounded-xl border border-slate-200 bg-white p-4">
                    <h3 class="text-sm font-bold text-slate-900">{{ $item['dataset']->nama }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ implode(', ', $item['context']['years']) ?: 'Belum ada data terbit' }} · {{ $number($item['published_count']) }} catatan</p>
                    @forelse ($item['charts'] as $chart)<x-infographic-chart :chart="$chart" :variant="$loop->index" />@empty<div class="leadership-empty">Data belum tersedia; angka kosong bukan nol.</div>@endforelse
                    <p class="leadership-source"><strong>Sumber:</strong> {{ $item['context']['source'] }}@if($item['context']['source_table']), tabel {{ $item['context']['source_table'] }}@endif.</p>
                </article>
            @endforeach
        </div>
    </section>
</div>
