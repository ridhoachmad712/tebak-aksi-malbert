<x-layouts.publik judul="Layanan Pengaduan">
    <section class="mx-auto max-w-2xl px-4 py-12">
        @if (session('terkirim'))
            <div class="kartu p-8 text-center">
                <div class="text-5xl">💌</div>
                <h1 class="mt-3 text-2xl font-extrabold">Terima kasih!</h1>
                <p class="mt-2 text-slate-600">Pesan Anda sudah kami terima dan akan ditindaklanjuti oleh admin sekolah.</p>
                <a href="{{ route('beranda') }}" class="tombol-utama mt-6">Kembali ke beranda</a>
            </div>
        @else
            <h1 class="judul-halaman">Layanan Pengaduan</h1>
            <p class="mt-1 text-slate-600">Sampaikan saran, pertanyaan, atau keluhan terkait program TEBAK AKSI. Nama dan kontak boleh dikosongkan.</p>

            <form method="POST" action="{{ route('pengaduan') }}" class="kartu mt-6 space-y-4 p-6">
                @csrf
                <div class="hidden" aria-hidden="true">
                    <label>Situs <input name="situs" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nama" class="label">Nama <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="nama" name="nama" value="{{ old('nama') }}" class="isian">
                        @error('nama') <p class="galat">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="kontak" class="label">No. HP / Email <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="kontak" name="kontak" value="{{ old('kontak') }}" class="isian">
                        @error('kontak') <p class="galat">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <span class="label">Kategori</span>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach (\App\Models\Pengaduan::KATEGORI as $nilai => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 p-2.5 text-sm font-bold has-checked:border-brand-400 has-checked:bg-brand-50">
                                <input type="radio" name="kategori" value="{{ $nilai }}" @checked(old('kategori', 'saran') === $nilai) class="accent-brand-500"> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('kategori') <p class="galat">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="isi" class="label">Isi pesan</label>
                    <textarea id="isi" name="isi" rows="5" required class="isian">{{ old('isi') }}</textarea>
                    @error('isi') <p class="galat">{{ $message }}</p> @enderror
                </div>
                <button class="tombol-utama w-full py-3 text-base">Kirim</button>
            </form>
        @endif
    </section>
</x-layouts.publik>
