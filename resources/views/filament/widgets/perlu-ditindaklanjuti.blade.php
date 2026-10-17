<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Perlu ditindaklanjuti</x-slot>

        @if ($tugas)
            <ul style="margin:0;padding:0;list-style:none">
                @foreach ($tugas as $t)
                    <li style="border-top:{{ $loop->first ? '0' : '1px solid rgb(243 244 246)' }}">
                        <a href="{{ $t['url'] }}" style="display:flex;align-items:flex-start;gap:.75rem;padding:.7rem .25rem;text-decoration:none;color:inherit"
                           onmouseover="this.style.background='rgb(249 250 251)'" onmouseout="this.style.background='transparent'">
                            <x-filament::icon :icon="$t['tingkat'] === 'penting' ? 'heroicon-o-bell-alert' : 'heroicon-o-exclamation-circle'"
                                style="height:1.35rem;width:1.35rem;flex-shrink:0;margin-top:.1rem;color:{{ $t['tingkat'] === 'penting' ? 'var(--primary-600)' : 'rgb(217 119 6)' }}" />
                            <span style="flex:1;min-width:0">
                                <span style="display:block;font-weight:700;color:rgb(17 24 39)">{{ $t['judul'] }}</span>
                                @if ($t['rincian'])
                                    <span style="display:block;font-size:.875rem;color:rgb(75 85 99)">{{ $t['rincian'] }}</span>
                                @endif
                            </span>
                            <x-filament::icon icon="heroicon-m-chevron-right" style="height:1.1rem;width:1.1rem;flex-shrink:0;margin-top:.15rem;color:rgb(156 163 175)" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p style="margin:0;color:rgb(75 85 99)">Tidak ada yang perlu ditindaklanjuti hari ini. Pendaftar, pengaduan, kelas, dan murid sudah beres.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
