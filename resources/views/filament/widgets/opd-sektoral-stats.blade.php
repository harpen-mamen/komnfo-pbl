<style>
.opd-stats-widget { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.opd-stats-widget * { box-sizing: border-box; }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
.stat-card { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center; }
.stat-value { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-card.draft { border-left: 3px solid #94a3b8; }
.stat-card.submitted { border-left: 3px solid #f59e0b; }
.stat-card.verified { border-left: 3px solid #3b82f6; }
.stat-card.published { border-left: 3px solid #10b981; }
.stat-card.rejected { border-left: 3px solid #ef4444; }
.stat-card.revision { border-left: 3px solid #f97316; }
.top-opds { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-top: 16px; }
.top-opds h3 { margin: 0 0 12px; font-size: 14px; font-weight: 600; color: #1e293b; }
.opd-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border-radius: 6px; margin-bottom: 4px; }
.opd-name { font-size: 13px; font-weight: 500; color: #1e293b; }
.opd-count { font-size: 12px; font-weight: 700; color: #4f46e5; padding: 2px 8px; background: #eff6ff; border-radius: 999px; }
</style>

<div class="opd-stats-widget">
    @if(count($stats) > 0)
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_opd'] ?? 0 }}</div>
                <div class="stat-label">Total OPD</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_datasets'] ?? 0 }}</div>
                <div class="stat-label">Dataset</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_values'] ?? 0 }}</div>
                <div class="stat-label">Total Data</div>
            </div>
            <div class="stat-card draft">
                <div class="stat-value">{{ $stats['draft'] ?? 0 }}</div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="stat-card submitted">
                <div class="stat-value">{{ $stats['submitted'] ?? 0 }}</div>
                <div class="stat-label">Diajukan</div>
            </div>
            <div class="stat-card verified">
                <div class="stat-value">{{ $stats['verified'] ?? 0 }}</div>
                <div class="stat-label">Diverifikasi</div>
            </div>
            <div class="stat-card published">
                <div class="stat-value">{{ $stats['published'] ?? 0 }}</div>
                <div class="stat-label">Dipublikasi</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-value">{{ $stats['rejected'] ?? 0 }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
            <div class="stat-card revision">
                <div class="stat-value">{{ $stats['revision'] ?? 0 }}</div>
                <div class="stat-label">Revisi</div>
            </div>
        </div>

        @if(isset($stats['top_opds']) && count($stats['top_opds']) > 0)
            <div class="top-opds">
                <h3>🏆 Top 5 OPD dengan Data Terbanyak</h3>
                @foreach($stats['top_opds'] as $opd)
                    <div class="opd-row">
                        <span class="opd-name">{{ $opd->kode }} — {{ $opd->nama }}</span>
                        <span class="opd-count">{{ $opd->total }} data</span>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="stat-card">
            <div class="stat-value">0</div>
            <div class="stat-label">Belum ada data</div>
        </div>
    @endif
</div>
