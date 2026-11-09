@php
    $apbd = $monitoring['apbd'];
    $demoRows = collect($monitoring['demo']['apbd']);
    $demoByYear = $demoRows->groupBy('tahun')->sortKeys();
    $money = fn ($value) => $value === null ? '—' : 'Rp '.number_format((float)$value,0,',','.');
    $number = fn ($value) => number_format((float)$value,0,',','.');
@endphp
<div class="leadership-content">
    @include('filament.partials.leadership-page-header', ['title'=>'Monitoring APBD','description'=>'Bandingkan pagu, realisasi keuangan, realisasi fisik, perangkat daerah, program, dan kegiatan.','sourceNote'=>'Sumber resmi: dataset BPKPD. Akun Bupati hanya membaca data yang sudah diterbitkan.'])
    <div class="leadership-kpis">
        <div class="leadership-kpi"><span>Pagu resmi</span><strong style="font-size:17px">{{ $money($apbd['pagu']) }}</strong><small>dari data BPKPD terbit</small></div>
        <div class="leadership-kpi"><span>Realisasi keuangan</span><strong style="font-size:17px">{{ $money($apbd['realization']) }}</strong><small>nilai resmi terbit</small></div>
        <div class="leadership-kpi"><span>Capaian keuangan</span><strong>{{ $apbd['financial_progress'] === null ? '—' : number_format($apbd['financial_progress'],1,',','.').'%' }}</strong><small>realisasi dibanding pagu</small></div>
        <div class="leadership-kpi"><span>Realisasi fisik</span><strong>{{ $apbd['physical_realization'] === null ? '—' : number_format($apbd['physical_realization'],1,',','.').'%' }}</strong><small>rata-rata catatan resmi</small></div>
    </div>
    @if ($apbd['financial_progress'] !== null)
        <section class="leadership-card"><h2>Perbandingan realisasi resmi</h2><div class="leadership-progress"><span style="width:{{ min(100,max(0,$apbd['financial_progress'])) }}%"></span></div><p class="leadership-muted">Realisasi keuangan {{ number_format($apbd['financial_progress'],1,',','.') }}% dari pagu.</p></section>
    @else
        <section class="leadership-card"><h2>Realisasi resmi dari BPKPD</h2><div class="leadership-empty"><strong>Belum ada nilai APBD terbit.</strong>Dataset anggaran tersedia, tetapi pagu dan realisasi resmi belum dipublikasikan BPKPD. Nilai contoh di bawah terpisah dan tidak menjadi laporan keuangan daerah.</div></section>
    @endif
    <section class="leadership-card" style="margin-top:14px">
        <h2>Data resmi menurut perangkat daerah dan program</h2>
        @if ($apbd['items'])
            <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>OPD</th><th>Program / kegiatan</th><th>Tahun</th><th class="right">Pagu</th><th class="right">Realisasi</th><th class="right">Capaian</th><th class="right">Fisik</th></tr></thead><tbody>
                @foreach ($apbd['items'] as $item)<tr><td>{{ $item['opd'] }}</td><td>{{ $item['program'] }}</td><td>{{ $item['year'] }}</td><td class="right">{{ $money($item['pagu']) }}</td><td class="right">{{ $money($item['realization']) }}</td><td class="right">{{ $item['progress']===null?'—':$item['progress'].'%' }}</td><td class="right">{{ $item['physical']===null?'—':$item['physical'].'%' }}</td></tr>@endforeach
            </tbody></table></div>
        @else
            <div class="leadership-empty">Belum ada rincian resmi untuk dibandingkan.</div>
        @endif
    </section>
    @if ($demoByYear->isNotEmpty())
        <div class="leadership-demo"><strong>SIMULASI · Bukan data APBD Sangihe</strong>Contoh pagu, realisasi, dan fisik berikut adalah draft dummy untuk pratinjau. Angka tidak bersumber dari BPKPD, tidak ikut laporan resmi, dan tidak boleh dijadikan dasar keputusan.</div>
        @foreach ($demoByYear as $year=>$rows)
            <section class="leadership-card" style="margin-top:12px">
                <h2>Contoh perbandingan anggaran · {{ $year }}</h2>
                <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>OPD contoh</th><th class="right">Pagu</th><th class="right">Realisasi keuangan</th><th class="right">%</th><th class="right">Realisasi fisik</th></tr></thead><tbody>
                    @foreach ($rows as $row)
                        @php $v=$row['values']; $pagu=(float)($v['pagu_anggaran']??0); $real=(float)($v['realisasi_anggaran']??0); @endphp
                        <tr><td>{{ $v['perangkat_daerah'] }}</td><td class="right">{{ $money($pagu) }}</td><td class="right">{{ $money($real) }}</td><td class="right">{{ number_format((float)($v['persen_realisasi']??0),1,',','.') }}%</td><td class="right">{{ number_format((float)($v['realisasi_fisik']??0),1,',','.') }}%</td></tr>
                        <tr><td colspan="5"><div class="leadership-progress"><span style="width:{{ min(100,$pagu>0?$real/$pagu*100:0) }}%"></span></div></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @endforeach
    @endif
    <section class="leadership-card" style="margin-top:14px">
        <h2>Dataset anggaran sumber</h2>
        <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Dataset</th><th>OPD pemilik</th><th class="right">Catatan terbit</th></tr></thead><tbody>
            @foreach ($apbd['datasets'] as $dataset)<tr><td>{{ $dataset['name'] }} ({{ $dataset['code'] }})</td><td>{{ $dataset['opd'] }}</td><td class="right">{{ $number($dataset['published']) }}</td></tr>@endforeach
        </tbody></table></div>
    </section>
</div>
