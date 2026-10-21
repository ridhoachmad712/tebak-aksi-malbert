{{-- Rincian satu aspek inovasi (isi dialog kartu aspek). Butuh: $a --}}
@use('App\Services\Rekap')
@use('App\Services\RingkasanMonev')

@php
    // Target dalam jumlah murid, dibulatkan ke atas agar tidak pernah kurang dari target persennya
    $targetMurid = fn ($target, $dari) => (int) ceil(round($target / 100 * $dari, 6));
    $warnaBatang = fn ($nilai, $target) => match (RingkasanMonev::warna($nilai, (float) $target)) {
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        default => 'bg-slate-700',
    };
@endphp

@if (isset($a['mingguan']))
    {{-- Penguatan Karakter: tren jumlah murid yang melanggar per minggu --}}
    @php
        $m = $a['mingguan'];
        $awal = $m[0] ?? 0;
        $akhirMinggu = end($m) ?: 0;
        $maks = max(1, ...$m);
    @endphp
    <p class="mt-1 text-2xl font-extrabold leading-tight tabular-nums text-slate-900">{{ $awal }} → {{ $akhirMinggu }} <span class="text-sm font-bold text-slate-600">murid melanggar</span></p>
    <div class="mt-2 flex h-12 items-end gap-1.5" aria-hidden="true">
        @foreach ($m as $i => $j)
            <div class="flex flex-1 flex-col items-center justify-end gap-0.5">
                <span class="text-[11px] font-bold tabular-nums text-slate-700">{{ $j }}</span>
                <div class="w-full rounded-t bg-slate-700" style="height: {{ max(2, round($j / $maks * 32)) }}px"></div>
            </div>
        @endforeach
    </div>
    <div class="mt-0.5 flex gap-1.5 text-center text-[10px] font-semibold text-slate-500" aria-hidden="true">
        @foreach ($m as $i => $j)
            <span class="flex-1">M{{ $i + 1 }}</span>
        @endforeach
    </div>
    <p class="sr-only">{{ $a['rincian'] }}</p>
    <p class="mt-2 text-sm text-slate-800">
        @if ($awal > 0 && $akhirMinggu < $awal)
            Turun {{ $awal - $akhirMinggu }} murid ({{ Rekap::persenTeks(round(($awal - $akhirMinggu) / $awal * 100, 1)) }}) dalam sebulan
        @elseif ($akhirMinggu > $awal)
            Naik {{ $akhirMinggu - $awal }} murid dalam sebulan
        @else
            Tidak berubah dalam sebulan
        @endif
    </p>
    <p class="mt-1 text-xs text-slate-600">
        @if ($a['nilai'] === null)
            Penurunan dibanding bulan lalu belum dapat dinilai (data pembanding belum ada). Target ≥ {{ Rekap::persenTeks((float) $a['target']) }}.
        @else
            Dibanding bulan lalu {{ $a['nilai'] < 0 ? 'naik' : 'turun' }} <x-nilai-target :nilai="$a['nilai']" :target="$a['target']" :teks="Rekap::persenTeks(abs($a['nilai']))" class="text-xs" /> · target turun ≥ {{ Rekap::persenTeks((float) $a['target']) }}
            @if (! empty($a['pembanding']))
                <span class="block">{{ $a['pembanding'] }}</span>
            @endif
        @endif
    </p>
@elseif (isset($a['jumlah']))
    {{-- Aspek berbasis jumlah murid --}}
    @php
        $tm = $a['dari'] ? $targetMurid($a['target'], $a['dari']) : 0;
        $selisih = $a['jumlah'] - $tm;
    @endphp
    @if ($a['dari'] > 0 && $a['nilai'] !== null)
        <p class="mt-1 text-2xl font-extrabold leading-tight tabular-nums text-slate-900">{{ $a['jumlah'] }} <span class="text-base font-bold text-slate-500">/ {{ $a['dari'] }}</span></p>
        <p class="text-xs font-semibold text-slate-600">{{ $a['satuan'] }}</p>
        {{-- Batang kemajuan dengan garis penanda target --}}
        <div class="relative mt-2 h-2 rounded-full bg-slate-100" role="img" aria-label="{{ $a['jumlah'] }} dari {{ $a['dari'] }}, target {{ $tm }}">
            <div class="h-2 rounded-full {{ $warnaBatang($a['nilai'], $a['target']) }}" style="width: {{ min(100, $a['nilai']) }}%"></div>
            <div class="absolute -top-1 h-4 w-0.5 bg-slate-900" style="left: calc({{ min(100, (float) $a['target']) }}% - 1px)"></div>
        </div>
        <p class="mt-2 text-xs text-slate-600">{{ Rekap::persenTeks($a['nilai']) }} · target {{ $tm }} murid ({{ Rekap::persenTeks((float) $a['target']) }})</p>
        <p @class(['mt-0.5 text-sm font-bold', 'text-slate-800' => $selisih >= 0, 'text-rose-800' => $selisih < 0])>
            {{ $selisih >= 0 ? '+'.$selisih.' murid di atas target' : 'kurang '.abs($selisih).' murid lagi' }}
        </p>
    @else
        <p class="mt-1 text-2xl font-bold text-slate-400">–</p>
        <p class="mt-1 text-xs text-slate-600">{{ $a['kosong'] ?? 'Belum ada catatan pada bulan ini.' }}</p>
    @endif

    @if (array_key_exists('jumlah2', $a))
        {{-- Ekosistem Literat: murid yang menghasilkan minimal 1 karya --}}
        <div class="mt-3 border-t border-garis pt-2">
            @if ($a['jumlah2'] !== null && $a['dari2'] > 0)
                @php $tm2 = $targetMurid($a['target2'], $a['dari2']); $sel2 = $a['jumlah2'] - $tm2; @endphp
                <p class="text-sm text-slate-800">Murid berkarya <b class="tabular-nums">{{ $a['jumlah2'] }} / {{ $a['dari2'] }}</b></p>
                <div class="relative mt-1.5 h-1.5 rounded-full bg-slate-100" role="img" aria-label="{{ $a['jumlah2'] }} dari {{ $a['dari2'] }} murid berkarya, target {{ $tm2 }}">
                    <div class="h-1.5 rounded-full {{ $warnaBatang($a['nilai2'], $a['target2']) }}" style="width: {{ min(100, (float) $a['nilai2']) }}%"></div>
                    <div class="absolute -top-1 h-3.5 w-0.5 bg-slate-900" style="left: calc({{ min(100, (float) $a['target2']) }}% - 1px)"></div>
                </div>
                <p class="mt-1 text-xs text-slate-600">{{ Rekap::persenTeks($a['nilai2']) }} · target {{ $tm2 }} murid · {{ $sel2 >= 0 ? 'tercapai' : 'kurang '.abs($sel2).' murid' }}</p>
            @else
                <p class="text-sm text-slate-800">Murid berkarya: <span class="font-semibold text-slate-500">belum tercatat</span></p>
            @endif
            @isset($a['jumlah_karya'])
                <p class="mt-0.5 text-xs text-slate-600">{{ $a['jumlah_karya'] }} karya dilaporkan</p>
            @endisset
        </div>
    @endif
@endif
