@props(['dari', 'sampai' => null])

{{-- Keterangan data awal rekonstruksi: hanya muncul bila rentang yang ditampilkan mencakup bulan tersebut --}}
@php
    $bulanRekonstruksi = \App\Models\Pengaturan::ambil('data_awal_rekonstruksi_bulan');
    $tampil = false;
    if ($bulanRekonstruksi) {
        $awal = \Illuminate\Support\Carbon::createFromFormat('!Y-m', $bulanRekonstruksi);
        $dariTgl = \Illuminate\Support\Carbon::parse($dari)->startOfDay();
        $sampaiTgl = \Illuminate\Support\Carbon::parse($sampai ?? $dari)->endOfDay();
        $tampil = $dariTgl->lte($awal->copy()->endOfMonth()) && $sampaiTgl->gte($awal);
    }
@endphp

@if ($tampil)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900']) }} role="note">
        <p class="font-bold">Data awal rekonstruksi {{ \App\Services\Rekap::labelBulan($awal) }}</p>
        @if ($catatan = \App\Models\Pengaturan::ambil('data_awal_rekonstruksi_catatan'))
            <p>{{ $catatan }}</p>
        @endif
    </div>
@endif
