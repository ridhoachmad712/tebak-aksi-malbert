<x-layouts.publik judul="Manual Book">
    <section class="mx-auto max-w-4xl px-4 py-12">
        <h1 class="judul-halaman">Manual Book TEBAK AKSI</h1>
        <p class="mt-1 text-slate-600">Panduan pelaksanaan program dan penggunaan aplikasi.</p>
        <div class="mt-6 space-y-3">
            @forelse ($daftar as $m)
                <div class="kartu flex flex-wrap items-center justify-between gap-3 p-5">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">📘</span>
                        <div>
                            <h2 class="font-extrabold">{{ $m->judul }}
                                @if ($m->untuk === 'guru') <span class="lencana bg-toska-100 text-toska-800">Khusus guru</span> @endif
                            </h2>
                            @if ($m->deskripsi)
                                <p class="text-sm text-slate-600">{{ $m->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($m->urlFile())
                        <a href="{{ $m->urlFile() }}" target="_blank" class="tombol-utama"><x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Buka PDF</a>
                    @endif
                </div>
            @empty
                <div class="kartu p-8 text-center text-slate-500">Manual book belum tersedia.</div>
            @endforelse
        </div>
    </section>
</x-layouts.publik>
