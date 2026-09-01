<x-filament::widgets::widget>
    <div class="p-6 space-y-6">
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

        <!-- Submission Stats -->
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

        <!-- Completion Percentage -->
        <div class="bg-white rounded-lg shadow border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Completion Percentage</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $completionPercentage }}%</p>
                </div>
                <div class="text-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="{{ $completionPercentage >= 80 ? 'green' : ($completionPercentage >= 50 ? 'yellow' : 'red') }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Submission Chart -->
        <div class="bg-white rounded-lg shadow border p-4">
            <h3 class="text-lg font-medium mb-4">Monthly Submission Trend</h3>
            <div id="submission-chart" style="height: 200px;"></div>
        </div>

        <!-- Latest Submissions -->
        <div class="bg-white rounded-lg shadow border p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium">Latest Submissions</h3>
                <a href="{{ filament()->getUrl('resources/submission-batches') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            
            @if ($latestSubmissions->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($latestSubmissions as $submission)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-medium">{{ SubmissionBatch::moduleLabel($submission->module) }} Submission</p>
                                <p class="text-sm text-gray-500">{{ $submission->submission_number }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded text-xs font-medium 
                                    @if ($submission->status === SubmissionBatch::STATUS_APPROVED)
                                        bg-green-100 text-green-800
                                    @elseif ($submission->status === SubmissionBatch::STATUS_REJECTED)
                                        bg-red-100 text-red-800
                                    @elseif ($submission->status === SubmissionBatch::STATUS_DRAFT)
                                        bg-gray-100 text-gray-800
                                    @else
                                        bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ SubmissionBatch::statusLabel($submission->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No submissions yet.</p>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow border p-4">
            <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['url'] }}" 
                       class="block p-4 bg-{{ $action['color'] }}-50 hover:bg-{{ $action['color'] }}-100 rounded-lg border border-{{ $action['color'] }}-200 flex flex-col items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if ($action['label'] === 'Input Penduduk')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 01-7 7h14a7 7 0 00-7-7z" />
                            @elseif ($action['label'] === 'Input Keluarga')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2s1 2 2 2 2-.9 2-2-1-2-2-2zm0 2a.5.5 0 01-.5.5v1.5a.5.5 0 01-1 0V10a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1.5a.5.5 0 01-1 0v1.5a.5.5 0 01-.5.5h-1zm0-4a2 2 0 100-4 2 2 0 000 4z" />
                            @elseif ($action['label'] === 'Input Rumah')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 1.749-5.226 5.188a1.5 1.5 0 00-1.06 2.53l1.04 6.03a1.5 1.5 0 002.512 1.06l6.03-1.04a1.5 1.5 0 002.53-1.06l5.188-5.226L21 3V1a1 1 0 00-1-1H4a1 1 0 00-1 1v2z" />
                            @elseif ($action['label'] === 'Buat Submission')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a2 2 0 100-4m-2 4a2 2 0 110-4m-6 0a2 2 0 100-4m-2 4a2 2 0 110-4m-6 0a2 2 0 100-4m-2 4a2 2 0 110-4z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M6 6l12 12" />
                            @endif
                        </svg>
                        <h4 class="font-medium">{{ $action['label'] }}</h4>
                        <p class="text-sm text-gray-500 text-center">{{ $action['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament::widgets::widget>

<script>
    document.addEventListener('alpine:init', () => {
        const chartData = {{ json_encode($chart) }};
        
        if (chartData && chartData.data && chartData.data.datasets && chartData.data.datasets.length > 0) {
            const ctx = document.getElementById('submission-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: chartData.type,
                    data: chartData.data,
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
    });
</script>
</x-filament::widgets::widget>