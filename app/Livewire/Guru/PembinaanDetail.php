<?php

namespace App\Livewire\Guru;

use App\Models\KasusPembinaan;
use Illuminate\Validation\Rule;
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
        $this->minggu = $kasus->mingguBelumDipantau()[0] ?? null;
    }

    public function pilihMinggu(int $minggu): void
    {
        abort_unless($minggu >= 1 && $minggu <= $this->kasus->mingguDimulai(), 422);
        $this->minggu = $minggu;
        $p = $this->kasus->pemantauan->firstWhere('minggu', $minggu);
        $this->tingkat = $p->tingkat ?? '';
        $this->catatan = $p->catatan ?? '';
    }

    public function simpanPemantauan(): void
    {
        abort_unless($this->minggu && $this->minggu <= $this->kasus->mingguDimulai(), 422);
        $this->validate([
            'tingkat' => ['required', Rule::in(array_keys(KasusPembinaan::PERKEMBANGAN))],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], ['tingkat.required' => 'Pilih salah satu tingkat perkembangan.']);

        $this->kasus->pemantauan()->updateOrCreate(
            ['minggu' => $this->minggu],
            ['tingkat' => $this->tingkat, 'catatan' => $this->catatan ?: null, 'user_id' => auth()->id()],
        );

        $this->kasus->load('pemantauan');
        $this->dispatch('notifikasi', pesan: "Pemantauan minggu ke-{$this->minggu} disimpan");
        $this->reset('tingkat', 'catatan');
        $this->minggu = $this->kasus->mingguBelumDipantau()[0] ?? null;
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
        $this->kasus->loadMissing(['murid', 'kelas', 'jenisPelanggaran.aspekKarakter', 'tindakanPembinaan', 'pemantauan.user', 'pencatat']);

        return view('livewire.guru.pembinaan-detail');
    }
}
