<div class="leadership-content">
    @include('filament.partials.leadership-page-header', ['title'=>'Pelaporan','description'=>'Rekapitulasi publikasi data OPD, frekuensi bulanan/triwulan/semester/tahunan, dan riwayat penerbitan.','sourceNote'=>'Yang dihitung adalah nilai terbit; jumlah kiriman admin bukan ukuran kondisi masyarakat.'])
    <div class="leadership-actions"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button><button type="button" onclick="exportLeadershipTables()">Unduh CSV untuk Excel</button></div>
    <section class="leadership-card">
        <h2>Rekap data terbit per OPD</h2>
        <p class="leadership-muted">Menunjukkan cakupan pelaporan yang tersedia, bukan penilaian kinerja atau kualitas OPD.</p>
        <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Perangkat daerah</th><th>Kode</th><th class="right">Dataset aktif</th><th class="right">Nilai terbit</th></tr></thead><tbody>
            @foreach ($monitoring['reports']['opds'] as $opd)
                <tr><td>{{ $opd['nama'] }}</td><td>{{ $opd['kode'] }}</td><td class="right">{{ number_format($opd['dataset_count'],0,',','.') }}</td><td class="right">{{ number_format($opd['published_values'],0,',','.') }}</td></tr>
            @endforeach
        </tbody></table></div>
    </section>
    <div class="leadership-grid">
        <section class="leadership-card"><h2>Frekuensi pelaporan</h2><p class="leadership-muted">Siklus yang ditetapkan pada dataset aktif beserta jumlah catatan terbit.</p>
            <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Frekuensi</th><th class="right">Dataset</th><th class="right">Nilai terbit</th></tr></thead><tbody>
                @foreach ($monitoring['reports']['cadence'] as $cadence)<tr><td>{{ ucfirst(str_replace('_',' ',$cadence['period'])) }}</td><td class="right">{{ number_format($cadence['datasets'],0,',','.') }}</td><td class="right">{{ number_format($cadence['published_values'],0,',','.') }}</td></tr>@endforeach
            </tbody></table></div>
        </section>
        <section class="leadership-card"><h2>Riwayat penerbitan</h2><p class="leadership-muted">Dikelompokkan berdasarkan tanggal publikasi pada sistem.</p>
            <div class="leadership-table-wrap"><table class="leadership-table monitor-export-table"><thead><tr><th>Bulan terbit</th><th class="right">Nilai</th><th class="right">Dataset</th></tr></thead><tbody>
                @forelse ($monitoring['reports']['history'] as $item)<tr><td>{{ $item['month'] }}</td><td class="right">{{ number_format($item['values_count'],0,',','.') }}</td><td class="right">{{ number_format($item['datasets_count'],0,',','.') }}</td></tr>@empty<tr><td colspan="3">Belum ada riwayat publikasi.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</div>
<script>
function exportLeadershipTables(){const lines=[];document.querySelectorAll('.monitor-export-table').forEach((table,index)=>{if(index)lines.push('');table.querySelectorAll('tr').forEach(row=>lines.push([...row.querySelectorAll('th,td')].map(cell=>'"'+cell.innerText.trim().replace(/\s+/g,' ').replace(/"/g,'""')+'"').join(',')));});const link=document.createElement('a');link.href=URL.createObjectURL(new Blob(['\ufeff'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'}));link.download='laporan-monitoring-sangihe.csv';link.click();URL.revokeObjectURL(link.href);}
</script>
