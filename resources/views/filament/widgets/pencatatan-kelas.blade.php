@use('App\Services\TugasAdmin')

<x-filament-widgets::widget id="pencatatan-kelas">
    <x-filament::section>
        <x-slot name="heading">Pencatatan kelas minggu ini</x-slot>
        <x-slot name="description">Kegiatan harian yang sudah dicatat sejak Senin. Kelas yang tertinggal ditandai kuning.</x-slot>

        @if ($kelas)
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead>
                        <tr style="text-align:left;color:rgb(75 85 99);border-bottom:1px solid rgb(229 231 235)">
                            <th style="padding:.5rem .5rem .5rem 0;font-weight:600">Kelas</th>
                            <th style="padding:.5rem;font-weight:600">Wali kelas</th>
                            <th style="padding:.5rem;font-weight:600">Terakhir dicatat</th>
                            <th style="padding:.5rem;font-weight:600;text-align:right">Minggu ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelas as $k)
                            @php $tertinggal = $k['jumlah_murid'] > 0 && $k['hari_terlewat'] >= 2; @endphp
                            <tr style="border-bottom:1px solid rgb(243 244 246);{{ $tertinggal ? 'background:rgb(254 252 232)' : '' }}">
                                <td style="padding:.55rem .5rem .55rem 0;font-weight:700;color:rgb(17 24 39)">{{ $k['kelas'] }}</td>
                                <td style="padding:.55rem .5rem;color:{{ $k['wali'] ? 'rgb(55 65 81)' : 'rgb(180 83 9)' }}">{{ $k['wali'] ?? 'Belum ditetapkan' }}</td>
                                <td style="padding:.55rem .5rem;color:{{ $tertinggal ? 'rgb(146 64 14)' : 'rgb(55 65 81)' }};font-weight:{{ $tertinggal ? 700 : 400 }}">
                                    {{ $k['jumlah_murid'] ? TugasAdmin::teksTerakhir($k['terakhir']) : 'Belum ada murid' }}
                                </td>
                                <td style="padding:.55rem .5rem;text-align:right;font-variant-numeric:tabular-nums;font-weight:700;color:rgb(17 24 39)">
                                    {{ $k['hari_minggu_ini'] ? $k['minggu_ini'].'/'.$k['hari_minggu_ini'].' hari' : '–' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (! ($kelas[0]['hari_minggu_ini'] ?? 0))
                <p style="margin:.75rem 0 0;font-size:.8rem;color:rgb(107 114 128)">Belum ada hari kegiatan minggu ini (akhir pekan atau hari libur).</p>
            @endif
        @else
            <p style="margin:0;color:rgb(75 85 99)">Belum ada kelas di tahun ajaran aktif. Buat kelas di menu Kelas.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
