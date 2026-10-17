{{-- Tautan keluar dari panel admin, di bilah atas samping menu profil. Dibuka di tab baru agar panel admin tetap terbuka. --}}
@php
    $tautan = [
        ['url' => route('monev'), 'label' => 'Monev', 'ikon' => 'heroicon-o-presentation-chart-line'],
        ['url' => route('guru.kegiatan'), 'label' => 'Halaman Guru', 'ikon' => 'heroicon-o-check-circle'],
        ['url' => route('beranda'), 'label' => 'Halaman Publik', 'ikon' => 'heroicon-o-globe-alt'],
    ];
@endphp

<nav aria-label="Buka aplikasi" style="display:flex;align-items:center;gap:.25rem;margin-inline-end:.5rem">
    @foreach ($tautan as $t)
        <a href="{{ $t['url'] }}" target="_blank" rel="noopener" title="{{ $t['label'] }} (tab baru)" aria-label="{{ $t['label'] }} (tab baru)"
           style="display:inline-flex;align-items:center;gap:.4rem;min-height:2.5rem;padding:0 .7rem;border-radius:.5rem;font-size:.875rem;font-weight:600;color:rgb(55 65 81);text-decoration:none"
           onmouseover="this.style.background='rgb(243 244 246)'" onmouseout="this.style.background='transparent'">
            <x-filament::icon :icon="$t['ikon']" style="height:1.25rem;width:1.25rem;color:var(--primary-600)" />
            <span class="tautan-aplikasi-label">{{ $t['label'] }}</span>
        </a>
    @endforeach
</nav>

{{-- Di layar sempit hanya ikon yang tampil agar bilah atas tidak penuh --}}
<style>
    @media (max-width: 767px) {
        .tautan-aplikasi-label { display: none; }
    }
</style>
