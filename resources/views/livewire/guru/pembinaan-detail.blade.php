@use('App\Models\KasusPembinaan')
@use('App\Services\Rekap')

@php
    $dimulai = $kasus->mingguDimulai();
    $terisi = $kasus->pemantauan->keyBy('minggu');
@endphp

<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route('guru.pembinaan') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-slate-600 hover:text-slate-900"><x-heroicon-m-chevron-left class="h-4 w-4" /> Kasus Pembinaan</a>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="judul-halaman">{{ $kasus->murid->nama }}</h1>
                <p class="teks-bantu">Kelas {{ $kasus->kelas->nama }} · dicatat {{ Rekap::tanggalPanjang($kasus->tanggal) }}{{ $kasus->pencatat ? ' oleh '.$kasus->pencatat->name : '' }}</p>
            </div>
            @if ($kasus->status_akhir)
                <x-perkembangan :tingkat="$kasus->status_akhir" class="px-3 py-1 text-sm" />
            @else
                <span class="lencana bg-slate-100 px-3 py-1 text-sm text-slate-700">Dalam pembinaan</span>
            @endif
        </div>
    </div>

    <section class="kartu">
        <dl class="grid gap-4 p-5 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-bold text-slate-600">Kondisi awal</dt>
                <dd class="mt-0.5">{{ $kasus->kondisi_awal ?: 'Tidak dicatat' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-bold text-slate-600">Jenis pelanggaran</dt>
                <dd class="mt-0.5">{{ $kasus->namaPelanggaran() }}, tingkat {{ strtolower(KasusPembinaan::TINGKAT[$kasus->tingkat]) }}
                    @if ($kasus->jenisPelanggaran?->aspekKarakter)
                        <span class="text-slate-600">(aspek {{ $kasus->jenisPelanggaran->aspekKarakter->nama }})</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-bold text-slate-600">Tindakan pembinaan</dt>
                <dd class="mt-0.5">{{ $kasus->namaTindakan() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-bold text-slate-600">Komitmen murid</dt>
                <dd class="mt-0.5">{{ $kasus->komitmen ?: 'Tidak dicatat' }}</dd>
            </div>
        </dl>
        <div class="flex flex-wrap gap-2 border-t border-garis px-5 py-3">
            <a href="{{ route('guru.pembinaan.ubah', $kasus) }}" wire:navigate class="tombol-kedua"><x-heroicon-o-pencil-square class="h-4 w-4" /> Ubah data kasus</a>
            <button wire:click="hapus" wire:confirm="Hapus kasus ini beserta seluruh pemantauannya?" class="tombol-bahaya"><x-heroicon-o-trash class="h-4 w-4" /> Hapus</button>
        </div>
    </section>

    <section class="kartu p-5">
        <h2 class="text-lg font-extrabold">Pemantauan mingguan</h2>
        <p class="teks-bantu">Minggu 1 dimulai sehari setelah kasus dicatat. Pilih minggu untuk mengisi atau mengubah.</p>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach (range(1, 4) as $m)
                @php
                    $p = $terisi->get($m);
                    $sudahMulai = $m <= $dimulai;
                @endphp
                <button type="button" wire:click="pilihMinggu({{ $m }})" @disabled(! $sudahMulai) aria-pressed="{{ $minggu === $m ? 'true' : 'false' }}"
                    @class(['rounded-lg border-2 p-3 text-left transition-colors disabled:cursor-not-allowed disabled:opacity-60',
                        'border-slate-800 bg-slate-50' => $minggu === $m,
                        'border-amber-300 bg-amber-50' => $minggu !== $m && $sudahMulai && ! $p,
                        'border-garis bg-white hover:border-slate-400' => $minggu !== $m && ($p || ! $sudahMulai)])>
                    <span class="block text-sm font-extrabold">Minggu {{ $m }}</span>
                    <span class="block text-xs text-slate-600">mulai {{ $kasus->mulaiMinggu($m)->format('d/m') }}</span>
                    <span class="mt-2 block">
                        @if ($p)
                            <x-perkembangan :tingkat="$p->tingkat" />
                        @elseif ($sudahMulai)
                            <span class="text-sm font-bold text-amber-800">Belum diisi</span>
                        @else
                            <span class="text-sm text-slate-600">Belum tiba</span>
                        @endif
                    </span>
                </button>
            @endforeach
        </div>

        @if ($minggu)
            {{-- Sekali ketuk langsung tersimpan --}}
            <div class="mt-5 rounded-lg border border-garis bg-krem p-4">
                <p class="font-extrabold">Perkembangan {{ str($kasus->murid->nama)->before(' ') }} pada minggu ke-{{ $minggu }}</p>
                <p class="text-xs text-slate-600">Ketuk salah satu, langsung tersimpan. Bila ingin menambah catatan, tulis dulu sebelum mengetuk.</p>
                <label for="catatan-pantau" class="label mt-3">Catatan <span class="font-normal text-slate-600">(opsional)</span></label>
                <textarea id="catatan-pantau" wire:model="catatan" rows="2" class="isian bg-white"></textarea>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (KasusPembinaan::PERKEMBANGAN as $nilai => $info)
                        <button type="button" wire:click="pantau('{{ $nilai }}')" wire:loading.attr="disabled" aria-pressed="{{ $tingkat === $nilai ? 'true' : 'false' }}"
                            @class(['min-h-12 rounded-lg border-2 px-3 py-2 text-sm font-bold transition-colors',
                                'border-slate-800 bg-slate-800 text-white' => $tingkat === $nilai,
                                'border-garis bg-white text-slate-800 hover:border-slate-500' => $tingkat !== $nilai])>
                            {{ $info['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($kasus->pemantauan->whereNotNull('catatan')->isNotEmpty())
            <h3 class="mt-5 text-sm font-bold text-slate-600">Catatan pemantauan</h3>
            <ul class="mt-1 space-y-1 text-sm text-slate-700">
                @foreach ($kasus->pemantauan->whereNotNull('catatan') as $p)
                    <li><b>Minggu {{ $p->minggu }}:</b> {{ $p->catatan }} <span class="text-slate-600">({{ $p->user?->name }})</span></li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="kartu p-5">
        <h2 class="text-lg font-extrabold">Status akhir</h2>
        @if ($kasus->status_akhir)
            <p class="teks-bantu mt-1">Status akhir sudah ditetapkan: <b>{{ $kasus->labelStatus() }}</b>.</p>
            <button wire:click="bukaKembali" class="tombol-kedua mt-3">Ubah status atau lanjutkan pembinaan</button>
        @else
            <p class="teks-bantu mt-1">
                {{ $kasus->siapDitutup() ? 'Pemantauan 4 minggu sudah lengkap. Tetapkan status akhir murid ini.' : 'Tetapkan setelah masa pemantauan selesai. Selama belum ditetapkan, status kasus adalah Dalam pembinaan.' }}
            </p>
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach (KasusPembinaan::PERKEMBANGAN as $nilai => $info)
                    <button wire:click="tetapkanStatus('{{ $nilai }}')" wire:confirm="Tetapkan status akhir: {{ $info['label'] }}?" class="tombol-kedua">{{ $info['label'] }}</button>
                @endforeach
            </div>
        @endif
    </section>
</div>
