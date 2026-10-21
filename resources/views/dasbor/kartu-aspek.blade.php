{{-- Satu kartu aspek inovasi yang ringkas; rinciannya terbuka di dialog saat kartu diketuk. Butuh: $a --}}
@use('App\Services\RingkasanMonev')

@php
    // Target dalam jumlah murid, dibulatkan ke atas agar tidak pernah kurang dari target persennya
    $targetMurid = fn ($target, $dari) => (int) ceil(round($target / 100 * $dari, 6));
    $judulStatus = match ($a['status']) {
        'tercapai' => 'Tercapai',
        'kurang' => 'Belum tercapai',
        'belum_cukup' => $a['keterangan_status'] ?? 'Belum dapat dinilai',
        default => 'Belum dapat dinilai',
    };
    $adaJumlah = isset($a['jumlah']) && $a['dari'] > 0 && $a['nilai'] !== null;
    $selisih = $adaJumlah ? $a['jumlah'] - $targetMurid($a['target'], $a['dari']) : null;
    if (isset($a['mingguan'])) {
        $awal = $a['mingguan'][0] ?? 0;
        $akhirMinggu = end($a['mingguan']) ?: 0;
    }
    $idDialog = 'rincian-aspek-'.$loop->index;
@endphp

<div x-data @class(['col-span-2 lg:col-span-1' => $akhir ?? false])>
    <button type="button" x-on:click="$refs.rincian.showModal()" aria-haspopup="dialog"
        class="flex h-full w-full flex-col rounded-lg border border-garis px-4 py-3 text-left transition hover:border-slate-400 hover:bg-krem focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-700">
        <span class="flex items-start justify-between gap-2">
            <span class="text-sm font-bold text-slate-800">{{ $a['nama'] }}</span>
            <x-heroicon-o-chevron-right class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" aria-hidden="true" />
        </span>

        @if (isset($a['mingguan']))
            <span class="mt-1 text-2xl font-extrabold leading-tight tabular-nums text-slate-900">{{ $awal }} → {{ $akhirMinggu }}</span>
            <span class="text-xs font-semibold text-slate-600">murid melanggar, minggu pertama → terakhir</span>
        @elseif ($adaJumlah)
            <span class="mt-1 text-2xl font-extrabold leading-tight tabular-nums text-slate-900">{{ $a['jumlah'] }} <span class="text-base font-bold text-slate-500">/ {{ $a['dari'] }}</span></span>
            <span class="text-xs font-semibold text-slate-600">{{ $a['satuan'] }}</span>
        @else
            <span class="mt-1 text-2xl font-bold text-slate-400">–</span>
            <span class="text-xs font-semibold text-slate-600">belum ada catatan</span>
        @endif

        <span class="mt-auto block pt-3">
            <span @class(['block text-xs font-bold', 'text-slate-900' => $a['status'] === 'kurang', 'text-slate-600' => $a['status'] !== 'kurang'])>{{ $judulStatus }}</span>
            @if ($selisih !== null)
                <span @class(['block text-xs', 'text-slate-600' => $selisih >= 0, 'font-bold text-rose-800' => $selisih < 0])>{{ $selisih >= 0 ? '+'.$selisih.' murid dari target' : 'kurang '.abs($selisih).' murid' }}</span>
            @endif
        </span>
    </button>

    <dialog x-ref="rincian" x-on:click.self="$el.close()" aria-labelledby="{{ $idDialog }}"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl border border-garis bg-white p-0 text-slate-900 shadow-xl backdrop:bg-slate-900/40">
        <div class="p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 id="{{ $idDialog }}" class="text-lg font-extrabold">{{ $a['nama'] }}</h3>
                    <p class="text-sm text-slate-600">{{ ucfirst($a['uraian']) }}</p>
                </div>
                <button type="button" x-on:click="$refs.rincian.close()" class="-mr-1 rounded-lg p-1 text-slate-600 hover:bg-slate-100" aria-label="Tutup rincian">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>
            <div class="mt-3">
                @include('dasbor.rincian-aspek')
            </div>
            <p class="mt-3 text-xs text-slate-600">Garis tegak pada batang menandai target. Batang dan angka berwarna hanya bila di bawah target.</p>
            <p @class(['mt-4 border-t border-garis pt-3 text-sm font-bold', 'text-slate-900' => $a['status'] === 'kurang', 'text-slate-600' => $a['status'] !== 'kurang'])>Status: {{ $judulStatus }}</p>
        </div>
    </dialog>
</div>
