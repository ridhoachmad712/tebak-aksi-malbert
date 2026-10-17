<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\KaryaLiterasi;
use App\Models\KasusPembinaan;
use App\Models\KegiatanHarian;
use App\Models\Pengaturan;
use App\Services\Rekap;
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
                $batas = (int) Pengaturan::angka('batas_ubah_hari');
                $tercatat = KegiatanHarian::where('kelas_id', $kelas->id)
                    ->whereDate('tanggal', '>=', $hariIni->copy()->subDays($batas))
                    ->pluck('tanggal')->map(fn ($t) => $t->toDateString());
                $data['belumDicatat'] = Rekap::hariKegiatan($hariIni->copy()->subDays($batas), $hariIni->copy()->subDay())
                    ->reject(fn ($t) => $tercatat->contains($t->toDateString()))->values();
            }

            $kasus = KasusPembinaan::with(['murid', 'kelas', 'pemantauan', 'jenisPelanggaran'])
                ->dalamPembinaan()->whereIn('kelas_id', $this->daftarKelas->pluck('id'))->get();
            $data['perluDipantau'] = $kasus->filter->perluDipantau()->values();
            $data['siapDitutup'] = $kasus->filter->siapDitutup()->values();

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
        }

        return view('livewire.guru.beranda', $data);
    }
}
