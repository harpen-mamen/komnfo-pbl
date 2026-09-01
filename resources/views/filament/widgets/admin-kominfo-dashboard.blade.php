<x-filament::widgets::widget>
    <div class="p-6 space-y-6">
        <!-- Stats Overview -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="card-kominfo group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $stat->getLabel() }}</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stat->getValue() }}</p>
                        </div>
                        <div class="text-3xl text-green-600 dark:text-green-400 opacity-20 group-hover:opacity-30 transition-opacity">
                            {{ $stat->getIcon() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submission Stats -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($submissionStats as $stat)
                <div class="card-kominfo group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $stat->getLabel() }}</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stat->getValue() }}</p>
                        </div>
                        <div class="text-3xl text-blue-600 dark:text-blue-400 opacity-20 group-hover:opacity-30 transition-opacity">
                            {{ $stat->getIcon() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Activities -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-semibold text-white">📋 Aktivitas Terbaru</h3>
            </div>
            
            @if ($recentActivities->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($recentActivities as $activity)
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
                            <div class="flex-1">
                                <p class="font-medium text-slate-900 dark:text-white">{{ $activity['title'] ?? 'Activity' }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $activity['description'] ?? '' }}</p>
                            </div>
                            <div class="text-right text-sm text-slate-500 dark:text-slate-400">
                                {{ $activity['time'] ?? 'Just now' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-slate-500 py-4">Tidak ada aktivitas terbaru.</p>
            @endif
        </div>

        <!-- Pending Queue -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-semibold text-white">⏳ Antrian Verifikasi</h3>
                <a href="{{ filament()->getUrl('resources/submission-batches') }}?status=menunggu_verifikasi" 
                   class="text-sm text-green-200 hover:text-white transition-colors font-medium">Lihat Semua →</a>
            </div>
            
            @if ($pendingQueue->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($pendingQueue as $submission)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-medium">{{ SubmissionBatch::moduleLabel($submission->module) }} Submission</p>
                                <p class="text-sm text-gray-500">{{ $submission->submission_number }}</p>
                                <p class="text-xs text-gray-400">{{ $submission->kecamatan?->nama ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Review
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No pending submissions.</p>
            @endif
        </div>

        <!-- Latest Reviews -->
        <div class="bg-white rounded-lg shadow border p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">Latest Reviews</h3>
                <a href="{{ filament()->getUrl('resources/submission-batches') }}?status=disetujui,ditolak" 
                   class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            
            @if ($latestReviews->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($latestReviews as $review)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-medium">{{ SubmissionBatch::moduleLabel($review->module) }} Submission</p>
                                <p class="text-sm text-gray-500">{{ $review->submission_number }}</p>
                                <p class="text-xs text-gray-400">{{ $review->kecamatan?->nama ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded text-xs font-medium 
                                    @if ($review->status === SubmissionBatch::STATUS_APPROVED)
                                        bg-green-100 text-green-800
                                    @else
                                        bg-red-100 text-red-800
                                    @endif
                                ">
                                    {{ $review->status === SubmissionBatch::STATUS_APPROVED ? 'Approved' : 'Rejected' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No recent reviews.</p>
            @endif
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
                <h3 class="text-lg font-medium mb-4">Population by Kecamatan</h3>
                <div id="population-chart" style="height: 200px;"></div>
            </div>

            <!-- House Count by Kecamatan -->
            <div class="bg-white rounded-lg shadow border p-4">
                <h3 class="text-lg font-medium mb-4">House Count by Kecamatan</h3>
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
