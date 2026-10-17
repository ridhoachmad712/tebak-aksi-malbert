@use('App\Models\KasusPembinaan')

@php
    // Pilihan berbentuk tombol yang tinggal diketuk
    $kelasPilihan = 'flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-garis bg-white px-3 py-2 text-sm font-bold has-checked:border-slate-800 has-checked:bg-slate-50';
@endphp

<div class="mx-auto max-w-2xl space-y-5">
    <div>
        <a href="{{ $kasus ? route('guru.pembinaan.detail', $kasus) : route('guru.pembinaan') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-slate-600 hover:text-slate-900"><x-heroicon-m-chevron-left class="h-4 w-4" /> Kembali</a>
        <h1 class="judul-halaman">{{ $kasus ? 'Ubah Kasus Pembinaan' : 'Catat Pelanggaran' }}</h1>
        <p class="teks-bantu">Cukup ketuk pilihan. Setelah dicatat, Beranda akan mengingatkan Anda memantau murid ini setiap minggu selama 4 minggu.</p>
    </div>

    @if ($this->daftarKelas->isEmpty())
        <x-belum-ada-kelas />
    @else
        <form wire:submit="simpan" class="kartu space-y-6 p-5 sm:p-6">
            @if ($this->daftarKelas->count() > 1 && ! $kasus)
                <div>
                    <label for="kelas" class="label">Kelas</label>
                    <select id="kelas" wire:model.live="kelasId" class="isian">
                        @foreach ($this->daftarKelas as $k)
                            <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Murid: cari dengan mengetik beberapa huruf, lalu ketuk namanya --}}
            <div x-data="{ cari: '' }">
                <span class="label" id="label-murid">Murid</span>
                <input x-model="cari" type="search" placeholder="Ketik 2–3 huruf nama murid" aria-label="Cari nama murid" class="isian mb-2">
                <div class="grid max-h-64 gap-1 overflow-y-auto rounded-lg border border-garis p-2 sm:grid-cols-2" role="radiogroup" aria-labelledby="label-murid">
                    @forelse ($daftarMurid as $m)
                        <label wire:key="murid-{{ $m->id }}" x-show="@js(mb_strtolower($m->nama)).includes(cari.toLowerCase())"
                            class="flex min-h-11 cursor-pointer items-center gap-2 rounded-md px-2 text-sm font-semibold hover:bg-krem has-checked:bg-slate-800 has-checked:text-white">
                            <input type="radio" wire:model="muridId" value="{{ $m->id }}" class="sr-only"> {{ $m->nama }}
                        </label>
                    @empty
                        <p class="p-2 text-sm text-slate-600">Belum ada murid di kelas ini. Admin perlu menambahkan data murid.</p>
                    @endforelse
                </div>
                @error('muridId') <p class="galat">{{ $message }}</p> @enderror
            </div>

            <div class="sm:w-1/2">
                <label for="tanggal" class="label">Tanggal kejadian</label>
                <input id="tanggal" type="date" wire:model="tanggal" max="{{ today()->toDateString() }}" class="isian">
                @error('tanggal') <p class="galat">{{ $message }}</p> @enderror
            </div>

            <fieldset>
                <legend class="label">Jenis pelanggaran</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($daftarPelanggaran as $p)
                        <label class="{{ $kelasPilihan }}">
                            <input type="radio" wire:model.live="pelanggaran" value="{{ $p->id }}" class="accent-slate-800">
                            <span>{{ $p->nama }}@if ($p->aspekKarakter) <span class="block text-xs font-semibold text-slate-500">{{ $p->aspekKarakter->nama }}</span>@endif</span>
                        </label>
                    @endforeach
                    <label class="{{ $kelasPilihan }}">
                        <input type="radio" wire:model.live="pelanggaran" value="lain" class="accent-slate-800"> Lainnya (tulis sendiri)
                    </label>
                </div>
                @error('pelanggaran') <p class="galat">{{ $message }}</p> @enderror
                @if ($pelanggaran === 'lain')
                    <input wire:model="pelanggaranLain" placeholder="Tuliskan jenis pelanggaran" aria-label="Jenis pelanggaran lainnya" class="isian mt-2">
                    @error('pelanggaranLain') <p class="galat">{{ $message }}</p> @enderror
                @endif
            </fieldset>

            <fieldset>
                <legend class="label">Tingkat pelanggaran</legend>
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach (KasusPembinaan::TINGKAT as $nilai => $label)
                        <label class="flex cursor-pointer flex-col rounded-lg border-2 border-garis p-3 has-checked:border-slate-800 has-checked:bg-slate-50">
                            <span class="flex items-center gap-2 font-extrabold">
                                <input type="radio" wire:model="tingkat" value="{{ $nilai }}" class="accent-slate-800"> {{ $label }}
                            </span>
                            <span class="mt-1 text-xs text-slate-600">{{ KasusPembinaan::KETERANGAN_TINGKAT[$nilai] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tingkat') <p class="galat">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset>
                <legend class="label">Tindakan pembinaan</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($daftarTindakan as $t)
                        <label class="{{ $kelasPilihan }}">
                            <input type="radio" wire:model.live="tindakan" value="{{ $t->id }}" class="accent-slate-800"> {{ $t->nama }}
                        </label>
                    @endforeach
                    <label class="{{ $kelasPilihan }}">
                        <input type="radio" wire:model.live="tindakan" value="lain" class="accent-slate-800"> Lainnya (tulis sendiri)
                    </label>
                </div>
                @error('tindakan') <p class="galat">{{ $message }}</p> @enderror
                @if ($tindakan === 'lain')
                    <input wire:model="tindakanLain" placeholder="Tuliskan tindakan pembinaan" aria-label="Tindakan pembinaan lainnya" class="isian mt-2">
                    @error('tindakanLain') <p class="galat">{{ $message }}</p> @enderror
                @endif
            </fieldset>

            <div>
                <label for="kondisi" class="label">Kondisi awal <span class="font-normal text-slate-600">(opsional)</span></label>
                <textarea id="kondisi" wire:model="kondisiAwal" rows="2" placeholder="Contoh: Sering mengganggu teman saat membaca" class="isian"></textarea>
                {{-- Saran hanya dipakai bila guru mengetuknya --}}
                @if ($saranKondisi && blank($kondisiAwal))
                    <button type="button" wire:click="$set('kondisiAwal', @js($saranKondisi))" class="mt-2 inline-flex min-h-10 items-center gap-1 rounded-lg border border-dashed border-slate-400 px-3 text-sm font-semibold text-slate-700 hover:border-slate-700">
                        <x-heroicon-m-plus class="h-4 w-4" /> Pakai: "{{ $saranKondisi }}"
                    </button>
                @endif
                @error('kondisiAwal') <p class="galat">{{ $message }}</p> @enderror
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
