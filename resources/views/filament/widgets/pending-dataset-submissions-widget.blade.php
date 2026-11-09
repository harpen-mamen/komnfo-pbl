<div style="border:1px solid #bae6fd;background:linear-gradient(120deg,#f0f9ff,#ecfeff);border-radius:14px;padding:18px 20px;margin-bottom:14px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div>
            <div style="font-size:16px;font-weight:750;color:#0c4a6e">Verifikasi data sektoral OPD <span style="display:inline-block;background:#0369a1;color:white;border-radius:999px;padding:2px 9px;font-size:12px;margin-left:5px">{{ $pendingCount }}</span></div>
            <div style="font-size:13px;color:#475569;margin-top:4px">Kiriman data sektoral memakai antrean paket tersendiri dan tidak tercampur dengan batch penduduk.</div>
            @if ($submissions->isNotEmpty())
                <div style="font-size:12px;color:#334155;margin-top:8px">
                    @foreach ($submissions->take(3) as $submission)
                        <span style="margin-right:12px">{{ $submission->opd?->nama ?? 'OPD' }} · {{ $submission->values_count }} data</span>
                    @endforeach
                    @if ($pendingCount > 3)<span>dan {{ $pendingCount - 3 }} paket lainnya</span>@endif
                </div>
            @endif
        </div>
        <a href="{{ $url }}" style="display:inline-flex;align-items:center;background:#0369a1;color:#fff;text-decoration:none;font-weight:700;border-radius:9px;padding:10px 14px">Buka antrean verifikasi</a>
    </div>
</div>
