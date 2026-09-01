<x-filament::widgets::widget>
    <div class="sipandu-super-dashboard p-6 space-y-6">
        <div class="rounded-2xl p-6 text-white" style="background:linear-gradient(120deg,#0b1f35,#145b78 65%,#008c95);">
            <div class="text-xs font-bold uppercase tracking-widest text-cyan-200">SIPANDU · Pusat Kendali Data</div>
            <h2 class="mt-2 text-2xl font-extrabold">Ringkasan administrasi Kabupaten Kepulauan Sangihe</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-200">Pantau cakupan wilayah, penduduk, rumah, dan kualitas data dari satu ruang kerja.</p>
        </div>
        <!-- Stats Overview -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="bg-white rounded-lg shadow border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $stat->getLabel() }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stat->getValue() }}</p>
                        </div>
                        <div class="text-2xl">
                            {{ $stat->getIcon() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ringkasan tata kelola -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($submissionStats as $stat)
                <div class="bg-white rounded-lg shadow border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $stat->getLabel() }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stat->getValue() }}</p>
                        </div>
                        <div class="text-2xl">
                            {{ $stat->getIcon() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Charts -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Submission Trend -->
            <div class="bg-white rounded-lg shadow border p-4" style="display:none">
                <h3 class="text-lg font-medium mb-4">Tren Kasus Stunting</h3>
                <div id="submission-trend-chart" style="height: 200px;"></div>
            </div>

            <!-- Approval Trend -->
            <div class="bg-white rounded-lg shadow border p-4" style="display:none">
                <h3 class="text-lg font-medium mb-4">Tren Putus Sekolah</h3>
                <div id="approval-trend-chart" style="height: 200px;"></div>
            </div>

            <!-- Population by Kecamatan -->
            <div class="bg-white rounded-lg shadow border p-4">
                <h3 class="text-lg font-medium mb-4">Sebaran Penduduk per Kecamatan</h3>
                <div id="population-chart" style="height: 200px;"></div>
            </div>

            <!-- House Count by Kecamatan -->
            <div class="bg-white rounded-lg shadow border p-4">
                <h3 class="text-lg font-medium mb-4">Sebaran Rumah per Kecamatan</h3>
                <div id="house-count-chart" style="height: 200px;"></div>
            </div>
        </div>
    </div>
</x-filament::widgets::widget>

<script>
    document.addEventListener('alpine:init', () => {
        const charts = {{ json_encode($charts) }};
        
        // Submission Trend Chart
        if (charts.submissionTrend && charts.submissionTrend.data) {
            const ctx1 = document.getElementById('submission-trend-chart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: charts.submissionTrend.type,
                    data: charts.submissionTrend.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Approval Trend Chart
        if (charts.approvalTrend && charts.approvalTrend.data) {
            const ctx2 = document.getElementById('approval-trend-chart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: charts.approvalTrend.type,
                    data: charts.approvalTrend.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Population by Kecamatan Chart
        if (charts.populationByKecamatan && charts.populationByKecamatan.data) {
            const ctx3 = document.getElementById('population-chart');
            if (ctx3) {
                new Chart(ctx3, {
                    type: charts.populationByKecamatan.type,
                    data: charts.populationByKecamatan.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // House Count by Kecamatan Chart
        if (charts.houseCountByKecamatan && charts.houseCountByKecamatan.data) {
            const ctx4 = document.getElementById('house-count-chart');
            if (ctx4) {
                new Chart(ctx4, {
                    type: charts.houseCountByKecamatan.type,
                    data: charts.houseCountByKecamatan.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>
</x-filament::widgets::widget>
