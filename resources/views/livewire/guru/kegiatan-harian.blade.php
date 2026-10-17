@use('App\Services\Rekap')

@php
    $warnaCentang = fn (string $k) => in_array($k, ['hadir', 'konsisten', 'jurnal']) ? 'bg-brand-700 border-brand-700' : 'bg-toska-700 border-toska-700';
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="judul-halaman">Kegiatan Harian</h1>
        <x-pilih-kelas :daftar="$this->daftarKelas" />
    </div>

    @if (! $this->kelas)
        <x-belum-ada-kelas />
    @else
        <div class="kartu flex items-center justify-between gap-2 p-2">
            <button wire:click="pindahHari(-1)" class="tombol-kedua px-3" aria-label="Hari kegiatan sebelumnya"><x-heroicon-m-chevron-left class="h-5 w-5" /></button>
            <div x-data class="flex flex-1 flex-col items-center text-center">
                <span class="text-lg font-extrabold">{{ Rekap::tanggalPanjang($tgl) }}</span>
                <button type="button" x-on:click="try { $refs.tgl.showPicker() } catch (e) { $refs.tgl.focus(); $refs.tgl.click() }"
                    class="mt-0.5 inline-flex min-h-8 items-center gap-1 text-sm font-bold text-brand-700 hover:underline">
                    <x-heroicon-o-calendar class="h-4 w-4" /> Ganti tanggal
                </button>
                <input x-ref="tgl" type="date" wire:model.live="tanggal" max="{{ today()->toDateString() }}" class="sr-only" tabindex="-1" aria-label="Tanggal kegiatan">
            </div>
            <button wire:click="pindahHari(1)" @disabled($tgl->isToday()) class="tombol-kedua px-3" aria-label="Hari kegiatan berikutnya"><x-heroicon-m-chevron-right class="h-5 w-5" /></button>
        </div>

        @if (! $this->hariKegiatan)
            <div class="kartu p-6">
                <p class="text-lg font-extrabold">Tanggal ini bukan hari kegiatan</p>
                <p class="teks-bantu mt-1">Kegiatan berlangsung Senin sampai Jumat, kecuali hari libur yang ditetapkan sekolah. Pilih tanggal lain dengan tombol panah.</p>
            </div>
        @elseif ($this->murid->isEmpty())
            <div class="kartu p-6">
                <p class="text-lg font-extrabold">Belum ada murid di kelas ini</p>
                <p class="teks-bantu mt-1">Admin sekolah perlu menambahkan data murid Kelas {{ $this->kelas->nama }} terlebih dahulu.</p>
            </div>
        @else
            @unless ($this->bolehUbah)
                <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-900">
                    <x-heroicon-o-lock-closed class="mt-0.5 h-5 w-5 shrink-0" />
                    <p>Catatan ini hanya bisa dilihat. Guru dapat mengubah catatan paling lama {{ (int) \App\Models\Pengaturan::angka('batas_ubah_hari') }} hari ke belakang. Hubungi admin jika perlu koreksi.</p>
                </div>
            @endunless

            {{-- Tabel centang; status disimpan otomatis dari Alpine --}}
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
                x-on:beforeunload.window="if (['menunggu', 'menyimpan', 'gagal'].includes(status)) { $event.preventDefault(); $event.returnValue = '' }"
                class="kartu">

                <div class="border-b border-garis px-4 py-3">
                    <h2 class="font-extrabold">Centang setiap murid</h2>
                    <p class="teks-bantu">Kolom lain dapat dicentang setelah <b>Hadir</b> dicentang.<span class="hidden md:inline"> Klik nama kolom untuk melihat artinya.</span></p>
                </div>

                {{-- Tampilan laptop: tabel --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-sm">
                        <thead class="tabel-kepala">
                            <tr>
                                <th class="px-4 pt-3" rowspan="2">No</th>
                                <th class="px-2 pt-3" rowspan="2">Nama murid</th>
                                <th colspan="3" class="border-l border-garis px-2 pt-3 text-center text-brand-800">Budaya Membaca</th>
                                <th colspan="3" class="border-l border-garis px-2 pt-3 text-center text-toska-800">Kemampuan Literasi</th>
                            </tr>
                            <tr>
                                @foreach ($indikator as $k => $info)
                                    <th @class(['px-2 pb-3 pt-1 text-center align-top', 'border-l border-garis' => in_array($k, ['hadir', 'memahami'])])>
                                        <div x-data="{ info: false }" class="relative flex flex-col items-center gap-1.5">
                                            <button type="button" x-on:click="info = !info" x-on:click.outside="info = false"
                                                class="text-[13px] font-extrabold text-slate-800 underline decoration-dotted underline-offset-4" :aria-expanded="info">
                                                {{ $info['label'] }}
                                            </button>
                                            <div x-cloak x-show="info" class="absolute top-7 z-20 w-56 rounded-lg bg-slate-800 p-3 text-left text-xs font-semibold text-white shadow-lg">Dicentang bila murid {{ lcfirst($info['arti']) }}.</div>
                                            <button type="button" x-show="boleh" x-on:click="semua('{{ $k }}')"
                                                class="min-h-8 rounded-md border border-garis bg-white px-2 text-[11px] font-bold text-slate-700 hover:border-slate-400"
                                                x-text="semuaTercentang('{{ $k }}') ? 'Kosongkan' : 'Centang semua'"></button>
                                            <span class="text-[11px] font-bold tabular-nums text-slate-600"><span x-text="jumlah('{{ $k }}')"></span>/{{ $this->murid->count() }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis">
                            @foreach ($this->murid as $i => $m)
                                <tr class="hover:bg-krem">
                                    <td class="px-4 py-1.5 tabular-nums text-slate-600">{{ $i + 1 }}</td>
                                    <td class="px-2 py-1.5 font-bold">{{ $m->nama }}</td>
                                    @foreach ($indikator as $k => $info)
                                        <td @class(['px-2 py-1 text-center', 'border-l border-garis' => in_array($k, ['hadir', 'memahami'])])>
                                            <button type="button" x-on:click="ubah({{ $m->id }}, '{{ $k }}')"
                                                :disabled="! bisa({{ $m->id }}, '{{ $k }}')"
                                                :aria-pressed="c[{{ $m->id }}].{{ $k }}"
                                                aria-label="{{ $info['label'] }}: {{ $m->nama }}"
                                                :class="c[{{ $m->id }}].{{ $k }} ? '{{ $warnaCentang($k) }} text-white' : 'bg-white border-slate-300 text-transparent'"
                                                class="mx-auto grid h-9 w-9 place-items-center rounded-lg border-2 transition-colors enabled:hover:border-slate-500 disabled:cursor-not-allowed disabled:opacity-30">
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
                    <div x-show="boleh" class="border-b border-garis px-4 py-3">
                        <p class="mb-2 text-xs font-bold text-slate-600">Centang semua murid sekaligus:</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($indikator as $k => $info)
                                <button type="button" x-on:click="semua('{{ $k }}')"
                                    :class="semuaTercentang('{{ $k }}') ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-700 border-garis'"
                                    class="min-h-11 rounded-lg border px-1 text-xs font-bold leading-tight">{{ $k === 'menyampaikan' ? 'Menyampaikan' : $info['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <ul class="divide-y divide-garis">
                        @foreach ($this->murid as $i => $m)
                            <li class="px-4 py-3">
                                <p class="mb-2 font-extrabold"><span class="mr-1 tabular-nums text-slate-600">{{ $i + 1 }}.</span> {{ $m->nama }}</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ($indikator as $k => $info)
                                        <button type="button" x-on:click="ubah({{ $m->id }}, '{{ $k }}')"
                                            :disabled="! bisa({{ $m->id }}, '{{ $k }}')"
                                            :aria-pressed="c[{{ $m->id }}].{{ $k }}"
                                            aria-label="{{ $info['label'] }}: {{ $m->nama }}"
                                            :class="c[{{ $m->id }}].{{ $k }} ? '{{ $warnaCentang($k) }} text-white' : 'bg-white border-slate-300 text-slate-700'"
                                            class="flex min-h-11 items-center justify-center gap-0.5 rounded-lg border-2 px-0.5 text-[11px] font-bold leading-tight transition-colors disabled:opacity-30 min-[400px]:text-xs">
                                            <x-heroicon-s-check x-show="c[{{ $m->id }}].{{ $k }}" class="h-3.5 w-3.5 shrink-0" />
                                            {{ $k === 'menyampaikan' ? 'Menyampaikan' : $info['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Status simpan menempel di bawah layar agar terlihat di mana pun guru mengetuk --}}
                <div x-show="boleh" class="sticky bottom-[calc(3.5rem+env(safe-area-inset-bottom))] z-10 flex items-center justify-between gap-3 rounded-b-xl border-t border-garis bg-white px-4 py-2.5 text-sm font-bold md:bottom-0" aria-live="polite">
                    <span class="tabular-nums text-slate-600"><span x-text="jumlah('hadir')"></span> dari {{ $this->murid->count() }} murid hadir</span>
                    <span x-show="status === 'siap'" class="text-slate-600">Tersimpan otomatis</span>
                    <span x-cloak x-show="status === 'menunggu' || status === 'menyimpan'" class="text-amber-800">Menyimpan…</span>
                    <span x-cloak x-show="status === 'tersimpan'" class="inline-flex items-center gap-1 text-toska-800"><x-heroicon-s-check-circle class="h-4 w-4" /> Tersimpan <span x-text="jam"></span></span>
                    <span x-cloak x-show="status === 'gagal'" class="text-rose-700">Gagal menyimpan. <button x-on:click="kirim()" class="underline">Coba lagi</button></span>
                </div>
            </section>

            {{-- Isian opsional --}}
            <section class="grid gap-5 lg:grid-cols-2">
                <div class="kartu p-5">
                    <h2 class="font-extrabold">Jenis kegiatan hari ini <span class="text-sm font-semibold text-slate-600">(opsional)</span></h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($jenisKegiatan as $j)
                            @php $dipilih = in_array((string) $j->id, $jenisDipilih, true); @endphp
                            <button type="button" wire:click="toggleJenis({{ $j->id }})" @disabled(! $this->bolehUbah) aria-pressed="{{ $dipilih ? 'true' : 'false' }}"
                                @class(['inline-flex min-h-11 items-center gap-1 rounded-lg border px-3 text-sm font-bold transition-colors disabled:opacity-60',
                                    'border-slate-800 bg-slate-800 text-white' => $dipilih,
                                    'border-garis bg-white text-slate-700 hover:border-slate-400' => ! $dipilih])>
                                @if ($dipilih) <x-heroicon-s-check class="h-4 w-4" /> @endif
                                {{ $j->nama }}
                            </button>
                        @endforeach
                    </div>

                    <label for="catatan" class="label mt-5">Catatan singkat <span class="font-normal text-slate-600">(opsional)</span></label>
                    <textarea id="catatan" wire:model="catatan" rows="2" @disabled(! $this->bolehUbah) placeholder="Contoh: Membaca buku cerita rakyat bersama" class="isian"></textarea>
                    @if ($this->bolehUbah)
                        <button wire:click="simpanCatatan" class="tombol-kedua mt-2">Simpan catatan</button>
                    @endif
                </div>

                <div class="kartu p-5">
                    <h2 class="font-extrabold">Foto dokumentasi <span class="text-sm font-semibold text-slate-600">(opsional)</span></h2>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($this->kegiatan?->foto ?? [] as $f)
                            <div wire:key="foto-{{ $f->id }}" class="relative aspect-square overflow-hidden rounded-lg bg-slate-100">
                                <img src="{{ $f->url() }}" alt="Dokumentasi kegiatan" class="h-full w-full object-cover" loading="lazy">
                                @if ($this->bolehUbah)
                                    <button wire:click="hapusFoto({{ $f->id }})" wire:confirm="Hapus foto ini?" class="absolute right-1 top-1 grid h-9 w-9 place-items-center rounded-md bg-white text-rose-700 shadow" aria-label="Hapus foto">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        @if ($this->bolehUbah)
                            <label class="grid aspect-square cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-300 text-center text-xs font-bold text-slate-600 hover:border-slate-500">
                                <span wire:loading.remove wire:target="fotoBaru"><x-heroicon-o-camera class="mx-auto h-7 w-7" />Tambah foto</span>
                                <span wire:loading wire:target="fotoBaru">Mengunggah…</span>
                                <input type="file" wire:model="fotoBaru" accept="image/*" multiple class="sr-only">
                            </label>
                        @endif
                    </div>
                    @error('fotoBaru.*') <p class="galat">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-slate-600">Admin dapat memilih foto untuk Galeri publik. Hindari foto yang menampilkan nama atau data pribadi murid.</p>
                </div>
            </section>

            @if ($this->kegiatan?->riwayat->isNotEmpty())
                <section class="kartu p-5">
                    <h2 class="font-extrabold">Riwayat pengisian</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-700">
                        @foreach ($this->kegiatan->riwayat->take(10) as $r)
                            <li>{{ $r->aksi === 'membuat' ? 'Diisi' : 'Diubah' }} oleh <b>{{ $r->user?->name ?? '-' }}</b>, {{ $r->created_at->format('d/m/Y H.i') }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    @endif
</div>
