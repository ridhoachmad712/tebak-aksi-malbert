<x-layouts.publik judul="Manual Book">
    <section class="mx-auto max-w-4xl px-4 py-12">
        <h1 class="judul-halaman">Manual Book TEBAK AKSI</h1>
        <p class="teks-bantu mt-1">Panduan pelaksanaan program dan penggunaan aplikasi.</p>
        <div class="mt-6 space-y-3">
            @forelse ($daftar as $m)
                <div class="kartu flex flex-wrap items-center justify-between gap-3 p-5">
                    <div class="flex items-center gap-4">
                        <x-heroicon-o-document-text class="h-8 w-8 shrink-0 text-slate-500" />
                        <div>
                            <h2 class="font-extrabold">{{ $m->judul }}
                                @if ($m->untuk === 'guru') <span class="text-sm font-semibold text-slate-600">(khusus guru)</span> @endif
                            </h2>
                            @if ($m->deskripsi)
                                <p class="text-sm text-slate-600">{{ $m->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($m->urlFile())
                        <a href="{{ $m->urlFile() }}" target="_blank" class="tombol-utama">Buka PDF</a>
                    @endif
                </div>
            @empty
                <div class="kartu p-5 text-slate-600">Manual book belum diunggah. Admin dapat menambahkannya melalui panel admin, menu Manual Book.</div>
            @endforelse
        </div>
    </section>
</x-layouts.publik>
