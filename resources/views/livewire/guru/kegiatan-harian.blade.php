@use('App\Services\Rekap')

<div class="space-y-5">
    {{-- Judul, kelas, dan tanggal --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('guru.beranda') }}" wire:navigate class="text-sm font-bold text-slate-500 hover:text-slate-700">← Beranda</a>
            <h1 class="judul-halaman">Kegiatan Harian</h1>
        </div>
        <x-pilih-kelas :daftar="$this->daftarKelas" />
    </div>

    @if (! $this->kelas)
        <x-belum-ada-kelas />
    @else
        <div class="kartu flex flex-wrap items-center justify-between gap-3 p-3">
            <button wire:click="pindahHari(-1)" class="tombol-kedua px-3" aria-label="Hari sebelumnya"><x-heroicon-m-chevron-left class="h-5 w-5" /></button>
            <div class="flex flex-1 flex-col items-center text-center">
                <span class="text-lg font-extrabold">{{ Rekap::tanggalPanjang($tgl) }}</span>
                <label class="mt-0.5 flex items-center gap-1 text-xs font-bold text-brand-600">
                    <x-heroicon-o-calendar class="h-4 w-4" /> Pilih tanggal lain
                    <input type="date" wire:model.live="tanggal" max="{{ today()->toDateString() }}" class="w-0 opacity-0 sm:w-auto sm:rounded-lg sm:border sm:border-slate-200 sm:px-2 sm:py-0.5 sm:text-slate-700 sm:opacity-100">
                </label>
            </div>
            <button wire:click="pindahHari(1)" @disabled($tgl->isToday()) class="tombol-kedua px-3" aria-label="Hari berikutnya"><x-heroicon-m-chevron-right class="h-5 w-5" /></button>
        </div>

        @if (! $this->hariKegiatan)
            <div class="kartu p-8 text-center">
                <div class="text-5xl">🌤️</div>
                <p class="mt-2 text-lg font-extrabold">Tanggal ini bukan hari kegiatan</p>
                <p class="text-slate-600">Kegiatan berlangsung Senin–Jumat, kecuali hari libur yang ditetapkan sekolah.</p>
            </div>
        @elseif ($this->murid->isEmpty())
            <div class="kartu p-8 text-center">
                <div class="text-5xl">👧🧒</div>
                <p class="mt-2 text-lg font-extrabold">Belum ada murid di kelas ini</p>
                <p class="text-slate-600">Admin sekolah perlu menambahkan data murid terlebih dahulu.</p>
            </div>
        @else
            @unless ($this->bolehUbah)
                <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-900">
                    <x-heroicon-o-lock-closed class="h-5 w-5 shrink-0" />
                    Catatan ini hanya bisa dilihat. Guru dapat mengubah catatan paling lama {{ (int) \App\Models\Pengaturan::angka('batas_ubah_hari') }} hari ke belakang. Hubungi admin jika perlu koreksi.
                </div>
            @endunless

            {{-- Tabel centang murid (state di Alpine, disimpan otomatis) --}}
            <section wire:key="tabel-{{ $kelasId }}-{{ $tanggal }}"
                x-data="{
                    c: @js((object) $centang),
                    boleh: @js($this->bolehUbah),
                    kolom: @js(array_keys($indikator)),
                    antrean: {},
                    status: 'siap',
                    jam: '',
                    pewaktu: null,
                    lainTercentang(id) { return this.kolom.slice(1).some(k => this.c[id][k]) },
                    bisa(id, k) { return this.boleh && (k === 'hadir' || this.c[id].hadir) },
                    ubah(id, k) {
                        if (! this.bisa(id, k)) return;
                        if (k === 'hadir' && this.c[id].hadir && this.lainTercentang(id)) {
                            if (! confirm('Murid ini sudah punya centang lain. Hapus juga centang lainnya?')) return;
                            this.kolom.forEach(x => this.c[id][x] = false);
                        } else {
                            this.c[id][k] = ! this.c[id][k];
                        }
                        this.antre(id);
                    },
                    layak(k) { return Object.keys(this.c).filter(id => k === 'hadir' || this.c[id].hadir) },
                    semuaTercentang(k) { const l = this.layak(k); return l.length > 0 && l.every(id => this.c[id][k]) },
                    semua(k) {
                        if (! this.boleh) return;
                        const l = this.layak(k);
                        if (this.semuaTercentang(k)) {
                            if (k === 'hadir' && l.some(id => this.lainTercentang(id)) && ! confirm('Kosongkan semua centang Hadir? Centang lain pada murid tersebut juga akan dihapus.')) return;
                            l.forEach(id => { if (k === 'hadir') this.kolom.forEach(x => this.c[id][x] = false); else this.c[id][k] = false; this.antre(id) });
                        } else {
                            l.forEach(id => { if (! this.c[id][k]) { this.c[id][k] = true; this.antre(id) } });
                        }
                    },
                    jumlah(k) { return Object.values(this.c).filter(r => r[k]).length },
                    antre(id) {
                        this.antrean[id] = { ...this.c[id] };
                        this.status = 'menunggu';
                        clearTimeout(this.pewaktu);
                        this.pewaktu = setTimeout(() => this.kirim(), 600);
                    },
                    async kirim() {
                        const data = this.antrean;
                        this.antrean = {};
                        if (! Object.keys(data).length) return;
                        this.status = 'menyimpan';
                        try {
                            const hasil = await $wire.simpan(data);
                            this.jam = hasil.jam;
                            this.status = Object.keys(this.antrean).length ? 'menunggu' : 'tersimpan';
                        } catch (e) {
                            this.antrean = { ...data, ...this.antrean };
                            this.status = 'gagal';
                        }
                    },
                }"
                x-on:beforeunload.window="if (status === 'menunggu' || status === 'menyimpan' || status === 'gagal') { $event.preventDefault(); $event.returnValue = '' }"
                class="kartu overflow-hidden">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-orange-100 px-4 py-3">
                    <div>
                        <h2 class="font-extrabold">Centang setiap murid</h2>
                        <p class="text-xs text-slate-500">Kolom lain bisa dicentang setelah <b>Hadir</b> dicentang. Ketuk ⓘ untuk arti setiap kolom.</p>
                    </div>
                    <div class="text-sm font-bold" aria-live="polite">
                        <span x-show="status === 'siap'" class="text-slate-400">Perubahan tersimpan otomatis</span>
                        <span x-cloak x-show="status === 'menunggu' || status === 'menyimpan'" class="text-amber-600">⏳ Menyimpan…</span>
                        <span x-cloak x-show="status === 'tersimpan'" class="text-toska-700">✓ Tersimpan <span x-text="jam"></span></span>
                        <span x-cloak x-show="status === 'gagal'" class="text-rose-600">⚠️ Gagal menyimpan. <button x-on:click="kirim()" class="underline">Coba lagi</button></span>
                    </div>
                </div>

                {{-- Tampilan laptop: tabel --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-orange-50/60 text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-4 pt-3 text-left" rowspan="2">No</th>
                                <th class="px-2 pt-3 text-left" rowspan="2">Nama Murid</th>
                                <th colspan="3" class="border-l border-orange-100 px-2 pt-3 text-center text-brand-700">Budaya Membaca</th>
                                <th colspan="3" class="border-l border-orange-100 px-2 pt-3 text-center text-toska-700">Kemampuan Literasi</th>
                            </tr>
                            <tr class="bg-orange-50/60">
                                @foreach ($indikator as $k => $info)
                                    <th @class(['px-2 pb-3 pt-1 text-center align-top', 'border-l border-orange-100' => in_array($k, ['hadir', 'memahami'])])>
                                        <div x-data="{ info: false }" class="relative flex flex-col items-center gap-1.5">
                                            <span class="flex items-center gap-1 text-[13px] font-extrabold normal-case text-slate-700">
                                                {{ $info['label'] }}
                                                <button type="button" x-on:click="info = !info" x-on:click.outside="info = false" class="text-slate-400 hover:text-brand-600" aria-label="Arti kolom {{ $info['label'] }}">ⓘ</button>
                                            </span>
                                            <div x-cloak x-show="info" class="absolute top-6 z-20 w-56 rounded-xl bg-slate-800 p-3 text-left text-xs font-semibold normal-case text-white shadow-xl">✓ = {{ $info['arti'] }}</div>
                                            <button type="button" x-show="boleh" x-on:click="semua('{{ $k }}')"
                                                class="rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-bold normal-case text-slate-600 hover:border-brand-300 hover:text-brand-700"
                                                x-text="semuaTercentang('{{ $k }}') ? 'Kosongkan' : 'Centang semua'"></button>
                                            <span class="text-[11px] font-bold normal-case text-slate-400"><span x-text="jumlah('{{ $k }}')"></span>/{{ $this->murid->count() }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-50">
                            @foreach ($this->murid as $i => $m)
                                <tr class="hover:bg-orange-50/40" :class="c[{{ $m->id }}].hadir ? '' : 'text-slate-500'">
                                    <td class="px-4 py-2 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-2 py-2 font-bold">{{ $m->nama }}</td>
                                    @foreach ($indikator as $k => $info)
                                        <td @class(['px-2 py-1.5 text-center', 'border-l border-orange-50' => in_array($k, ['hadir', 'memahami'])])>
                                            <button type="button" x-on:click="ubah({{ $m->id }}, '{{ $k }}')"
                                                :disabled="! bisa({{ $m->id }}, '{{ $k }}')"
                                                :aria-pressed="c[{{ $m->id }}].{{ $k }}"
                                                aria-label="{{ $info['label'] }} {{ $m->nama }}"
                                                :class="c[{{ $m->id }}].{{ $k }}
                                                    ? '{{ $k === 'hadir' || in_array($k, ['konsisten', 'jurnal']) ? 'bg-brand-500 border-brand-500' : 'bg-toska-600 border-toska-600' }} text-white'
                                                    : 'bg-white border-slate-300 text-transparent'"
                                                class="mx-auto grid h-9 w-9 place-items-center rounded-xl border-2 transition enabled:hover:border-brand-400 disabled:cursor-not-allowed disabled:opacity-30">
                                                <x-heroicon-s-check class="h-5 w-5" />
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tampilan HP: kartu per murid --}}
                <div class="md:hidden">
                    <div x-show="boleh" class="flex gap-2 overflow-x-auto border-b border-orange-100 px-4 py-3">
                        <span class="shrink-0 self-center text-xs font-bold text-slate-500">Centang semua:</span>
                        @foreach ($indikator as $k => $info)
                            <button type="button" x-on:click="semua('{{ $k }}')"
                                :class="semuaTercentang('{{ $k }}') ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-700 border-slate-200'"
                                class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-bold">{{ $info['label'] }}</button>
                        @endforeach
                    </div>
                    <ul class="divide-y divide-orange-50">
                        @foreach ($this->murid as $i => $m)
                            <li class="px-4 py-3">
                                <p class="mb-2 font-extrabold"><span class="mr-1 text-slate-400">{{ $i + 1 }}.</span> {{ $m->nama }}</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ($indikator as $k => $info)
                                        <button type="button" x-on:click="ubah({{ $m->id }}, '{{ $k }}')"
                                            :disabled="! bisa({{ $m->id }}, '{{ $k }}')"
                                            :aria-pressed="c[{{ $m->id }}].{{ $k }}"
                                            :class="c[{{ $m->id }}].{{ $k }}
                                                ? '{{ in_array($k, ['hadir', 'konsisten', 'jurnal']) ? 'bg-brand-500 border-brand-500' : 'bg-toska-600 border-toska-600' }} text-white'
                                                : 'bg-white border-slate-200 text-slate-600'"
                                            class="flex min-h-11 items-center justify-center gap-0.5 rounded-xl border-2 px-0.5 text-[11px] font-bold leading-tight transition disabled:opacity-30 min-[400px]:text-xs">
                                            <span x-show="c[{{ $m->id }}].{{ $k }}">✓</span>
                                            {{ $k === 'menyampaikan' ? 'Menyampaikan' : $info['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Isian opsional --}}
            <section class="grid gap-5 lg:grid-cols-2">
                <div class="kartu p-5">
                    <h2 class="font-extrabold">Jenis kegiatan hari ini <span class="text-sm font-semibold text-slate-400">(opsional)</span></h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($jenisKegiatan as $j)
                            @php $dipilih = in_array((string) $j->id, $jenisDipilih, true); @endphp
                            <button type="button" wire:click="toggleJenis({{ $j->id }})" @disabled(! $this->bolehUbah)
                                @class(['rounded-full border-2 px-3.5 py-1.5 text-sm font-bold transition disabled:opacity-60',
                                    'border-toska-600 bg-toska-600 text-white' => $dipilih,
                                    'border-slate-200 bg-white text-slate-600 hover:border-toska-300' => ! $dipilih])>
                                {{ $dipilih ? '✓ ' : '' }}{{ $j->nama }}
                            </button>
                        @endforeach
                    </div>

                    <label for="catatan" class="label mt-5">Catatan singkat <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="catatan" wire:model="catatan" rows="2" @disabled(! $this->bolehUbah) placeholder="Contoh: Membaca buku cerita rakyat bersama" class="isian"></textarea>
                    @if ($this->bolehUbah)
                        <button wire:click="simpanCatatan" class="tombol-kedua mt-2">Simpan catatan</button>
                    @endif
                </div>

                <div class="kartu p-5">
                    <h2 class="font-extrabold">Foto dokumentasi <span class="text-sm font-semibold text-slate-400">(opsional)</span></h2>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($this->kegiatan?->foto ?? [] as $f)
                            <div wire:key="foto-{{ $f->id }}" class="group relative aspect-square overflow-hidden rounded-xl bg-slate-100">
                                <img src="{{ $f->url() }}" alt="Dokumentasi kegiatan" class="h-full w-full object-cover" loading="lazy">
                                @if ($this->bolehUbah)
                                    <button wire:click="hapusFoto({{ $f->id }})" wire:confirm="Hapus foto ini?" class="absolute right-1 top-1 rounded-lg bg-white/90 p-1 text-rose-600 shadow" aria-label="Hapus foto">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        @if ($this->bolehUbah)
                            <label class="grid aspect-square cursor-pointer place-items-center rounded-xl border-2 border-dashed border-slate-300 text-center text-xs font-bold text-slate-500 hover:border-brand-400 hover:text-brand-600">
                                <span wire:loading.remove wire:target="fotoBaru"><x-heroicon-o-camera class="mx-auto h-7 w-7" />Tambah foto</span>
                                <span wire:loading wire:target="fotoBaru">Mengunggah…</span>
                                <input type="file" wire:model="fotoBaru" accept="image/*" multiple class="hidden">
                            </label>
                        @endif
                    </div>
                    @error('fotoBaru.*') <p class="galat">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-slate-500">Foto dapat dipilih admin untuk ditampilkan di Galeri publik.</p>
                </div>
            </section>

            {{-- Jejak pengisian --}}
            @if ($this->kegiatan?->riwayat->isNotEmpty())
                <section class="kartu p-5">
                    <h2 class="font-extrabold">Riwayat pengisian</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        @foreach ($this->kegiatan->riwayat->take(10) as $r)
                            <li>• {{ $r->aksi === 'membuat' ? 'Diisi' : 'Diubah' }} oleh <b>{{ $r->user?->name ?? '-' }}</b> · {{ $r->created_at->format('d/m/Y H.i') }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    @endif
</div>
