@php
    $summary = $monitoring['summary'];
    $opdCards = collect($opdCards);
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $totalPublishedRecords = $opdCards->sum('published_values_count');
@endphp

<div class="leadership-content sangihe-summary">
    @include('filament.partials.leadership-page-header', [
        'title' => 'Ringkasan Sangihe',
        'description' => 'Gambaran umum masyarakat dan wilayah. Pilih OPD untuk membuka data sektoral yang telah diterbitkan.',
        'sourceNote' => 'Angka hanya berasal dari data terbit. Tahun dan sumber ditampilkan pada setiap dataset.',
    ])

    <section class="sangihe-summary__section" aria-labelledby="sangihe-overview-title">
        <div class="sangihe-summary__section-heading">
            <div>
                <h2 id="sangihe-overview-title">Gambaran umum</h2>
                <p>Ringkasan jumlah penduduk dan wilayah administratif.</p>
            </div>
        </div>
        <div class="sangihe-summary__metrics">
            <article class="sangihe-summary__metric sangihe-summary__metric--primary">
                <span>Penduduk</span>
                <strong>{{ $summary['population_total'] ? $number($summary['population_total']) : '—' }}</strong>
                <small>jiwa · data {{ $summary['population_year'] ?? 'belum tersedia' }}</small>
            </article>
            <article class="sangihe-summary__metric">
                <span>Keluarga</span>
                <strong>{{ $summary['household_total'] ? $number($summary['household_total']) : '—' }}</strong>
                <small>kepala keluarga · data {{ $summary['population_year'] ?? 'belum tersedia' }}</small>
            </article>
            <article class="sangihe-summary__metric">
                <span>Kecamatan</span>
                <strong>{{ $number($summary['district_count']) }}</strong>
                <small>wilayah terdaftar</small>
            </article>
            <article class="sangihe-summary__metric">
                <span>Kampung / desa</span>
                <strong>{{ $number($summary['village_count']) }}</strong>
                <small>wilayah terdaftar</small>
            </article>
        </div>
    </section>

    <section class="sangihe-summary__section" aria-labelledby="sangihe-opd-title">
        <div class="sangihe-summary__section-heading sangihe-summary__section-heading--opd">
            <div>
                <h2 id="sangihe-opd-title">Kondisi daerah menurut OPD / lembaga</h2>
                <p>Buka kartu instansi, lalu pilih dataset untuk melihat grafik dan sumber datanya.</p>
            </div>
            <span class="sangihe-summary__total-records">{{ $number($totalPublishedRecords) }} catatan terbit</span>
        </div>

        @if ($opdCards->isNotEmpty())
            <div class="sangihe-summary__opd-list">
                @foreach ($opdCards as $opd)
                    <details class="sangihe-summary__opd-card">
                        <summary class="sangihe-summary__opd-summary">
                            <span class="sangihe-summary__opd-icon" aria-hidden="true">{{ mb_substr($opd['name'], 0, 1) }}</span>
                            <span class="sangihe-summary__opd-info">
                                <strong>{{ $opd['name'] }}</strong>
                                <small>{{ $opd['active_dataset_count'] }} dataset aktif · {{ $number($opd['published_values_count']) }} catatan terbit</small>
                            </span>
                            <span class="sangihe-summary__opd-meta">
                                <small>{{ $opd['code'] }}</small>
                                <span class="sangihe-summary__chevron" aria-hidden="true"></span>
                            </span>
                        </summary>

                        <div class="sangihe-summary__dataset-list">
                            @forelse ($opd['infographics'] as $item)
                                @php
                                    $dataset = $item['dataset'];
                                    $years = $item['context']['years'] ?? [];
                                @endphp
                                <details class="sangihe-summary__dataset-card">
                                    <summary>
                                        <span>
                                            <strong>{{ $dataset->nama }}</strong>
                                            <small>{{ $dataset->kode }} · {{ $number($item['published_count']) }} catatan · {{ $years ? implode(', ', $years) : 'Tahun belum tersedia' }}</small>
                                        </span>
                                        <span class="sangihe-summary__dataset-chevron" aria-hidden="true"></span>
                                    </summary>
                                    <div class="sangihe-summary__dataset-content">
                                        @if (!empty($item['charts']))
                                            <x-infographic-chart :chart="$item['charts'][0]" variant="0" />
                                        @else
                                            <div class="leadership-empty">Belum ada angka resmi terbit untuk divisualisasikan.</div>
                                        @endif
                                        <p class="leadership-source"><strong>Sumber:</strong> {{ $item['context']['source'] }}@if($item['context']['source_table']), tabel {{ $item['context']['source_table'] }}@endif @if($item['context']['source_page']), halaman {{ $item['context']['source_page'] }}@endif.@if($item['context']['source_url']) <a href="{{ $item['context']['source_url'] }}" target="_blank" rel="noopener noreferrer" class="underline">Buka sumber</a>@endif</p>
                                        @foreach(($item['context']['notes'] ?? []) as $sourceNote)
                                            <p class="leadership-source">{{ $sourceNote }}</p>
                                        @endforeach
                                    </div>
                                </details>
                            @empty
                                <p class="sangihe-summary__no-infographic">
                                    @if ($opd['published_values_count'] > 0)
                                        Ada {{ $number($opd['published_values_count']) }} catatan data terbit. Visualisasi untuk dataset OPD ini belum disertakan pada ringkasan.
                                    @else
                                        Belum ada nilai dataset terbit untuk ditampilkan.
                                    @endif
                                </p>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        @else
            <div class="leadership-empty"><strong>Belum ada infografis sektoral yang diterbitkan.</strong>Data akan muncul setelah tersedia dan diterbitkan oleh OPD / lembaga.</div>
        @endif
    </section>
</div>
