{{--
    Penampil foto layar penuh untuk panel admin (gaya ditulis langsung karena panel memakai tema bawaan Filament).
    Dibuka lewat event `buka-foto` berisi {url, ket}. Ketuk foto untuk memperbesar di titik yang diketuk,
    tombol + dan − mengatur besarnya, geser untuk melihat bagian lain, Esc atau × untuk menutup.
--}}
<div x-data="{
        buka: null,
        skala: 1,
        lebarDasar: 0,
        tutup() { this.buka = null; this.skala = 1; },
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
    x-on:buka-foto.window="buka = $event.detail; skala = 1"
    x-on:keydown.escape.window="tutup()">
    {{-- x-show di pembungkus agar display:flex pada isi tidak dihapus Alpine --}}
    <div x-cloak x-show="buka" style="position:fixed;inset:0;z-index:60">
    <div role="dialog" aria-modal="true" aria-label="Foto diperbesar"
        style="display:flex;height:100%;flex-direction:column;background:rgba(0,0,0,.9)">
        <div style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem;color:#fff">
            <p x-text="buka?.ket" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.875rem;font-weight:700"></p>
            @foreach ([['Perkecil', 'heroicon-o-minus', 'atur(skala - 0.5)', 'skala <= 1'], ['Perbesar', 'heroicon-o-plus', 'atur(skala + 0.5)', 'skala >= 4']] as [$label, $ikon, $aksi, $mati])
                @if ($label === 'Perbesar')
                    <span x-text="Math.round(skala * 100) + '%'" style="width:3rem;text-align:center;font-size:.875rem;font-weight:700"></span>
                @endif
                <button type="button" x-on:click="{{ $aksi }}" x-bind:disabled="{{ $mati }}" x-bind:style="{{ $mati }} ? 'opacity:.4' : ''" aria-label="{{ $label }}"
                    style="display:grid;place-items:center;height:2.5rem;width:2.5rem;border:0;border-radius:.5rem;background:rgba(255,255,255,.12);color:#fff;cursor:pointer">
                    <x-filament::icon :icon="$ikon" style="height:1.25rem;width:1.25rem" />
                </button>
            @endforeach
            <button type="button" x-on:click="tutup()" aria-label="Tutup"
                style="display:grid;place-items:center;height:2.5rem;width:2.5rem;margin-left:.5rem;border:0;border-radius:.5rem;background:rgba(255,255,255,.12);color:#fff;cursor:pointer">
                <x-filament::icon icon="heroicon-o-x-mark" style="height:1.25rem;width:1.25rem" />
            </button>
        </div>
        {{-- Ketuk di luar foto untuk menutup selama foto belum diperbesar --}}
        <div x-ref="area" x-on:click.self="skala === 1 && tutup()" style="flex:1;min-height:0;overflow:auto">
            <div x-on:click.self="skala === 1 && tutup()" style="display:flex;min-height:100%;min-width:max-content;align-items:center;justify-content:center;padding:1rem">
                <img x-ref="foto" :src="buka?.url" alt="" x-on:click="ketuk($event)"
                    :style="skala > 1 ? `width:${lebarDasar * skala}px;max-width:none;max-height:none;cursor:zoom-out` : 'max-height:calc(100vh - 7rem);max-width:calc(100vw - 2rem);cursor:zoom-in'"
                    style="border-radius:.5rem;user-select:none">
            </div>
        </div>
        <p x-text="skala > 1 ? 'Geser untuk melihat bagian lain. Ketuk foto untuk kembali.' : 'Ketuk foto untuk memperbesar.'"
            style="padding:0 1rem .75rem;text-align:center;font-size:.75rem;color:rgba(255,255,255,.7)"></p>
    </div>
    </div>
</div>
