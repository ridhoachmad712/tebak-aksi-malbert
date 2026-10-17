<?php

namespace App\Livewire\Guru;

use App\Livewire\Guru\Concerns\PilihKelas;
use App\Models\JenisKarya;
use App\Models\KaryaLiterasi;
use App\Services\SimpanFoto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.guru')]
class KaryaForm extends Component
{
    use PilihKelas, WithFileUploads;

    public ?KaryaLiterasi $karya = null;

    public string $pemilik = 'murid'; // murid | guru

    public array $muridDipilih = [];

    public string $tanggal = '';

    public string $jenis = '';

    public string $jenisLain = '';

    public string $judul = '';

    public string $bukuSumber = '';

    public array $fotoBaru = [];

    public function mount(?KaryaLiterasi $karya = null): void
    {
        if ($karya?->exists) {
            $boleh = $karya->guru_pemilik_id === auth()->id()
                || ($karya->kelas_id && auth()->user()->bolehAksesKelas($karya->kelas_id));
            abort_unless($boleh, 403);

            $this->karya = $karya;
            $this->inisialisasiKelas($karya->kelas_id);
            $this->fill([
                'pemilik' => $karya->guru_pemilik_id ? 'guru' : 'murid',
                'muridDipilih' => $karya->murid->pluck('id')->map(fn ($id) => (string) $id)->all(),
                'tanggal' => $karya->tanggal->toDateString(),
                'jenis' => $karya->jenis_karya_id ? (string) $karya->jenis_karya_id : 'lain',
                'jenisLain' => (string) $karya->jenis_karya_lain,
                'judul' => $karya->judul,
                'bukuSumber' => (string) $karya->buku_sumber,
            ]);
        } else {
            $this->inisialisasiKelas();
            $this->tanggal = today()->toDateString();
        }
    }

    public function updatedKelasId(): void
    {
        abort_unless($this->daftarKelas->contains('id', $this->kelasId), 403);
        session(['kelas_dipilih' => $this->kelasId]);
        $this->muridDipilih = [];
    }

    public function simpan()
    {
        $idMurid = $this->kelas?->murid()->pluck('murid.id')->map(fn ($id) => (string) $id)->all() ?? [];

        $this->validate([
            'pemilik' => ['required', 'in:murid,guru'],
            'muridDipilih' => ['required_if:pemilik,murid', 'array'],
            'muridDipilih.*' => [Rule::in($idMurid)],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jenis' => ['required'],
            'jenisLain' => ['required_if:jenis,lain', 'nullable', 'string', 'max:255'],
            'judul' => ['required', 'string', 'max:255'],
            'bukuSumber' => ['nullable', 'string', 'max:255'],
            'fotoBaru.*' => ['image', 'max:'.SimpanFoto::MAKS_KB],
        ], [
            'muridDipilih.required_if' => 'Pilih minimal satu murid pemilik karya.',
            'jenisLain.required_if' => 'Tuliskan jenis karyanya.',
        ], ['jenis' => 'jenis karya', 'bukuSumber' => 'buku sumber', 'fotoBaru.*' => 'foto']);

        $data = [
            'kelas_id' => $this->kelasId,
            'tanggal' => $this->tanggal,
            'jenis_karya_id' => $this->jenis === 'lain' ? null : JenisKarya::findOrFail($this->jenis)->id,
            'jenis_karya_lain' => $this->jenis === 'lain' ? $this->jenisLain : null,
            'judul' => $this->judul,
            'buku_sumber' => $this->bukuSumber ?: null,
            'guru_pemilik_id' => $this->pemilik === 'guru' ? ($this->karya?->guru_pemilik_id ?? auth()->id()) : null,
        ];

        if ($this->karya) {
            $this->karya->update($data);
            $karya = $this->karya;
        } else {
            $karya = KaryaLiterasi::create($data + ['user_id' => auth()->id()]);
        }

        $karya->murid()->sync($this->pemilik === 'murid' ? $this->muridDipilih : []);

        foreach ($this->fotoBaru as $file) {
            $karya->foto()->create(['path' => SimpanFoto::simpan($file, 'karya'), 'user_id' => auth()->id()]);
        }

        session()->flash('sukses', $this->karya ? 'Karya diperbarui' : 'Karya literasi disimpan 🎉');

        return $this->redirectRoute('guru.karya', navigate: true);
    }

    public function hapusFoto(int $id): void
    {
        $this->karya?->foto()->whereKey($id)->first()?->delete();
        $this->karya?->load('foto');
    }

    public function hapus()
    {
        abort_unless($this->karya, 404);
        $this->karya->foto->each->delete();
        $this->karya->delete();
        session()->flash('sukses', 'Karya dihapus');

        return $this->redirectRoute('guru.karya', navigate: true);
    }

    public function render()
    {
        $jenjang = $this->kelas?->jenjang();

        return view('livewire.guru.karya-form', [
            'daftarMurid' => $this->kelas?->muridAktif()->get() ?? collect(),
            'daftarJenis' => JenisKarya::aktif()->urut()->get()
                ->sortBy(fn ($j) => in_array($j->jenjang, [$jenjang, 'semua'], true) ? 0 : 1)->values(),
            'jenjang' => $jenjang,
        ])->title($this->karya ? 'Ubah Karya' : 'Tambah Karya');
    }
}
