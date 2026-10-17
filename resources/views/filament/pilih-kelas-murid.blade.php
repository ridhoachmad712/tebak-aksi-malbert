@use('App\Filament\Resources\MuridResource')

{{-- Halaman awal menu Murid di panel admin: pilih kelas dulu. Gaya ditulis langsung karena CSS aplikasi tidak dimuat di panel. --}}
<div>
    @if ($kelasList->isEmpty())
        <div style="padding:1.25rem;border:1px solid rgb(229 231 235);border-radius:.75rem;background:#fff">
            <p style="font-weight:700;margin:0">Belum ada kelas di tahun ajaran aktif.</p>
            <p style="margin:.35rem 0 0;color:rgb(75 85 99)">Buat kelas terlebih dahulu di menu Kelas, lalu kembali ke sini untuk menambahkan murid.</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(13rem,1fr));gap:.75rem">
            @foreach ($kelasList as $k)
                <a href="{{ MuridResource::getUrl('index', ['kelas' => $k->id]) }}"
                   style="display:block;padding:1rem 1.1rem;border:1px solid rgb(229 231 235);border-radius:.75rem;background:#fff;text-decoration:none;color:inherit"
                   onmouseover="this.style.borderColor='var(--primary-500)'" onmouseout="this.style.borderColor='rgb(229 231 235)'">
                    <span style="display:block;font-size:1.35rem;font-weight:800;color:rgb(17 24 39)">Kelas {{ $k->nama }}</span>
                    <span style="display:block;margin-top:.15rem;font-size:.9rem;font-weight:700;color:var(--primary-700)">{{ $k->murid_aktif_count }} murid</span>
                    <span style="display:block;margin-top:.35rem;font-size:.8rem;color:rgb(75 85 99)">Wali kelas: {{ $k->waliKelas?->name ?? 'belum ditetapkan' }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;margin-top:1.25rem;font-size:.9rem">
        @if ($tanpaKelas)
            <a href="{{ MuridResource::getUrl('index', ['kelas' => 'tanpa']) }}" style="font-weight:700;color:rgb(180 83 9);text-decoration:underline">{{ $tanpaKelas }} murid aktif belum punya kelas</a>
        @endif
        <a href="{{ MuridResource::getUrl('index', ['kelas' => 'semua']) }}" style="font-weight:700;color:var(--primary-700);text-decoration:underline">Cari di semua murid ({{ $semua }})</a>
    </div>
</div>
