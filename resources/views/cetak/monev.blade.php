@use('App\Models\Pengaturan')
@use('App\Services\Rekap')
@use('App\Services\RingkasanMonev')
@php
    $warna = fn ($nilai, $target) => match (RingkasanMonev::warna($nilai, (float) $target)) {
        'amber' => 'background:#fef3c7;color:#78350f;font-weight:bold',
        'rose' => 'background:#ffe4e6;color:#9f1239;font-weight:bold',
        default => '',
    };
    $rataRataKelas = [];
    foreach (['jumlah', 'persen', 'budaya', 'literasi', 'kasus_total', 'kasus_aktif', 'kasus_selesai'] as $kolomRataRata) {
        $rataRataKelas[$kolomRataRata] = Rekap::rerata(collect($indikator['per_kelas'])->pluck($kolomRataRata)->all());
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Ringkasan Monev TEBAK AKSI, {{ $label }}</title>
    <style>
        @page { margin: 1.2cm 1.3cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 5px; margin-bottom: 8px; }
        .kop img { height: 40px; float: left; }
        .kop .sekolah { font-size: 12px; font-weight: bold; }
        h1 { text-align: center; font-size: 12px; margin: 0 0 1px; }
        .sub { text-align: center; margin-bottom: 8px; }
        h2 { font-size: 10px; margin: 9px 0 3px; }
        .ringkasan { border: 1px solid #334155; border-left: 4px solid #1e293b; padding: 5px 7px; font-size: 10px; }
        .ringkasan p { margin: 0 0 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 2px 4px; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; text-align: left; }
        td.angka, th.angka { text-align: center; }
        tr.pilar td { background: #f8fafc; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .catatan { margin-top: 3px; font-size: 8px; color: #475569; }
        .dua td.kolom { width: 50%; border: none; padding: 0 4px 0 0; }
        ul { margin: 0; padding-left: 12px; }
        .ttd { width: 40%; margin-left: 60%; margin-top: 14px; text-align: center; }
        .ttd .nama { margin-top: 40px; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="kop">
        @if (($logo = Pengaturan::ambil('logo_sekolah')) && is_file(storage_path('app/public/'.$logo)))
            <img src="{{ storage_path('app/public/'.$logo) }}" alt="Logo">
        @endif
        <div class="sekolah">{{ Pengaturan::ambil('nama_sekolah') }}</div>
        <div>Program Inovasi TEBAK AKSI (TEras Baca Kelas Asah Karakter dan literaSI)</div>
        @if ($alamat = Pengaturan::ambil('alamat_sekolah'))
            <div>{{ $alamat }}</div>
        @endif
    </div>

    <h1>Ringkasan Monitoring dan Evaluasi</h1>
    <div class="sub">Seluruh sekolah Â· {{ $label }} Â· {{ $hari }} hari kegiatan</div>

    <div class="ringkasan">
        @foreach ($kalimat as $k)
            <p @if ($loop->first) style="font-weight:bold" @endif>{{ $k }}</p>
        @endforeach
    </div>

    <h2>Capaian per pilar program</h2>
    <table>
        <tr>
            <th>Indikator</th>
            <th class="angka">Capaian</th>
            <th class="angka">Target</th>
            <th>Selisih</th>
            <th>{{ $labelLalu ? 'Dibanding '.$labelLalu : 'Dibanding periode lalu' }}</th>
        </tr>
        @foreach ($pilar as $namaPilar => $baris)
            <tr class="pilar"><td colspan="5">{{ $namaPilar }}</td></tr>
            @foreach ($baris as $b)
                <tr>
                    <td>{{ $b['judul'] }}</td>
                    <td class="angka" style="{{ $b['status'] === 'kurang' ? $warna($b['nilai'], $b['target']) : '' }}">{{ Rekap::persenTeks($b['nilai']) }}</td>
                    <td class="angka">â‰¥ {{ Rekap::persenTeks((float) $b['target']) }}</td>
                    <td>{{ match ($b['status']) { 'tercapai' => 'Tercapai', 'kurang' => 'kurang '.Rekap::persenTeks($b['selisih'], false).' poin', 'belum_cukup' => 'Data belum cukup', default => 'Belum ada data' } }}</td>
                    <td>{{ $b['tren'] === null ? 'â€“' : (abs($b['tren']) < 0.1 ? 'tetap' : ($b['tren'] > 0 ? 'naik ' : 'turun ').Rekap::persenTeks(abs($b['tren']), false).' poin') }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

    <h2>Peta kelas</h2>
    <table>
        <tr>
            <th>Kelas</th>
            <th>Wali kelas</th>
            <th class="angka">Murid</th>
            <th class="angka">Murid aktif (â‰¥ {{ Rekap::persenTeks((float) $indikator['murid_aktif']['target']) }})</th>
            <th class="angka">Budaya Membaca (â‰¥ {{ Rekap::persenTeks((float) $indikator['budaya_membaca']['target']) }})</th>
            <th class="angka">Kemampuan Literasi (â‰¥ {{ Rekap::persenTeks((float) $indikator['kemampuan_literasi']['target']) }})</th>
            <th class="angka">Total kasus</th>
                <th class="angka">Masih dibina</th>
                <th class="angka">Selesai</th>
        </tr>
        @forelse ($indikator['per_kelas'] as $r)
            <tr>
                <td>{{ $r['kelas']->nama }}</td>
                <td>{{ $r['kelas']->waliKelas?->name ?? 'â€“' }}</td>
                <td class="angka">{{ $r['jumlah'] }}</td>
                <td class="angka" style="{{ $warna($r['persen'], $indikator['murid_aktif']['target']) }}">{{ Rekap::persenTeks($r['persen']) }}</td>
                <td class="angka" style="{{ $warna($r['budaya'], $indikator['budaya_membaca']['target']) }}">{{ Rekap::persenTeks($r['budaya']) }}</td>
                <td class="angka" style="{{ $warna($r['literasi'], $indikator['kemampuan_literasi']['target']) }}">{{ Rekap::persenTeks($r['literasi']) }}</td>
                <td class="angka">{{ $r['kasus_total'] }}</td>
                <td class="angka">{{ $r['kasus_aktif'] }}</td>
                <td class="angka">{{ $r['kasus_selesai'] }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Belum ada kelas pada tahun ajaran aktif.</td></tr>
        @endforelse
        @if (count($indikator['per_kelas']))
            <tfoot>
                <tr>
                    <th scope="row" colspan="2">Total</th>
                    <td class="angka">{{ collect($indikator['per_kelas'])->sum('jumlah') }}</td>
                    <td colspan="3" class="angka">-</td>
                    <td class="angka">{{ collect($indikator['per_kelas'])->sum('kasus_total') }}</td>
                    <td class="angka">{{ collect($indikator['per_kelas'])->sum('kasus_aktif') }}</td>
                    <td class="angka">{{ collect($indikator['per_kelas'])->sum('kasus_selesai') }}</td>
                </tr>
                <tr style="background:#f1f5f9;font-weight:bold">
                    <th scope="row" colspan="2">Rata-rata per kelas</th>
                    <td class="angka">{{ Rekap::persenTeks($rataRataKelas['jumlah'], false) }}</td>
                    <td class="angka" style="{{ $warna($rataRataKelas['persen'], $indikator['murid_aktif']['target']) }}">{{ Rekap::persenTeks($rataRataKelas['persen']) }}</td>
                    <td class="angka" style="{{ $warna($rataRataKelas['budaya'], $indikator['budaya_membaca']['target']) }}">{{ Rekap::persenTeks($rataRataKelas['budaya']) }}</td>
                    <td class="angka" style="{{ $warna($rataRataKelas['literasi'], $indikator['kemampuan_literasi']['target']) }}">{{ Rekap::persenTeks($rataRataKelas['literasi']) }}</td>
                    <td class="angka">{{ Rekap::persenTeks($rataRataKelas['kasus_total'], false) }}</td>
                    <td class="angka">{{ Rekap::persenTeks($rataRataKelas['kasus_aktif'], false) }}</td>
                    <td class="angka">{{ Rekap::persenTeks($rataRataKelas['kasus_selesai'], false) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
    <p class="catatan">Warna hanya pada capaian di bawah target: kuning bila kurang paling banyak {{ RingkasanMonev::SELISIH_RINGAN }} poin, merah bila lebih jauh. Kasus dihitung berdasarkan tanggal kejadian dalam periode terpilih. Masih dibina dan Selesai menunjukkan status terkini, bukan status pada akhir periode. Rata-rata dihitung antar kelas; persentase yang belum tersedia tidak ikut dihitung.</p>

    @if ($indikator['tindak_lanjut']->isNotEmpty())
        <h2>Murid belum berubah setelah 4 minggu pembinaan</h2>
        <ul>
            @foreach ($indikator['tindak_lanjut']->take(8) as $k)
                <li>{{ $k->murid->nama }}, Kelas {{ $k->kelas->nama }}: {{ $k->namaPelanggaran() }} (sejak {{ $k->tanggal->format('d/m/Y') }})</li>
            @endforeach
            @if ($indikator['tindak_lanjut']->count() > 8)
                <li>dan {{ $indikator['tindak_lanjut']->count() - 8 }} murid lainnya (lihat aplikasi).</li>
            @endif
        </ul>
    @endif

    <div class="ttd">
        <div>{{ Pengaturan::ambil('kota_ttd') }}, {{ now()->day }} {{ Rekap::BULAN[now()->month] }} {{ now()->year }}</div>
        <div>Kepala Sekolah,</div>
        <div class="nama">{{ Pengaturan::ambil('nama_kepala_sekolah') ?: '.................................' }}</div>
        @if ($nip = Pengaturan::ambil('nip_kepala_sekolah'))
            <div>NIP. {{ $nip }}</div>
        @endif
    </div>
</body>
</html>
