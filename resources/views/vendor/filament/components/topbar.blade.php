@props([
    'breadcrumbs' => [],
])

<header
    {{
        $attributes->class([
            'filament-main-topbar sticky top-0 z-10 flex h-16 w-full shrink-0 items-center border-b',
        ])
    }}
    style="background: linear-gradient(90deg, #ffffff 0%, #f3f9ff 100%) !important; backdrop-filter: blur(16px); border-color: #c9dff3 !important;"
>
    <div class="flex w-full items-center px-2 sm:px-4 md:px-6 lg:px-8">
        <button
            x-cloak
            x-data="{}"
            x-bind:aria-label="
                $store.sidebar.isOpen
                    ? '{{ __('filament::layout.buttons.sidebar.collapse.label') }}'
                    : '{{ __('filament::layout.buttons.sidebar.expand.label') }}'
            "
            x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
            @class([
                'filament-sidebar-open-button flex h-10 w-10 shrink-0 items-center justify-center rounded-full outline-none',
            ])
            style="color: #0868b9 !important;"
        >
            <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
            </svg>
        </button>

        <div class="flex flex-1 items-center justify-between">
            <x-filament::layouts.app.topbar.breadcrumbs
                :breadcrumbs="$breadcrumbs"
            />

            @livewire('filament.core.global-search')

            @livewire('filament.core.notifications')

            <x-filament::layouts.app.topbar.user-menu />
        </div>
    </div>
</header>
