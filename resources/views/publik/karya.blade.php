@use('App\Services\Rekap')

<x-layouts.publik judul="Karya Literasi">
    <section x-data="{ buka: null }" class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Karya Literasi</h1>
        <p class="teks-bantu mt-1">Karya murid dari kegiatan Teras Baca, langsung tampil setelah dicatat wali kelas.</p>

        {{-- Saringan bulan dan kelas; berubah langsung saat dipilih --}}
        <form method="GET" action="{{ route('karya.publik') }}" class="mt-6 flex flex-wrap items-end gap-3">
            <div>
                <label for="bulan" class="label">Bulan</label>
                <select id="bulan" name="bulan" onchange="this.form.submit()" class="isian w-auto pr-9 font-bold">
                    <option value="">Semua bulan</option>
                    @foreach ($daftarBulan as $nilai => $teks)
                        <option value="{{ $nilai }}" @selected($bulan === $nilai)>{{ $teks }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kelas" class="label">Kelas</label>
                <select id="kelas" name="kelas" onchange="this.form.submit()" class="isian w-auto pr-9 font-bold">
                    <option value="">Semua kelas</option>
                    @foreach ($daftarKelas as $k)
                        <option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>Kelas {{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <noscript><button class="tombol-utama">Tampilkan</button></noscript>
            @if ($bulan || $kelas)
                <a href="{{ route('karya.publik') }}" class="tautan pb-2.5 text-sm">Hapus saringan</a>
            @endif
        </form>

        <p class="mt-4 text-sm text-slate-600">{{ $karya->total() }} karya{{ $kelas ? ' · Kelas '.$kelas->nama : '' }}{{ $bulan ? ' · '.$daftarBulan[$bulan] : '' }}</p>

        <div class="mt-3 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @forelse ($karya as $k)
                @php $foto = $k->foto->first(); @endphp
                <article class="flex flex-col overflow-hidden rounded-xl border border-garis bg-white">
                    @if ($foto)
                        <button type="button" x-on:click="buka = @js(['url' => $foto->url(), 'ket' => $k->judul.' · '.$k->murid->pluck('nama')->join(', ')])" class="block" aria-label="Perbesar foto {{ $k->judul }}">
                            <img src="{{ $foto->url() }}" alt="Foto karya {{ $k->judul }}" loading="lazy" class="aspect-square w-full object-cover">
                        </button>
                    @else
                        <div class="grid aspect-square w-full place-items-center bg-slate-100 text-center text-xs font-semibold text-slate-500">
                            <span><x-heroicon-o-photo class="mx-auto h-8 w-8" />Foto belum diunggah</span>
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-3">
                        <h2 class="font-extrabold leading-snug">{{ $k->judul }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $k->murid->pluck('nama')->join(', ') }}</p>
                        <p class="mt-auto pt-2 text-xs text-slate-600">{{ $k->namaJenis() }} · Kelas {{ $k->kelas->nama }} · {{ Rekap::tanggalPanjang($k->tanggal) }}</p>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-xl border border-garis bg-white p-6 text-slate-700">
                    @if ($bulan || $kelas)
                        Belum ada karya untuk saringan ini. Pilih bulan atau kelas lain, atau <a href="{{ route('karya.publik') }}" class="tautan">lihat semua karya</a>.
                    @else
                        Belum ada karya literasi murid. Karya akan tampil di sini setelah wali kelas mencatatnya di aplikasi.
                    @endif
                </p>
            @endforelse
        </div>
        <div class="mt-6">{{ $karya->links() }}</div>

        <div x-cloak x-show="buka" x-on:click="buka = null" x-on:keydown.escape.window="buka = null" class="fixed inset-0 z-50 grid place-items-center bg-black/80 p-4">
            <figure class="max-w-4xl" x-on:click.stop>
                <img :src="buka?.url" alt="" class="max-h-[80vh] rounded-lg">
                <figcaption class="mt-2 text-center font-bold text-white" x-text="buka?.ket"></figcaption>
            </figure>
        </div>
    </section>
</x-layouts.publik>
