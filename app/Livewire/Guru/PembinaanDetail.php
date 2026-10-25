<?php

namespace App\Livewire\Guru;

use App\Models\KasusPembinaan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guru')]
#[Title('Detail Kasus Pembinaan')]
class PembinaanDetail extends Component
{
    public KasusPembinaan $kasus;

    /** Minggu yang sedang diisi pada formulir pemantauan. */
    public ?int $minggu = null;

    public string $tingkat = '';

    public string $catatan = '';

    public function mount(KasusPembinaan $kasus): void
    {
        abort_unless(auth()->user()->bolehAksesKelas($kasus->kelas_id), 403);
        $this->kasus = $kasus;
        // Langsung buka minggu yang belum diisi
        if ($minggu = $kasus->mingguBelumDipantau()[0] ?? null) {
            $this->pilihMinggu($minggu);
        }
    }

    public function pilihMinggu(int $minggu): void
    {
        abort_unless($minggu >= 1 && $minggu <= $this->kasus->mingguDimulai(), 422);
        $this->minggu = $minggu;
        $p = $this->kasus->pemantauan->firstWhere('minggu', $minggu);
        $this->tingkat = $p->tingkat ?? '';
        $this->catatan = $p->catatan ?? '';
    }

    /** Sekali ketuk: perkembangan (beserta catatan bila diisi) langsung tersimpan, lalu pindah ke minggu berikutnya yang belum diisi. */
    public function pantau(string $tingkat): void
    {
        abort_unless($this->minggu, 422);
        $this->validate(['catatan' => ['nullable', 'string', 'max:1000']]);
        $this->kasus->catatPemantauan($this->minggu, $tingkat, $this->catatan);
        $this->dispatch('notifikasi', pesan: "Minggu ke-{$this->minggu}: ".KasusPembinaan::PERKEMBANGAN[$tingkat]['label']);

        if ($berikut = $this->kasus->mingguBelumDipantau()[0] ?? null) {
            $this->pilihMinggu($berikut);
        } else {
            $this->tingkat = $tingkat;
        }
    }

    public function tetapkanStatus(string $status): void
    {
        abort_unless(array_key_exists($status, KasusPembinaan::PERKEMBANGAN), 422);
        $this->kasus->update(['status_akhir' => $status]);
        $this->dispatch('notifikasi', pesan: 'Status akhir ditetapkan');
    }

    public function bukaKembali(): void
    {
        $this->kasus->update(['status_akhir' => null]);
    }

    public function hapus()
    {
        $this->kasus->delete();
        session()->flash('sukses', 'Kasus dihapus');

        return $this->redirectRoute('guru.pembinaan', navigate: true);
    }

    public function render()
    {
        $this->kasus->loadMissing(['murid', 'kelas', 'jenisPelanggaran.aspekKarakter', 'tindakanPembinaan', 'pencatat']);

        return view('livewire.guru.pembinaan-detail');
    }
}
