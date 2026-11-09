<style>
.sd-send{--ink:#172b4d;--muted:#64748b;--line:#e2e8f0}.sd-send *{box-sizing:border-box}.sd-send-hero{padding:26px 30px;border-radius:18px;color:#fff;background:linear-gradient(120deg,#102f4d,#176b78);margin-bottom:22px}.sd-send-hero h2{font-size:23px;font-weight:750;margin:0 0 6px}.sd-send-hero p{margin:0;color:#dbeafe}.sd-send-toolbar,.sd-send-card{background:#fff;border:1px solid var(--line);border-radius:14px}.sd-send-toolbar{padding:16px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px}.sd-send-card{padding:16px;margin-bottom:10px;display:grid;grid-template-columns:24px minmax(0,1fr) auto;gap:14px;align-items:start}.sd-send-name{color:var(--ink);font-weight:700}.sd-send-meta{font-size:12px;color:var(--muted);margin-top:5px}.sd-send-status{font-size:11px;border-radius:999px;padding:4px 9px;background:#fff7ed;color:#c2410c;font-weight:700}.sd-send-empty{padding:44px 20px;text-align:center;background:#f8fafc;border-radius:14px;color:var(--muted)}.sd-send-note{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px 12px;margin-top:14px}.sd-send-button{border:0;border-radius:9px;background:#087e83;color:#fff;padding:10px 16px;font-weight:700;cursor:pointer}.sd-send-button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:640px){.sd-send-card{grid-template-columns:22px 1fr}.sd-send-status{grid-column:2}.sd-send-toolbar{align-items:flex-start;flex-direction:column}}
</style>

<form class="sd-send" method="POST" action="{{ route('dataset-submissions.store') }}">
    @csrf
    <div class="sd-send-hero">
        <h2>Kirim data sektoral ke Kominfo</h2>
        <p>{{ $opd }} · Pilih data draft dan revisi yang siap dikirim. Semua pilihan akan masuk sebagai satu paket verifikasi.</p>
    </div>

    @if (session('submission_success') || session('submission_error'))
        @php($submissionFailed = session()->has('submission_error'))
        <div role="status" aria-live="polite" style="padding:16px 18px;margin-bottom:16px;border:1px solid {{ $submissionFailed ? '#fecaca' : '#a7f3d0' }};border-radius:10px;background:{{ $submissionFailed ? '#fef2f2' : '#ecfdf5' }};color:{{ $submissionFailed ? '#b91c1c' : '#047857' }};font-weight:700">
            {{ session('submission_error') ?? session('submission_success') }}
        </div>
    @endif

    @if ($values->isEmpty())
        <div class="sd-send-empty">
            <div style="font-size:30px;margin-bottom:8px">✓</div>
            <strong>Semua data sudah dikirim</strong>
            <div style="margin-top:5px">Buat data dari menu Input Data OPD. Data akan muncul di sini sebelum dikirim.</div>
        </div>
    @else
        <div class="sd-send-toolbar">
            <div><strong>{{ number_format($total_ready) }}</strong> data siap dikirim <span style="color:#64748b">| ditampilkan: {{ $values->count() }} (maksimal 100)</span></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="button" class="sd-send-button" style="background:#475569" wire:click="selectAll">Pilih semua data</button>
                <button type="button" class="sd-send-button" style="background:#475569" wire:click="clearSelection">Batal pilih</button>
            </div>
        </div>

        @foreach ($values as $value)
            <label class="sd-send-card" for="value-{{ $value->id }}">
                <input class="sd-send-check" id="value-{{ $value->id }}" type="checkbox" name="selectedValues[]" value="{{ $value->id }}" wire:model="selectedValues" @checked(in_array((string) $value->id, $selectedValues, true)) style="margin-top:4px">
                <span>
                    <span class="sd-send-name">{{ $value->dataset?->nama ?? 'Dataset' }}</span>
                    <span class="sd-send-meta" style="display:block">{{ $value->dataset?->kode }} · {{ $value->tahun }} {{ $value->periode_label ? '· ' . $value->periode_label : '' }} · {{ $value->wilayah_type === 'kabupaten' ? 'Kabupaten' : 'Wilayah ' . $value->wilayah_id }}</span>
                    <span class="sd-send-meta" style="display:block;margin-top:8px">{{ collect($value->values ?? [])->take(3)->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : '…'))->join(' · ') }}</span>
                </span>
                <span class="sd-send-status">{{ $value->status === 'revision' ? 'Perlu revisi' : 'Draft' }}</span>
            </label>
        @endforeach

        <textarea class="sd-send-note" name="notes" rows="3" placeholder="Catatan untuk verifikator Kominfo (opsional)"></textarea>
        <div style="display:flex;justify-content:flex-end;margin-top:12px">
            <button type="submit" class="sd-send-button">Kirim data terpilih ke Kominfo</button>
        </div>
    @endif
</form>
