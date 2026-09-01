@if (filled($brand = config('filament.brand')))
    <div class="filament-brand text-xl font-bold leading-5 tracking-tight" style="color: #ffffff !important;">
        {{ $brand }}
    </div>
@endif
