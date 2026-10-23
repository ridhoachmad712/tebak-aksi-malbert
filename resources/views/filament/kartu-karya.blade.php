{{-- Kartu satu karya literasi di panel admin, meniru tampilan halaman Karya Literasi. Gaya ditulis langsung karena panel memakai tema bawaan Filament. --}}
@php
    $k = $getRecord();
    $foto = $k->foto->first();
    $pemilik = $k->namaPemilik();
@endphp

<div style="display:flex;flex-direction:column;gap:.6rem;width:100%">
    @if ($foto)
        <button type="button" x-on:click.stop="$dispatch('buka-foto', @js(['url' => $foto->url(), 'ket' => $k->judul.' · '.$pemilik]))"
            title="Perbesar foto" aria-label="Perbesar foto {{ $k->judul }}"
            style="display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in">
            <img src="{{ $foto->url() }}" alt="Foto karya {{ $k->judul }}" loading="lazy"
                style="display:block;width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:.6rem;background:rgb(241 245 249)">
        </button>
        @if ($k->foto->count() > 1)
            <span style="font-size:.75rem;color:rgb(71 85 105)">{{ $k->foto->count() }} foto</span>
        @endif
    @else
        <div style="display:grid;place-items:center;width:100%;aspect-ratio:1/1;border-radius:.6rem;background:rgb(241 245 249);color:rgb(100 116 139);font-size:.8rem;font-weight:600;text-align:center">
            <span><x-filament::icon icon="heroicon-o-photo" style="height:2rem;width:2rem;margin:0 auto" />Foto belum diunggah</span>
        </div>
    @endif
    <div>
        <p style="font-weight:800;line-height:1.3;color:rgb(15 23 42)">{{ $k->judul }}</p>
        <p style="margin-top:.2rem;font-size:.875rem;font-weight:600;color:rgb(30 41 59)">{{ $pemilik ?: '-' }}@if ($k->guru_pemilik_id) <span style="font-weight:400;color:rgb(71 85 105)">(guru)</span>@endif</p>
        <p style="margin-top:.35rem;font-size:.75rem;color:rgb(71 85 105)">{{ $k->namaJenis() }} · Kelas {{ $k->kelas?->nama ?? '-' }} · {{ \App\Services\Rekap::tanggalPanjang($k->tanggal) }}</p>
    </div>
</div>
