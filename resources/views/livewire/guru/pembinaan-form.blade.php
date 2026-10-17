@use('App\Models\KasusPembinaan')

<div class="mx-auto max-w-2xl space-y-5">
    <div>
        <a href="{{ $kasus ? route('guru.pembinaan.detail', $kasus) : route('guru.pembinaan') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-slate-600 hover:text-slate-900"><x-heroicon-m-chevron-left class="h-4 w-4" /> Kembali</a>
        <h1 class="judul-halaman">{{ $kasus ? 'Ubah Kasus Pembinaan' : 'Catat Pelanggaran' }}</h1>
        <p class="teks-bantu">Setelah dicatat, Beranda akan mengingatkan Anda untuk memantau murid ini setiap minggu selama 4 minggu.</p>
    </div>

    @if ($this->daftarKelas->isEmpty())
        <x-belum-ada-kelas />
    @else
        <form wire:submit="simpan" class="kartu space-y-5 p-5 sm:p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                @if ($this->daftarKelas->count() > 1 && ! $kasus)
                    <div>
                        <label class="label">Kelas</label>
                        <select wire:model.live="kelasId" class="isian">
                            @foreach ($this->daftarKelas as $k)
                                <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div @class(['sm:col-span-2' => $this->daftarKelas->count() === 1 || $kasus])>
                    <label for="murid" class="label">Murid</label>
                    <select id="murid" wire:model="muridId" class="isian">
                        <option value="">— Pilih murid —</option>
                        @foreach ($daftarMurid as $m)
                            <option value="{{ $m->id }}">{{ $m->nama }}</option>
                        @endforeach
                    </select>
                    @error('muridId') <p class="galat">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal" class="label">Tanggal kejadian</label>
                    <input id="tanggal" type="date" wire:model="tanggal" max="{{ today()->toDateString() }}" class="isian">
                    @error('tanggal') <p class="galat">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="kondisi" class="label">Kondisi awal</label>
                <textarea id="kondisi" wire:model="kondisiAwal" rows="2" placeholder="Contoh: Sering mengganggu teman saat membaca" class="isian"></textarea>
                @error('kondisiAwal') <p class="galat">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="pelanggaran" class="label">Jenis pelanggaran</label>
                <select id="pelanggaran" wire:model.live="pelanggaran" class="isian">
                    <option value="">— Pilih jenis pelanggaran —</option>
                    @foreach ($daftarPelanggaran as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}{{ $p->aspekKarakter ? ' ('.$p->aspekKarakter->nama.')' : '' }}</option>
                    @endforeach
                    <option value="lain">Lainnya (tulis sendiri)</option>
                </select>
                @error('pelanggaran') <p class="galat">{{ $message }}</p> @enderror
                @if ($pelanggaran === 'lain')
                    <input wire:model="pelanggaranLain" placeholder="Tuliskan jenis pelanggaran" class="isian mt-2">
                    @error('pelanggaranLain') <p class="galat">{{ $message }}</p> @enderror
                @endif
            </div>

            <div>
                <span class="label" id="label-tingkat">Tingkat pelanggaran</span>
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach (KasusPembinaan::TINGKAT as $nilai => $label)
                        <label class="flex cursor-pointer flex-col rounded-lg border-2 border-garis p-3 has-checked:border-brand-700 has-checked:bg-brand-50">
                            <span class="flex items-center gap-2 font-extrabold">
                                <input type="radio" wire:model="tingkat" value="{{ $nilai }}" class="accent-brand-700"> {{ $label }}
                            </span>
                            <span class="mt-1 text-xs text-slate-600">{{ KasusPembinaan::KETERANGAN_TINGKAT[$nilai] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tingkat') <p class="galat">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tindakan" class="label">Tindakan pembinaan</label>
                <select id="tindakan" wire:model.live="tindakan" class="isian">
                    <option value="">— Pilih tindakan pembinaan —</option>
                    @foreach ($daftarTindakan as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                    @endforeach
                    <option value="lain">Lainnya (tulis sendiri)</option>
                </select>
                @error('tindakan') <p class="galat">{{ $message }}</p> @enderror
                @if ($tindakan === 'lain')
                    <input wire:model="tindakanLain" placeholder="Tuliskan tindakan pembinaan" class="isian mt-2">
                    @error('tindakanLain') <p class="galat">{{ $message }}</p> @enderror
                @endif
            </div>

            <div>
                <label for="komitmen" class="label">Komitmen murid <span class="font-normal text-slate-600">(opsional)</span></label>
                <textarea id="komitmen" wire:model="komitmen" rows="2" placeholder="Contoh: Saya akan membaca dengan tertib dan tidak mengganggu teman" class="isian"></textarea>
            </div>

            <div class="flex justify-end gap-2 border-t border-garis pt-4">
                <a href="{{ $kasus ? route('guru.pembinaan.detail', $kasus) : route('guru.pembinaan') }}" wire:navigate class="tombol-kedua">Batal</a>
                <button class="tombol-utama" wire:loading.attr="disabled">{{ $kasus ? 'Simpan perubahan' : 'Simpan kasus' }}</button>
            </div>
        </form>
    @endif
</div>
