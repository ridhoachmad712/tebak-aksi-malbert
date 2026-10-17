<x-layouts.auth judul="Pilih halaman">
    <h1 class="text-2xl font-extrabold">Halo, {{ str(auth()->user()->name)->before(',') }}</h1>
    <p class="teks-bantu mt-1">Mau membuka halaman apa? Anda bisa pindah halaman kapan saja lewat tombol di bagian atas.</p>

    <ul class="mt-6 space-y-3">
        @foreach (\App\Support\HalamanAdmin::daftar() as $h)
            <li>
                <a href="{{ $h['url'] }}" class="flex items-center gap-4 rounded-xl border-2 border-garis bg-white p-4 transition-colors hover:border-slate-800">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-700">
                        <x-dynamic-component :component="$h['ikon']" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-extrabold">{{ $h['label'] }}</span>
                        <span class="block text-sm text-slate-600">{{ $h['keterangan'] }}</span>
                    </span>
                    <x-heroicon-m-chevron-right class="h-5 w-5 shrink-0 text-slate-400" />
                </a>
            </li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('keluar') }}" class="mt-6 border-t border-garis pt-4">
        @csrf
        <button class="text-sm font-semibold text-rose-700 hover:underline">Keluar</button>
    </form>
</x-layouts.auth>
