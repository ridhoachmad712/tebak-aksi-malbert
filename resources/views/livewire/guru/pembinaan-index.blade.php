<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Kasus Pembinaan</h1>
            <p class="text-sm text-slate-500">Catat pelanggaran, lalu pantau perkembangan murid selama 4 minggu.</p>
        </div>
        <a href="{{ route('guru.pembinaan.baru') }}" wire:navigate class="tombol-utama"><x-heroicon-m-plus class="h-5 w-5" /> Catat Pelanggaran</a>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @foreach (['perlu' => '🔔 Perlu dipantau', 'aktif' => 'Dalam pembinaan', 'selesai' => 'Selesai'] as $nilai => $label)
            <button wire:click="$set('tab', '{{ $nilai }}')"
                @class(['rounded-full px-4 py-2 text-sm font-bold transition',
                    'bg-slate-800 text-white' => $tab === $nilai,
                    'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' => $tab !== $nilai])>
                {{ $label }}
                @isset($jumlah[$nilai])
                    <span @class(['ml-1 rounded-full px-1.5 text-xs', 'bg-white/20' => $tab === $nilai, 'bg-slate-100' => $tab !== $nilai])>{{ $jumlah[$nilai] }}</span>
                @endisset
            </button>
        @endforeach
        <div class="ml-auto flex flex-wrap gap-2">
            @if ($daftarKelas->count() > 1)
                <select wire:model.live="kelas" class="isian w-auto py-2 pr-9 font-bold">
                    <option value="">Semua kelas</option>
                    @foreach ($daftarKelas as $k)
                        <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                    @endforeach
                </select>
            @endif
            <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama murid…" class="isian w-48 py-2">
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($kasus as $k)
            @php
                $belum = $k->mingguBelumDipantau();
                $terisi = $k->pemantauan->keyBy('minggu');
            @endphp
            <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate wire:key="kasus-{{ $k->id }}"
               class="kartu flex flex-col gap-3 p-4 transition hover:border-brand-300 sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <p class="font-extrabold">{{ $k->murid->nama }} <span class="font-semibold text-slate-400">· Kelas {{ $k->kelas->nama }}</span></p>
                    <p class="truncate text-sm text-slate-600">{{ $k->namaPelanggaran() }} · {{ \App\Models\KasusPembinaan::TINGKAT[$k->tingkat] }} · {{ $k->tanggal->format('d/m/Y') }}</p>
                    @if ($belum)
                        <p class="mt-1 text-sm font-bold text-amber-700">🔔 Perlu dipantau minggu ke-{{ implode(', ', $belum) }}</p>
                    @elseif ($k->siapDitutup())
                        <p class="mt-1 text-sm font-bold text-toska-700">🏁 Siap ditetapkan status akhirnya</p>
                    @endif
                </div>
                <div class="flex items-center gap-1.5">
                    @foreach (range(1, 4) as $m)
                        @php $p = $terisi->get($m); @endphp
                        <span class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-base" title="Minggu {{ $m }}">
                            {{ $p ? \App\Models\KasusPembinaan::PERKEMBANGAN[$p->tingkat]['ikon'] : '·' }}
                        </span>
                    @endforeach
                    <span class="ml-2 min-w-32 text-right text-sm font-bold text-slate-700">{{ $k->labelStatus() }}</span>
                </div>
            </a>
        @empty
            <div class="kartu p-8 text-center">
                <div class="text-5xl">🌈</div>
                <p class="mt-2 font-bold">{{ $tab === 'perlu' ? 'Tidak ada murid yang perlu dipantau saat ini.' : 'Belum ada kasus di sini.' }}</p>
            </div>
        @endforelse
    </div>

    @if ($kasus instanceof \Illuminate\Contracts\Pagination\Paginator)
        {{ $kasus->links() }}
    @endif
</div>
