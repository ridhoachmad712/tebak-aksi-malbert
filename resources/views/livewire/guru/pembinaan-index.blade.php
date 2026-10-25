@use('App\Models\KasusPembinaan')

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Kasus Pembinaan</h1>
            <p class="teks-bantu">Catat pelanggaran saat terjadi, lalu pantau perkembangan murid seminggu sekali selama 4 minggu.</p>
        </div>
        <a href="{{ route('guru.pembinaan.baru') }}" wire:navigate class="tombol-utama"><x-heroicon-m-plus class="h-5 w-5" /> Catat pelanggaran</a>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @foreach (['perlu' => 'Perlu dipantau', 'aktif' => 'Dalam pembinaan', 'selesai' => 'Selesai'] as $nilai => $label)
            <button wire:click="$set('tab', '{{ $nilai }}')" @class(['tab', 'tab-aktif' => $tab === $nilai, 'tab-pasif' => $tab !== $nilai])>
                {{ $label }}
                @isset($jumlah[$nilai])
                    <span class="ml-1 tabular-nums opacity-80">({{ $jumlah[$nilai] }})</span>
                @endisset
            </button>
        @endforeach
        <div class="ml-auto flex flex-wrap gap-2">
            <select wire:model.live="bulan" class="isian w-auto pr-9 font-bold" aria-label="Saring bulan kejadian">
                <option value="">Semua bulan</option>
                @foreach ($daftarBulan as $nilai => $teks)
                    <option value="{{ $nilai }}">{{ $teks }}</option>
                @endforeach
            </select>
            @if ($daftarKelas->count() > 1)
                <select wire:model.live="kelas" class="isian w-auto pr-9 font-bold" aria-label="Saring kelas">
                    <option value="">Semua kelas</option>
                    @foreach ($daftarKelas as $k)
                        <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                    @endforeach
                </select>
            @endif
            <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama murid" aria-label="Cari nama murid" class="isian w-52">
        </div>
    </div>

    @if ($kasus->isNotEmpty())
        {{-- Satu kelompok per bulan kejadian, terbaru di atas --}}
        @foreach ($perBulan as $kodeBulan => $kasusBulan)
        <section class="space-y-2" aria-labelledby="bulan-{{ $kodeBulan }}">
        <h2 id="bulan-{{ $kodeBulan }}" class="text-sm font-extrabold uppercase tracking-wide text-slate-600">
            {{ \App\Services\Rekap::labelBulan(\Illuminate\Support\Carbon::createFromFormat('!Y-m', $kodeBulan)) }}
            <span class="font-bold normal-case tracking-normal">· {{ $kasusBulan->count() }} kasus</span>
        </h2>
        <ul class="kartu divide-y divide-garis">
            @foreach ($kasusBulan as $k)
                @php
                    $belum = $k->mingguBelumDipantau();
                    $terisi = $k->pemantauan->keyBy('minggu');
                @endphp
                <li wire:key="kasus-{{ $k->id }}">
                    <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="flex flex-col gap-3 px-4 py-3 hover:bg-krem lg:flex-row lg:items-center">
                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold">{{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></p>
                            <p class="truncate text-sm text-slate-600">{{ $k->namaPelanggaran() }} · {{ KasusPembinaan::TINGKAT[$k->tingkat] }} · dicatat {{ $k->tanggal->format('d/m/Y') }}</p>
                            @if ($belum)
                                <p class="mt-1 text-sm font-bold text-amber-800">Minggu ke-{{ implode(', ', $belum) }} belum dipantau</p>
                            @elseif ($k->siapDitutup())
                                <p class="mt-1 text-sm font-bold text-toska-800">Siap ditetapkan status akhirnya</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach (range(1, 4) as $m)
                                <span class="flex items-center gap-1 text-xs text-slate-600">
                                    <span class="sr-only lg:not-sr-only">M{{ $m }}</span>
                                    <x-perkembangan :tingkat="$terisi->get($m)?->tingkat" ringkas />
                                </span>
                            @endforeach
                        </div>
                        <span class="text-sm font-bold text-slate-800 lg:w-36 lg:text-right">{{ $k->labelStatus() }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        </section>
        @endforeach
    @else
        <div class="kartu p-5 text-sm text-slate-600">
            @if ($cari)
                Tidak ada kasus untuk murid bernama "{{ $cari }}"{{ $bulan ? ' pada '.$daftarBulan[$bulan] : '' }}.
            @elseif ($bulan && array_key_exists($bulan, $daftarBulan))
                Tidak ada kasus {{ $tab === 'selesai' ? 'yang selesai' : ($tab === 'perlu' ? 'yang perlu dipantau' : 'dalam pembinaan') }} dengan kejadian pada {{ $daftarBulan[$bulan] }}. Pilih <button type="button" wire:click="$set('bulan', '')" class="font-bold underline">Semua bulan</button> untuk melihat kasus lainnya.
            @elseif ($tab === 'perlu')
                Tidak ada murid yang menunggu pemantauan minggu ini.
            @elseif ($tab === 'selesai')
                Belum ada kasus yang ditetapkan status akhirnya.
            @else
                Belum ada pelanggaran yang sedang dibina. Gunakan tombol Catat pelanggaran saat ada kejadian.
            @endif
        </div>
    @endif

    @if ($kasus instanceof \Illuminate\Contracts\Pagination\Paginator)
        {{ $kasus->links() }}
    @endif
</div>
