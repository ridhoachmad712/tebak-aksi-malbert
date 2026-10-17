@use('App\Models\KasusPembinaan')
@use('App\Services\Rekap')

@php
    $dimulai = $kasus->mingguDimulai();
    $terisi = $kasus->pemantauan->keyBy('minggu');
@endphp

<div class="mx-auto max-w-3xl space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('guru.pembinaan') }}" wire:navigate class="text-sm font-bold text-slate-500 hover:text-slate-700">← Kasus Pembinaan</a>
            <h1 class="judul-halaman">{{ $kasus->murid->nama }}</h1>
            <p class="text-sm font-semibold text-slate-500">Kelas {{ $kasus->kelas->nama }} · dicatat {{ Rekap::tanggalPanjang($kasus->tanggal) }}</p>
        </div>
        <span @class(['lencana px-3 py-1.5 text-sm', 'bg-amber-100 text-amber-800' => ! $kasus->status_akhir, 'bg-toska-100 text-toska-800' => $kasus->status_akhir])>{{ $kasus->labelStatus() }}</span>
    </div>

    {{-- Ringkasan kasus --}}
    <section class="kartu grid gap-4 p-5 sm:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase text-slate-400">Kondisi awal</p>
            <p class="font-semibold">{{ $kasus->kondisi_awal }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-slate-400">Jenis pelanggaran</p>
            <p class="font-semibold">{{ $kasus->namaPelanggaran() }}
                <span class="lencana bg-slate-100 text-slate-600">{{ KasusPembinaan::TINGKAT[$kasus->tingkat] }}</span>
                @if ($kasus->jenisPelanggaran?->aspekKarakter)
                    <span class="lencana bg-violet-100 text-violet-700">{{ $kasus->jenisPelanggaran->aspekKarakter->nama }}</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-slate-400">Tindakan pembinaan</p>
            <p class="font-semibold">{{ $kasus->namaTindakan() }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-slate-400">Komitmen murid</p>
            <p class="font-semibold">{{ $kasus->komitmen ?: '-' }}</p>
        </div>
        <div class="flex gap-2 sm:col-span-2">
            <a href="{{ route('guru.pembinaan.ubah', $kasus) }}" wire:navigate class="tombol-kedua"><x-heroicon-o-pencil-square class="h-4 w-4" /> Ubah</a>
            <button wire:click="hapus" wire:confirm="Hapus kasus ini beserta seluruh pemantauannya?" class="tombol-kedua text-rose-600"><x-heroicon-o-trash class="h-4 w-4" /> Hapus</button>
        </div>
    </section>

    {{-- Pemantauan 4 minggu --}}
    <section class="kartu p-5">
        <h2 class="text-lg font-extrabold">Pemantauan mingguan</h2>
        <p class="text-sm text-slate-500">Minggu 1 dimulai sehari setelah kasus dicatat. Pilih minggu untuk mengisi atau mengubah.</p>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach (range(1, 4) as $m)
                @php
                    $p = $terisi->get($m);
                    $sudahMulai = $m <= $dimulai;
                @endphp
                <button type="button" wire:click="pilihMinggu({{ $m }})" @disabled(! $sudahMulai)
                    @class(['rounded-2xl border-2 p-3 text-left transition disabled:cursor-not-allowed disabled:opacity-50',
                        'border-brand-400 bg-brand-50' => $minggu === $m,
                        'border-amber-300 bg-amber-50' => $minggu !== $m && $sudahMulai && ! $p,
                        'border-slate-200 bg-white hover:border-slate-300' => $minggu !== $m && ($p || ! $sudahMulai)])>
                    <span class="block text-xs font-bold uppercase text-slate-500">Minggu {{ $m }}</span>
                    <span class="block text-[11px] text-slate-400">mulai {{ $kasus->tanggal->copy()->addDays(7 * ($m - 1) + 1)->format('d/m') }}</span>
                    <span class="mt-1 block font-extrabold">
                        @if ($p)
                            {{ KasusPembinaan::PERKEMBANGAN[$p->tingkat]['ikon'] }} {{ KasusPembinaan::PERKEMBANGAN[$p->tingkat]['label'] }}
                        @elseif ($sudahMulai)
                            <span class="text-amber-700">🔔 Belum diisi</span>
                        @else
                            <span class="text-slate-400">Belum tiba</span>
                        @endif
                    </span>
                </button>
            @endforeach
        </div>

        @if ($minggu)
            <form wire:submit="simpanPemantauan" class="mt-5 rounded-2xl border border-orange-100 bg-orange-50/40 p-4">
                <p class="font-extrabold">Bagaimana perkembangan {{ str($kasus->murid->nama)->before(' ') }} pada minggu ke-{{ $minggu }}?</p>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (KasusPembinaan::PERKEMBANGAN as $nilai => $info)
                        <label class="flex cursor-pointer flex-col items-center gap-1 rounded-xl border-2 border-slate-200 bg-white p-3 text-center has-checked:border-brand-400 has-checked:bg-brand-50">
                            <input type="radio" wire:model="tingkat" value="{{ $nilai }}" class="sr-only">
                            <span class="text-2xl">{{ $info['ikon'] }}</span>
                            <span class="text-sm font-bold">{{ $info['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tingkat') <p class="galat">{{ $message }}</p> @enderror
                <textarea wire:model="catatan" rows="2" placeholder="Catatan (opsional)" class="isian mt-3"></textarea>
                <div class="mt-3 flex justify-end">
                    <button class="tombol-utama">Simpan Pemantauan</button>
                </div>
            </form>
        @endif

        @if ($kasus->pemantauan->whereNotNull('catatan')->isNotEmpty())
            <ul class="mt-4 space-y-1 text-sm text-slate-600">
                @foreach ($kasus->pemantauan->whereNotNull('catatan') as $p)
                    <li>• <b>Minggu {{ $p->minggu }}:</b> {{ $p->catatan }} <span class="text-slate-400">— {{ $p->user?->name }}</span></li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Status akhir --}}
    <section class="kartu p-5">
        <h2 class="text-lg font-extrabold">Status akhir</h2>
        @if ($kasus->status_akhir)
            <p class="mt-1 text-slate-600">Status akhir: <b>{{ $kasus->labelStatus() }}</b></p>
            <button wire:click="bukaKembali" class="tombol-kedua mt-3">Ubah status / lanjutkan pembinaan</button>
        @else
            <p class="text-sm text-slate-500">
                {{ $kasus->siapDitutup() ? 'Pemantauan 4 minggu sudah lengkap. Tetapkan status akhir murid ini.' : 'Tetapkan setelah masa pemantauan selesai. Selama belum ditetapkan, status kasus adalah Dalam Pembinaan.' }}
            </p>
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach (KasusPembinaan::PERKEMBANGAN as $nilai => $info)
                    <button wire:click="tetapkanStatus('{{ $nilai }}')" wire:confirm="Tetapkan status akhir: {{ $info['label'] }}?"
                        class="flex flex-col items-center gap-1 rounded-xl border-2 border-slate-200 bg-white p-3 hover:border-brand-400">
                        <span class="text-2xl">{{ $info['ikon'] }}</span>
                        <span class="text-sm font-bold">{{ $info['label'] }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>
</div>
