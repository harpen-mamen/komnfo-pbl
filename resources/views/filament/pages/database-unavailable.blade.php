<main class="mx-auto max-w-2xl px-6 py-12">
    <section class="rounded-xl border border-amber-200 bg-white p-6 shadow-sm" role="alert">
        <p class="text-sm font-semibold text-emerald-700">Login berhasil</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Database dashboard belum lengkap</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600">Akun Anda sudah terautentikasi. Dashboard belum dapat menampilkan data karena sejumlah tabel aplikasi belum tersedia. Pemulihan database diperlukan sebelum fitur data dapat digunakan.</p>
        @if (\App\Support\PanelAccess::isKominfoKabupaten() || \App\Support\PanelAccess::isSuperAdmin())
            <p class="mt-4 text-sm font-semibold text-slate-700">Tabel yang belum tersedia:</p>
            <ul class="mt-2 list-inside list-disc text-sm text-slate-600">
                @foreach ($missingTables as $table)
                    <li>{{ $table }}</li>
                @endforeach
            </ul>
        @endif
        <p class="mt-4 text-sm text-slate-600">Data dan akun yang masih tersedia tetap dipertahankan.</p>
        <div class="mt-6 flex flex-wrap items-center gap-4">
            <a class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white" href="{{ url(config('filament.path', 'admin')) }}">Periksa kembali</a>
            <form method="post" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Keluar</button>
            </form>
        </div>
    </section>
</main>
