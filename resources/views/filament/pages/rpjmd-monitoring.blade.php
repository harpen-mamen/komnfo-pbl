@php
    $rpjmd = $monitoring['rpjmd'];
    $demoRows = collect($monitoring['demo']['rpjmd']);
    $demoByIndicator = $demoRows->groupBy(fn ($row) => $row['values']['indikator'] ?? 'Indikator simulasi');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
@endphp
<div class="leadership-content">
    @include('filament.partials.leadership-page-header', ['title'=>'Monitoring RPJMD','description'=>'Pantau indikator tujuan, sasaran, kinerja utama, program prioritas, target tahunan, realisasi, dan persentase capaian.','sourceNote'=>'Angka resmi hanya berasal dari data terbit Bappelitbang/Bappeda.'])
    <div class="leadership-kpis">
        <div class="leadership-kpi"><span>Dataset RPJMD terdeteksi</span><strong>{{ count($rpjmd['datasets']) }}</strong><small>di katalog data aktif</small></div>
        <div class="leadership-kpi"><span>Indikator resmi terbit</span><strong>{{ $rpjmd['published_values'] }}</strong><small>catatan dengan status terbit</small></div>
        <div class="leadership-kpi"><span>Target tahunan resmi</span><strong>—</strong><small>belum tersedia dari sumber</small></div>
        <div class="leadership-kpi"><span>Capaian resmi</span><strong>—</strong><small>menunggu data Bappelitbang</small></div>
    </div>
    <section class="leadership-card">
        <h2>Tujuan, sasaran, dan indikator kinerja utama</h2>
        <p class="leadership-muted">Data resmi yang diterbitkan menjadi dasar capaian di bagian ini.</p>
        @if ($rpjmd['indicators'])
            <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Indikator / sasaran</th><th>Tahun</th><th class="right">Target</th><th class="right">Realisasi</th><th class="right">Capaian</th></tr></thead><tbody>
                @foreach ($rpjmd['indicators'] as $row)
                    <tr><td>{{ $row['name'] }}<div class="leadership-muted">{{ $row['objective'] }}</div></td><td>{{ $row['year'] }}</td><td class="right">{{ $row['target'] ?? '—' }} {{ $row['unit'] }}</td><td class="right">{{ $row['actual'] ?? '—' }} {{ $row['unit'] }}</td><td class="right">{{ $row['progress'] === null ? '—' : number_format($row['progress'],1,',','.').'%' }}@if($row['progress'] !== null)<div class="leadership-progress"><span style="width:{{ min(100,max(0,$row['progress'])) }}%"></span></div>@endif</td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="leadership-empty"><strong>Belum ada capaian RPJMD resmi yang diterbitkan.</strong>Dataset IKU sudah tersedia, tetapi target/realisasi belum masuk. Nilai simulasi di bawah hanya untuk pratinjau tampilan dan tidak mewakili capaian Sangihe.</div>
        @endif
    </section>
    <section class="leadership-card" style="margin-top:14px">
        <h2>Dataset sumber</h2>
        <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Dataset</th><th>OPD</th><th class="right">Nilai resmi terbit</th></tr></thead><tbody>
            @foreach ($rpjmd['datasets'] as $dataset)
                <tr><td>{{ $dataset['name'] }} <span class="leadership-muted">({{ $dataset['code'] }})</span></td><td>{{ $dataset['opd'] }}</td><td class="right">{{ $number($dataset['published']) }}</td></tr>
            @endforeach
        </tbody></table></div>
    </section>
    @if ($demoByIndicator->isNotEmpty())
        <div class="leadership-demo"><strong>SIMULASI · Bukan capaian resmi</strong>Baris dan grafik berikut adalah data dummy draft untuk mencoba tampilan. Data ini tidak dihitung pada statistik resmi dan tidak boleh dipakai sebagai laporan kinerja.</div>
        @foreach ($demoByIndicator as $indicator=>$rows)
            <section class="leadership-card" style="margin-top:12px">
                <h2>{{ $indicator }}</h2>
                <p class="leadership-muted">Contoh target dan realisasi tahunan · kategori {{ $rows->first()['values']['kategori'] ?? '—' }}</p>
                <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Tahun</th><th class="right">Target</th><th class="right">Realisasi</th><th class="right">Contoh capaian</th></tr></thead><tbody>
                    @foreach ($rows as $row)
                        @php $v=$row['values']; $target=(float)($v['target']??0); $actual=(float)($v['realisasi']??0); $percent=$target>0?round($actual/$target*100,1):null; @endphp
                        <tr><td>{{ $row['tahun'] }}</td><td class="right">{{ $v['target'] }} {{ $v['satuan']??'' }}</td><td class="right">{{ $v['realisasi'] }} {{ $v['satuan']??'' }}</td><td class="right">{{ $percent }}%<div class="leadership-progress"><span style="width:{{ min(100,$percent) }}%"></span></div></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @endforeach
    @endif
</div>
