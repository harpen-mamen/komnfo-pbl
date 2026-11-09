<style>
.sd-review-hero{padding:24px 28px;border-radius:18px;color:#fff;background:linear-gradient(120deg,#172554,#075985);margin-bottom:20px}.sd-review-hero h2{font-size:22px;font-weight:750;margin:0 0 6px}.sd-review-hero p{margin:0;color:#dbeafe}.sd-review-package{background:#fff;border:1px solid #e2e8f0;border-radius:15px;margin-bottom:14px;overflow:hidden}.sd-review-head{padding:17px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;gap:14px;align-items:center}.sd-review-detail{background:#fff;border:1px solid #e2e8f0;border-radius:12px;margin:12px 14px;padding:12px 14px}.sd-review-detail summary{cursor:pointer;color:#075985;font-weight:700;padding:3px}.sd-review-detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:9px;margin-top:13px}.sd-review-field{border:1px solid #eef2f7;border-radius:9px;padding:10px 12px;min-width:0}.sd-review-field-label{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;font-weight:700}.sd-review-field-value{font-size:14px;color:#172b4d;margin-top:4px;overflow-wrap:anywhere;white-space:pre-wrap}.sd-review-meta{font-size:12px;color:#64748b;margin-top:9px}.sd-review-foot{padding:15px 20px;display:flex;justify-content:flex-end;gap:9px}.sd-review-btn{border:0;border-radius:9px;padding:9px 13px;font-weight:700;color:#fff;cursor:pointer}.sd-review-empty{padding:48px 20px;text-align:center;color:#64748b;background:#f8fafc;border-radius:14px}@media(max-width:700px){.sd-review-head{align-items:flex-start;flex-direction:column}.sd-review-detail-grid{grid-template-columns:1fr}}
</style>

<div>
    <div class="sd-review-hero">
        <h2>Antrean verifikasi data sektoral</h2>
        <p>Buka setiap data untuk meninjau seluruh nilai dan metadata sebelum memverifikasi atau meminta revisi.</p>
    </div>

    @if (session('review_success') || session('review_error'))
        @php($reviewFailed = session()->has('review_error'))
        <div role="status" aria-live="polite" style="padding:14px 16px;margin-bottom:16px;border-radius:10px;background:{{ $reviewFailed ? '#fef2f2' : '#ecfdf5' }};color:{{ $reviewFailed ? '#b91c1c' : '#047857' }};font-weight:700">
            {{ session('review_error') ?? session('review_success') }}
        </div>
    @endif

    @forelse ($submissions as $submission)
        <section class="sd-review-package">
            <div class="sd-review-head">
                <div>
                    <strong style="font-size:16px;color:#172b4d">{{ $submission->opd?->nama ?? 'OPD' }}</strong>
                    <div style="font-size:12px;color:#64748b;margin-top:4px">Pengiriman #{{ $submission->id }} · {{ $submission->values->count() }} data · {{ $submission->submitted_at?->format('d M Y H:i') }} · oleh {{ $submission->createdBy?->name ?? '-' }}</div>
                    @if ($submission->notes)<div style="font-size:13px;margin-top:8px">Catatan OPD: {{ $submission->notes }}</div>@endif
                </div>
                <span style="border-radius:999px;background:#fff7ed;color:#c2410c;padding:5px 10px;font-size:11px;font-weight:700">Menunggu verifikasi</span>
            </div>

            @foreach ($submission->values as $value)
                @php($fieldMap = $value->dataset?->fields?->keyBy('nama') ?? collect())
                <details class="sd-review-detail" open>
                    <summary>{{ $value->dataset?->nama ?? 'Dataset' }} · {{ $value->tahun }}{{ $value->periode_label ? ' · ' . $value->periode_label : '' }} <span style="color:#64748b;font-weight:400">— Tinjau data lengkap</span></summary>
                    <div class="sd-review-meta">Kode dataset: {{ $value->dataset?->kode ?? '-' }} · Cakupan: {{ $value->wilayah_type === 'kabupaten' ? 'Kabupaten' : ucfirst($value->wilayah_type ?? 'wilayah') . ' #' . ($value->wilayah_id ?? '-') }} · Status: {{ $value->status }}</div>
                    <div class="sd-review-detail-grid">
                        @forelse (($value->values ?? []) as $key => $fieldValue)
                            @php($field = $fieldMap->get($key))
                            <div class="sd-review-field">
                                <div class="sd-review-field-label">{{ $field?->label ?? \Illuminate\Support\Str::headline($key) }}</div>
                                <div class="sd-review-field-value">@if (is_array($fieldValue) || is_object($fieldValue)){{ json_encode($fieldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}@elseif (is_bool($fieldValue)){{ $fieldValue ? 'Ya' : 'Tidak' }}@elseif ($fieldValue === null || $fieldValue === '')-@else{{ $fieldValue }}@endif</div>
                                @if ($field?->satuan)<div class="sd-review-meta">Satuan: {{ $field->satuan }}</div>@endif
                            </div>
                        @empty
                            <div class="sd-review-field"><div class="sd-review-field-value">Data tidak memiliki nilai field.</div></div>
                        @endforelse
                    </div>
                </details>
            @endforeach

            <div class="sd-review-foot" style="flex-wrap:wrap">
                <form method="POST" action="{{ route('dataset-submissions.revision', $submission->id) }}" style="display:flex;gap:8px;align-items:center;flex:1;min-width:280px">
                    @csrf
                    <input name="notes" required maxlength="2000" placeholder="Catatan revisi untuk OPD" style="flex:1;min-width:160px;border:1px solid #cbd5e1;border-radius:8px;padding:9px 10px">
                    <button type="submit" class="sd-review-btn" style="background:#b45309">Minta revisi</button>
                </form>
                <form method="POST" action="{{ route('dataset-submissions.verify', $submission->id) }}">
                    @csrf
                    <button type="submit" class="sd-review-btn" style="background:#087e83">Verifikasi dan terbitkan</button>
                </form>
            </div>
        </section>
    @empty
        <div class="sd-review-empty"><strong>Tidak ada paket menunggu verifikasi</strong><div style="margin-top:5px">Pengiriman baru dari OPD akan muncul di sini.</div></div>
    @endforelse
</div>
