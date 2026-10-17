@php $ta = \App\Services\Rekap::tahunAjaranAktif(); @endphp

<x-filament-widgets::widget>
    <p style="margin:0;display:flex;flex-wrap:wrap;align-items:center;gap:.25rem 1.25rem;font-size:.875rem;color:rgb(75 85 99)">
        <span>Tahun ajaran <b style="color:rgb(17 24 39)">{{ $ta ? 'Semester '.(\App\Services\Rekap::semesterDari(today()) === 1 ? 'Ganjil' : 'Genap').' '.$ta->nama : 'belum diatur' }}</b></span>
        <span><b style="color:rgb(17 24 39)">{{ $angka['kelas'] }}</b> kelas</span>
        <span><b style="color:rgb(17 24 39)">{{ $angka['guru'] }}</b> guru aktif</span>
        <span><b style="color:rgb(17 24 39)">{{ $angka['murid'] }}</b> murid aktif</span>
        <a href="{{ route('monev') }}" target="_blank" rel="noopener" style="margin-left:auto;font-weight:700;color:var(--primary-700);text-decoration:underline">Lihat capaian program di Monev</a>
    </p>
</x-filament-widgets::widget>
