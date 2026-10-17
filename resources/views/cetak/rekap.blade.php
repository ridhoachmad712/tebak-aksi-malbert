@use('App\Models\Pengaturan')
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $laporan['judul'] }}</title>
    <style>
        @page { margin: 1.6cm 1.4cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 6px; margin-bottom: 12px; }
        .kop img { height: 48px; float: left; }
        .kop .sekolah { font-size: 13px; font-weight: bold; }
        .kop .program { font-size: 10px; }
        h1 { text-align: center; font-size: 13px; margin: 0 0 2px; }
        .sub { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #334155; padding: 4px 5px; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; text-align: center; }
        td.tengah { text-align: center; }
        .catatan { margin-top: 8px; font-size: 9px; color: #475569; }
        .ttd { width: 45%; margin-left: 55%; margin-top: 28px; text-align: center; }
        .ttd .nama { margin-top: 56px; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="kop">
        @if (($logo = Pengaturan::ambil('logo_sekolah')) && is_file(storage_path('app/public/'.$logo)))
            <img src="{{ storage_path('app/public/'.$logo) }}" alt="Logo">
        @endif
        <div class="sekolah">{{ Pengaturan::ambil('nama_sekolah') }}</div>
        <div class="program">Program Inovasi TEBAK AKSI (TEras Baca Kelas Asah Karakter dan literaSI)</div>
        @if ($alamat = Pengaturan::ambil('alamat_sekolah'))
            <div class="program">{{ $alamat }}</div>
        @endif
    </div>

    <h1>{{ $laporan['judul'] }}</h1>
    <div class="sub">{{ $labelKelas }} · Bulan {{ $labelBulan }}{{ $laporan['n'] !== null ? ' · '.$laporan['n'].' hari kegiatan' : '' }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 24px">No</th>
                @foreach ($laporan['kolom'] as $k)
                    <th>{{ $k }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($laporan['baris'] as $no => $baris)
                <tr>
                    <td class="tengah">{{ $no + 1 }}</td>
                    @foreach ($baris as $nilai)
                        @if (is_array($nilai))
                            <td class="tengah">
                                @if ($angka)
                                    {{ $nilai['jumlah'] }}/{{ $nilai['n'] }}
                                @else
                                    {{ $nilai['persen'] !== null && $nilai['persen'] >= $batas ? '✓' : '–' }}
                                @endif
                            </td>
                        @else
                            <td @class(['tengah' => is_int($nilai) || str_starts_with($laporan['kolom'][$loop->index] ?? '', 'Minggu')])>{{ $nilai }}{{ is_int($nilai) ? ' karya' : '' }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($laporan['kolom']) + 1 }}" class="tengah">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($laporan['n'] !== null)
        <p class="catatan">
            @if ($angka)
                Angka menunjukkan jumlah centang dibanding jumlah hari kegiatan.
            @else
                ✓ = tercapai minimal {{ (int) $batas }}% dari {{ $laporan['n'] }} hari kegiatan; – = belum tercapai.
            @endif
            Data disusun otomatis dari catatan harian guru di aplikasi TEBAK AKSI.
        </p>
    @endif

    <div class="ttd">
        <div>{{ Pengaturan::ambil('kota_ttd') }}, {{ now()->day }} {{ \App\Services\Rekap::BULAN[now()->month] }} {{ now()->year }}</div>
        <div>Kepala Sekolah,</div>
        <div class="nama">{{ Pengaturan::ambil('nama_kepala_sekolah') ?: '.................................' }}</div>
        @if ($nip = Pengaturan::ambil('nip_kepala_sekolah'))
            <div>NIP. {{ $nip }}</div>
        @endif
    </div>
</body>
</html>
