@props([
    'breadcrumbs' => [],
])

<div {{ $attributes->class(['filament-breadcrumbs flex-1']) }}>
    <ul
        @class([
            'hidden items-center gap-4 text-sm font-medium lg:flex',
        ])
        style="color: #94a3b8 !important;"
    >
        @foreach ($breadcrumbs as $url => $label)
            <li>
                <a
                    href="{{ is_int($url) ? '#' : $url }}"
                    @class([
                        'text-gray-500' => $loop->last && (! $loop->first),
                    ])
                    style="color: #94a3b8 !important;"
                >
                    {{ $label }}
                </a>
            </li>

            @if (! $loop->last)
                <li
                    @class([
                        'h-6 -skew-x-12 border-r border-gray-300',
                    ])
                    style="border-color: rgba(148,163,184,0.3) !important; color: rgba(148,163,184,0.4) !important;"
                >
                    <span style="color: rgba(148,163,184,0.4);">/</span>
                </li>
            @endif
        @endforeach
    </ul>
</div>
