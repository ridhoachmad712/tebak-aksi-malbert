<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\JenisPelanggaran;
use App\Models\KasusPembinaan;
use App\Models\TindakanPembinaan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guru')]
class PembinaanForm extends Component
{
    use PilihKelas;

    public ?KasusPembinaan $kasus = null;

    public string $muridId = '';

    public string $tanggal = '';

    public string $kondisiAwal = '';

    public string $pikiran = '';

    public string $perasaan = '';

    public string $pelanggaran = '';

    public string $pelanggaranLain = '';

    public string $tingkat = '';

    public string $tindakan = '';

    public string $tindakanLain = '';

    public string $komitmen = '';

    public function mount(?KasusPembinaan $kasus = null): void
    {
        if ($kasus?->exists) {
            abort_unless(auth()->user()->bolehAksesKelas($kasus->kelas_id), 403);
            $this->kasus = $kasus;
            $this->inisialisasiKelas($kasus->kelas_id);
            $this->fill([
                'muridId' => (string) $kasus->murid_id,
                'tanggal' => $kasus->tanggal->toDateString(),
                'kondisiAwal' => (string) $kasus->kondisi_awal,
                'pikiran' => (string) $kasus->pikiran,
                'perasaan' => (string) $kasus->perasaan,
                'pelanggaran' => $kasus->jenis_pelanggaran_id ? (string) $kasus->jenis_pelanggaran_id : 'lain',
                'pelanggaranLain' => (string) $kasus->jenis_pelanggaran_lain,
                'tingkat' => $kasus->tingkat,
                'tindakan' => $kasus->tindakan_pembinaan_id ? (string) $kasus->tindakan_pembinaan_id : 'lain',
                'tindakanLain' => (string) $kasus->tindakan_pembinaan_lain,
                'komitmen' => (string) $kasus->komitmen,
            ]);
        } else {
            $this->inisialisasiKelas(request()->integer('kelas') ?: null);
            $this->muridId = (string) request()->integer('murid') ?: '';
            $this->tanggal = today()->toDateString();
        }
    }

    public function updatedKelasId(): void
    {
        abort_unless($this->daftarKelas->contains('id', $this->kelasId), 403);
        session(['kelas_dipilih' => $this->kelasId]);
        $this->muridId = '';
    }

    public function simpan()
    {
        abort_unless($this->kelas, 403);

        $this->validate([
            'muridId' => ['required', Rule::in($this->kelas->murid()->pluck('murid.id')->map(fn ($id) => (string) $id))],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kondisiAwal' => ['nullable', 'string', 'max:1000'],
            'pikiran' => ['nullable', 'string', 'max:1000'],
            'perasaan' => ['nullable', 'string', 'max:1000'],
            'pelanggaran' => ['required'],
            'pelanggaranLain' => ['required_if:pelanggaran,lain', 'nullable', 'string', 'max:255'],
            'tingkat' => ['required', Rule::in(array_keys(KasusPembinaan::TINGKAT))],
            'tindakan' => ['required'],
            'tindakanLain' => ['required_if:tindakan,lain', 'nullable', 'string', 'max:255'],
            'komitmen' => ['nullable', 'string', 'max:1000'],
        ], [
            'pelanggaranLain.required_if' => 'Tuliskan jenis pelanggarannya.',
            'tindakanLain.required_if' => 'Tuliskan tindakan pembinaannya.',
        ], [
            'muridId' => 'murid', 'kondisiAwal' => 'observation', 'pikiran' => 'thought', 'perasaan' => 'feeling', 'komitmen' => 'desire', 'pelanggaran' => 'jenis pelanggaran', 'tindakan' => 'tindakan pembinaan',
        ]);

        $data = [
            'murid_id' => (int) $this->muridId,
            'kelas_id' => $this->kelasId,
            'tanggal' => $this->tanggal,
            'kondisi_awal' => trim($this->kondisiAwal) ?: null,
            'pikiran' => trim($this->pikiran) ?: null,
            'perasaan' => trim($this->perasaan) ?: null,
            'jenis_pelanggaran_id' => $this->pelanggaran === 'lain' ? null : JenisPelanggaran::findOrFail($this->pelanggaran)->id,
            'jenis_pelanggaran_lain' => $this->pelanggaran === 'lain' ? $this->pelanggaranLain : null,
            'tingkat' => $this->tingkat,
            'tindakan_pembinaan_id' => $this->tindakan === 'lain' ? null : TindakanPembinaan::findOrFail($this->tindakan)->id,
            'tindakan_pembinaan_lain' => $this->tindakan === 'lain' ? $this->tindakanLain : null,
            'komitmen' => trim($this->komitmen) ?: null,
        ];

        if ($this->kasus) {
            $this->kasus->update($data);
            $kasus = $this->kasus;
        } else {
            $kasus = KasusPembinaan::create($data + ['user_id' => auth()->id()]);
        }

        session()->flash('sukses', $this->kasus ? 'Kasus diperbarui' : 'Kasus pembinaan dicatat. Pantau perkembangannya setiap minggu.');

        return $this->redirectRoute('guru.pembinaan.detail', $kasus, navigate: true);
    }

    public function render()
    {
        $daftarPelanggaran = JenisPelanggaran::with('aspekKarakter')->aktif()->urut()->get();

        return view('livewire.guru.pembinaan-form', [
            'daftarMurid' => $this->kelas?->muridAktif()->get() ?? collect(),
            'daftarPelanggaran' => $daftarPelanggaran,
            // Saran Observation (kondisi awal) dari jenis pelanggaran; hanya terisi bila guru mengetuknya
            'saranKondisi' => $daftarPelanggaran->firstWhere('id', (int) $this->pelanggaran)?->nama,
            'daftarTindakan' => TindakanPembinaan::aktif()->urut()->get(),
        ])->title($this->kasus ? 'Ubah Kasus' : 'Catat Pelanggaran');
    }
}
