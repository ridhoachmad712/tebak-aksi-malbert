@use('App\Services\Rekap')

@php
    // Satu warna netral untuk centang; jingga disimpan untuk tombol utama
    $warnaCentang = fn (string $k) => 'bg-slate-800 border-slate-800';
    $labelPendek = fn (string $k, array $info) => $k === 'menyampaikan' ? 'Menyampaikan' : $info['label'];
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
            <div class="flex flex-1 flex-col items-center text-center">
                <span class="text-lg font-extrabold">{{ Rekap::tanggalPanjang($tgl) }}</span>
                {{-- Kotak tanggal bawaan browser diletakkan transparan di atas tulisan, sehingga yang diklik langsung pemilih tanggal (andal di Chrome, Safari, dan HP) --}}
                <label class="relative mt-0.5 inline-flex min-h-8 cursor-pointer items-center gap-1 rounded-md px-2 text-sm font-bold text-brand-700 focus-within:ring-2 focus-within:ring-brand-700 hover:underline">
                    <x-heroicon-o-calendar class="h-4 w-4" /> Ganti tanggal
                    <input type="date" wire:model.live="tanggal" max="{{ today()->toDateString() }}" aria-label="Ganti tanggal kegiatan"
                        x-data x-on:click="try { $el.showPicker() } catch (e) {}"
                        class="pemilih-tanggal absolute inset-0 h-full w-full cursor-pointer opacity-0">
                </label>
            </div>
            <button wire:click="pindahHari(1)" @disabled($tgl->isToday()) class="tombol-kedua px-3" aria-label="Hari kegiatan berikutnya"><x-heroicon-m-chevron-right class="h-5 w-5" /></button>
        </div>


        @if ($this->belumDicatat->isNotEmpty())
            @php $berikut = $this->belumDicatat->first(); @endphp
            <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-garis bg-white px-4 py-2.5 text-sm">
                <span class="text-slate-700">Masih ada <b>{{ $this->belumDicatat->count() }} hari</b> kegiatan lain yang belum dicatat.</span>
                <button type="button" wire:click="keBelumDicatat" class="tombol-kedua min-h-10 text-sm">
                    Catat {{ Rekap::namaHari($berikut) }}, {{ $berikut->day }} {{ mb_substr(Rekap::BULAN[$berikut->month], 0, 3) }} <x-heroicon-m-arrow-right class="h-4 w-4" />
                </button>
            </div>
        @endif

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
                    <p>Catatan ini hanya bisa dilihat. Guru dapat mengubah catatan mulai {{ \App\Services\Rekap::tanggalPanjang(\App\Models\Pengaturan::ubahMulai()) }}. Hubungi admin jika perlu koreksi.</p>
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
                    sisa(k) { return this.layak(k).filter(id => ! this.c[id][k]).length },
                    aktif: 'hadir',
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
                <div class="hidden overflow-x-auto md:block lg:overflow-visible">
                    <table class="w-full text-sm">
                        {{-- Judul kolom menempel di bawah bilah atas saat daftar digulir --}}
                        <thead class="tabel-kepala lg:sticky lg:top-16 lg:z-10 lg:shadow-[0_1px_0_var(--color-garis)]">
                            <tr>
                                <th class="px-4 pt-3" rowspan="2">No</th>
                                <th class="px-2 pt-3" rowspan="2">Nama murid</th>
                                <th colspan="3" class="border-l border-garis px-2 pt-3 text-center uppercase tracking-wide text-slate-700">Budaya Membaca</th>
                                <th colspan="3" class="border-l border-garis px-2 pt-3 text-center uppercase tracking-wide text-slate-700">Kemampuan Literasi</th>
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
                                            <span x-show="boleh && sisa('{{ $k }}')" class="text-[11px] font-bold text-amber-800"><span x-text="sisa('{{ $k }}')"></span> belum</span>
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

                {{-- Tampilan HP: satu kolom sekaligus agar daftar tetap pendek (absen dulu, lalu kolom berikutnya) --}}
                <div class="md:hidden">
                    <div class="sticky top-16 z-10 border-b border-garis bg-white px-3 py-3">
                        <p class="mb-2 text-xs font-bold text-slate-600">Pilih yang sedang dicentang:</p>
                        <div class="grid grid-cols-3 gap-2" role="tablist">
                            @foreach ($indikator as $k => $info)
                                <button type="button" role="tab" x-on:click="aktif = '{{ $k }}'" :aria-selected="aktif === '{{ $k }}'"
                                    :class="aktif === '{{ $k }}' ? 'border-slate-800 bg-slate-800 text-white' : 'border-garis bg-white text-slate-700'"
                                    class="flex min-h-12 flex-col items-center justify-center rounded-lg border px-1 text-xs font-bold leading-tight">
                                    {{ $labelPendek($k, $info) }}
                                    <span class="mt-0.5 text-[11px] font-semibold tabular-nums opacity-80"><span x-text="jumlah('{{ $k }}')"></span>/{{ $this->murid->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($indikator as $k => $info)
                        <div x-show="aktif === '{{ $k }}'" @if (! $loop->first) x-cloak @endif>
                            <div class="flex items-center justify-between gap-2 border-b border-garis bg-krem px-4 py-2">
                                <p class="text-xs text-slate-700">Dicentang bila murid {{ lcfirst($info['arti']) }}.</p>
                                <button type="button" x-show="boleh" x-on:click="semua('{{ $k }}')" class="tombol-kedua min-h-10 shrink-0 px-3 text-xs"
                                    x-text="semuaTercentang('{{ $k }}') ? 'Kosongkan' : 'Centang semua'"></button>
                            </div>
                            <ul class="divide-y divide-garis">
                                @foreach ($this->murid as $i => $m)
                                    <li>
                                        <button type="button" x-on:click="ubah({{ $m->id }}, '{{ $k }}')"
                                            :disabled="! bisa({{ $m->id }}, '{{ $k }}')"
                                            :aria-pressed="c[{{ $m->id }}].{{ $k }}"
                                            aria-label="{{ $info['label'] }}: {{ $m->nama }}"
                                            class="flex min-h-14 w-full items-center gap-3 px-4 py-2 text-left disabled:cursor-not-allowed">
                                            <span class="w-6 shrink-0 text-sm tabular-nums text-slate-500">{{ $i + 1 }}</span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block font-bold" :class="bisa({{ $m->id }}, '{{ $k }}') || ! boleh ? 'text-slate-900' : 'text-slate-400'">{{ $m->nama }}</span>
                                                @if ($k !== 'hadir')
                                                    <span x-show="! c[{{ $m->id }}].hadir" class="block text-xs text-slate-500">Belum dicentang Hadir</span>
                                                @endif
                                            </span>
                                            <span :class="c[{{ $m->id }}].{{ $k }} ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-slate-300 text-transparent'"
                                                :style="bisa({{ $m->id }}, '{{ $k }}') || c[{{ $m->id }}].{{ $k }} ? '' : 'opacity:.3'"
                                                class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border-2 transition-colors">
                                                <x-heroicon-s-check class="h-6 w-6" />
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
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
                        <button wire:click="simpanCatatan" wire:loading.attr="disabled" class="tombol-utama mt-2">Simpan catatan</button>
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
