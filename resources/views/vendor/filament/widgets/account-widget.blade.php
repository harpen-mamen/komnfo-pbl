<x-filament::widget class="filament-account-widget">
    <x-filament::card>
        @php
            $user = \Filament\Facades\Filament::auth()->user();
        @endphp

        <div class="flex h-12 items-center space-x-4 rtl:space-x-reverse">
            <x-filament::user-avatar :user="$user" />

            <div>
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">
                    {{ __('filament::widgets/account-widget.welcome', ['user' => \Filament\Facades\Filament::getUserName($user)]) }}
                </h2>

                <div class="flex items-center gap-3 mt-1">
                    <a
                        href="{{ url('/admin/pengaturan-profil') }}"
                        class="text-sm text-gray-600 hover:text-primary-500 focus:underline"
                    >
                        Edit Profil
                    </a>
                    <span class="text-gray-300">|</span>
                    <form
                        action="{{ route('filament.auth.logout') }}"
                        method="post"
                        class="inline"
                    >
                        @csrf

                        <button
                            type="submit"
                            @class([
                                'text-sm text-gray-600 outline-none hover:text-primary-500 focus:underline',
                                'dark:text-gray-300 dark:hover:text-primary-500' => config('filament.dark_mode'),
                            ])
                        >
                            {{ __('filament::widgets/account-widget.buttons.logout.label') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
