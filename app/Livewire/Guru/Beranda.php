<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Services\Rekap;
use App\Services\RingkasanMonev;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Beranda')]
class Beranda extends Component
{
    use PilihKelas;

    public function mount(): void
    {
        $this->inisialisasiKelas();
    }

    /** Pemantauan cepat dari Beranda: mengisi minggu paling awal yang belum dipantau. */
    public function pantau(int $kasusId, string $tingkat): void
    {
        $kasus = KasusPembinaan::with('pemantauan', 'murid')->dalamPembinaan()->findOrFail($kasusId);
        abort_unless(auth()->user()->bolehAksesKelas($kasus->kelas_id), 403);
        abort_if($kasus->terkunci(), 403);
        $minggu = $kasus->mingguBelumDipantau()[0] ?? null;
        abort_unless($minggu, 422);

        $kasus->catatPemantauan($minggu, $tingkat);
        $this->dispatch('notifikasi', pesan: $kasus->murid->nama.', minggu ke-'.$minggu.': '.KasusPembinaan::PERKEMBANGAN[$tingkat]['label']);
    }

    public function render()
    {
        $kelas = $this->kelas;
        $user = auth()->user();
        $data = ['kelas' => $kelas];

        if ($kelas) {
            $hariIni = today();
            $data['hariKegiatan'] = Rekap::hariKegiatanIni($hariIni);
            $data['kegiatanHariIni'] = KegiatanHarian::where('kelas_id', $kelas->id)->whereDate('tanggal', $hariIni)
                ->withCount(['catatanMurid as hadir_count' => fn ($q) => $q->where('hadir', true)])->first();
            $data['jumlahMurid'] = $kelas->muridAktif()->count();
            $data['hariTerakhir'] = Rekap::hariKegiatan($hariIni->copy()->subDays(14), $hariIni->copy()->subDay())->last();

            // Pengingat hari kosong hanya untuk wali kelas (atau admin); guru mapel tidak bertanggung jawab atas catatan harian kelas
            $data['belumDicatat'] = collect();
            if ($user->isAdmin() || $kelas->wali_kelas_id === $user->id) {
                $data['belumDicatat'] = Rekap::hariBelumDicatat($kelas);
            }

            $kasus = KasusPembinaan::with(['murid', 'kelas', 'pemantauan', 'jenisPelanggaran'])
                ->dalamPembinaan()->whereIn('kelas_id', $this->daftarKelas->pluck('id'))->get();
            // Kasus di bulan yang dikunci admin tidak lagi diingatkan karena tidak bisa diisi
            $bisaDiisi = $kasus->reject->terkunci();
            $data['perluDipantau'] = $bisaDiisi->filter->perluDipantau()->values();
            $data['siapDitutup'] = $bisaDiisi->filter->siapDitutup()->values();

            [$dari, $sampai] = Rekap::periodeBulan($hariIni->year, $hariIni->month);
            $rekap = Rekap::rekapKelas($kelas, $dari, $sampai);
            $data['ringkasan'] = [
                'bulan' => Rekap::labelBulan($hariIni),
                'hari' => $rekap['n'],
                'hadir' => Rekap::rerata($rekap['murid']->pluck('hadir_persen')->all()),
                'literasi' => Rekap::rerata($rekap['murid']->pluck('literasi_persen')->all()),
                'kasus' => $kasus->where('kelas_id', $kelas->id)->count(),
                'karya' => KaryaLiterasi::where('kelas_id', $kelas->id)->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->count(),
            ];
            // Murid baru dinilai bila bulan ini sudah ada catatan; tanpa catatan semua akan tampak 0%
            $data['adaCatatan'] = KegiatanHarian::where('kelas_id', $kelas->id)->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->exists();
            $data['muridPerhatian'] = $data['adaCatatan'] ? RingkasanMonev::muridPerluPerhatian($rekap) : collect();
        }

        return view('livewire.guru.beranda', $data);
    }
}
