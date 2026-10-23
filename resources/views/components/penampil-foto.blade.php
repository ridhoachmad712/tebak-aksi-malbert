{{--
    Penampil foto layar penuh yang bisa diperbesar. Dipakai di dalam elemen Alpine yang punya
    variabel `buka` berisi {url, ket}. Ketuk foto untuk memperbesar di titik yang diketuk,
    ketuk lagi untuk kembali; tombol + dan − mengatur besarnya, lalu geser untuk melihat bagian lain.
--}}
<div x-cloak x-show="buka" x-on:keydown.escape.window="buka = null"
    x-data="{
        skala: 1,
        lebarDasar: 0,
        atur(baru, titikX = 0.5, titikY = 0.5) {
            const img = this.$refs.foto, area = this.$refs.area;
            if (this.skala === 1) this.lebarDasar = img.clientWidth;
            this.skala = Math.min(4, Math.max(1, baru));
            this.$nextTick(() => {
                area.scrollLeft = img.offsetLeft + img.clientWidth * titikX - area.clientWidth / 2;
                area.scrollTop = img.offsetTop + img.clientHeight * titikY - area.clientHeight / 2;
            });
        },
        ketuk(e) {
            if (this.skala > 1) return this.atur(1);
            const kotak = this.$refs.foto.getBoundingClientRect();
            this.atur(2.5, (e.clientX - kotak.left) / kotak.width, (e.clientY - kotak.top) / kotak.height);
        },
    }"
    x-effect="if (! buka) skala = 1"
    class="fixed inset-0 z-50 flex flex-col bg-black/90" role="dialog" aria-modal="true" aria-label="Foto diperbesar">
    <div class="flex items-center gap-2 px-4 py-3 text-white">
        <p class="min-w-0 flex-1 truncate text-sm font-bold" x-text="buka?.ket"></p>
        <button type="button" x-on:click="atur(skala - 0.5)" x-bind:disabled="skala <= 1" class="grid h-10 w-10 place-items-center rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-40" aria-label="Perkecil"><x-heroicon-o-minus class="h-5 w-5" /></button>
        <span class="w-12 text-center text-sm font-bold tabular-nums" x-text="Math.round(skala * 100) + '%'"></span>
        <button type="button" x-on:click="atur(skala + 0.5)" x-bind:disabled="skala >= 4" class="grid h-10 w-10 place-items-center rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-40" aria-label="Perbesar"><x-heroicon-o-plus class="h-5 w-5" /></button>
        <button type="button" x-on:click="buka = null" class="ml-2 grid h-10 w-10 place-items-center rounded-lg bg-white/10 hover:bg-white/20" aria-label="Tutup"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
    </div>
    {{-- Ketuk di luar foto untuk menutup selama foto belum diperbesar --}}
    <div x-ref="area" class="min-h-0 flex-1 overflow-auto" x-on:click.self="skala === 1 && (buka = null)">
        <div class="flex min-h-full min-w-max items-center justify-center p-4" x-on:click.self="skala === 1 && (buka = null)">
            <img x-ref="foto" :src="buka?.url" alt="" x-on:click="ketuk($event)"
                :style="skala > 1 ? `width: ${lebarDasar * skala}px; max-width: none; max-height: none` : ''"
                :class="skala > 1 ? 'cursor-zoom-out' : 'max-h-[calc(100vh-6rem)] max-w-[calc(100vw-2rem)] cursor-zoom-in'"
                class="select-none rounded-lg">
        </div>
    </div>
    <p class="px-4 pb-3 text-center text-xs text-white/70" x-text="skala > 1 ? 'Geser untuk melihat bagian lain. Ketuk foto untuk kembali.' : 'Ketuk foto untuk memperbesar.'"></p>
</div>
