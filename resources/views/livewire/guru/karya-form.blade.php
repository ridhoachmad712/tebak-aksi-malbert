@use('App\Models\JenisKarya')

<div class="mx-auto max-w-2xl space-y-5">
    <div>
        <a href="{{ route('guru.karya') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-slate-600 hover:text-slate-900"><x-heroicon-m-chevron-left class="h-4 w-4" /> Karya Literasi</a>
        <h1 class="judul-halaman">{{ $karya ? 'Ubah karya' : 'Tambah karya literasi' }}</h1>
    </div>

    <form wire:submit="simpan" class="kartu space-y-5 p-5 sm:p-6">
        <div>
            <span class="label">Pemilik karya</span>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-garis p-3 font-bold has-checked:border-slate-800 has-checked:bg-slate-50">
                    <input type="radio" wire:model.live="pemilik" value="murid" class="accent-slate-800"> Murid
                </label>
                <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-garis p-3 font-bold has-checked:border-slate-800 has-checked:bg-slate-50">
                    <input type="radio" wire:model.live="pemilik" value="guru" class="accent-slate-800"> Saya sendiri (guru)
                </label>
            </div>
        </div>

        @if ($this->daftarKelas->count() > 1)
            <div>
                <label class="label">Kelas</label>
                <select wire:model.live="kelasId" class="isian">
                    @foreach ($this->daftarKelas as $k)
                        <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($pemilik === 'murid')
            <div x-data="{ cari: '' }">
                <span class="label">Murid pemilik karya <span class="font-normal text-slate-600">(pilih lebih dari satu untuk karya kelompok)</span></span>
                <input x-model="cari" type="search" placeholder="Cari nama murid" aria-label="Cari nama murid" class="isian mb-2">
                <div class="grid max-h-72 gap-1 overflow-y-auto rounded-lg border border-garis p-2 sm:grid-cols-2">
                    @forelse ($daftarMurid as $m)
                        <label x-show="@js(mb_strtolower($m->nama)).includes(cari.toLowerCase())" wire:key="m-{{ $m->id }}"
                            class="flex min-h-11 cursor-pointer items-center gap-2 rounded-md px-2 text-sm font-semibold hover:bg-krem has-checked:bg-slate-100 has-checked:text-slate-900">
                            <input type="checkbox" wire:model="muridDipilih" value="{{ $m->id }}" class="h-4 w-4 accent-slate-800"> {{ $m->nama }}
                        </label>
                    @empty
                        <p class="p-2 text-sm text-slate-600">Belum ada murid di kelas ini. Admin perlu menambahkan data murid.</p>
                    @endforelse
                </div>
                @error('muridDipilih') <p class="galat">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Jenis karya berupa pilihan yang tinggal diketuk; jenis untuk jenjang kelas ini tampil lebih dulu --}}
        <fieldset>
            <legend class="label">Jenis karya</legend>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($daftarJenis as $j)
                    <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-garis bg-white px-3 py-2 text-sm font-bold has-checked:border-slate-800 has-checked:bg-slate-50">
                        <input type="radio" wire:model.live="jenis" value="{{ $j->id }}" class="accent-slate-800">
                        <span>{{ $j->nama }}@if ($j->jenjang !== 'semua' && $j->jenjang !== $jenjang)<span class="block text-xs font-semibold text-slate-500">{{ $j->jenjang === 'rendah' ? 'kelas rendah' : 'kelas tinggi' }}</span>@endif</span>
                    </label>
                @endforeach
                <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-garis bg-white px-3 py-2 text-sm font-bold has-checked:border-slate-800 has-checked:bg-slate-50">
                    <input type="radio" wire:model.live="jenis" value="lain" class="accent-slate-800"> Lainnya
                </label>
            </div>
            @error('jenis') <p class="galat">{{ $message }}</p> @enderror
            @if ($jenis === 'lain')
                <input wire:model="jenisLain" placeholder="Tuliskan jenis karya" aria-label="Jenis karya lainnya" class="isian mt-2">
                @error('jenisLain') <p class="galat">{{ $message }}</p> @enderror
            @endif
        </fieldset>

        <div class="sm:w-1/2">
            <label for="tanggal" class="label">Tanggal</label>
            <input id="tanggal" type="date" wire:model="tanggal" max="{{ today()->toDateString() }}" class="isian">
            @error('tanggal') <p class="galat">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="judul" class="label">Judul karya</label>
            <input id="judul" wire:model="judul" placeholder="Contoh: Petualangan Si Kancil" class="isian">
            @error('judul') <p class="galat">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="buku" class="label">Judul buku sumber <span class="font-normal text-slate-600">(opsional)</span></label>
            <input id="buku" wire:model="bukuSumber" class="isian">
        </div>

        <div>
            <span class="label">Foto karya <span class="font-normal text-slate-600">(wajib, minimal 1 foto)</span></span>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                @foreach ($karya?->foto ?? [] as $f)
                    <div wire:key="f-{{ $f->id }}" class="relative aspect-square overflow-hidden rounded-lg bg-slate-100">
                        <img src="{{ $f->url() }}" alt="Foto karya" class="h-full w-full object-cover">
                        <button type="button" wire:click="hapusFoto({{ $f->id }})" wire:confirm="Hapus foto ini?" class="absolute right-1 top-1 grid h-9 w-9 place-items-center rounded-md bg-white text-rose-700 shadow" aria-label="Hapus foto"><x-heroicon-o-trash class="h-4 w-4" /></button>
                    </div>
                @endforeach
                @foreach ($fotoBaru as $i => $f)
                    @if (method_exists($f, 'isPreviewable') && $f->isPreviewable())
                        <img wire:key="baru-{{ $i }}" src="{{ $f->temporaryUrl() }}" alt="Foto baru" class="aspect-square rounded-lg object-cover ring-2 ring-brand-300">
                    @endif
                @endforeach
                <label class="grid aspect-square cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-300 text-center text-xs font-bold text-slate-600 hover:border-slate-500">
                    <span wire:loading.remove wire:target="fotoBaru"><x-heroicon-o-camera class="mx-auto h-7 w-7" />Pilih foto</span>
                    <span wire:loading wire:target="fotoBaru">Mengunggah…</span>
                    <input type="file" wire:model="fotoBaru" accept="image/*" multiple class="sr-only">
                </label>
            </div>
            @error('fotoBaru') <p class="galat">{{ $message }}</p> @enderror
            @error('fotoBaru.*') <p class="galat">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-wrap justify-between gap-2 border-t border-garis pt-4">
            <div>
                @if ($karya)
                    <button type="button" wire:click="hapus" wire:confirm="Hapus karya ini?" class="tombol-bahaya"><x-heroicon-o-trash class="h-4 w-4" /> Hapus</button>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('guru.karya') }}" wire:navigate class="tombol-kedua">Batal</a>
                <button class="tombol-utama" wire:loading.attr="disabled">Simpan karya</button>
            </div>
        </div>
    </form>
</div>
