{{-- Pesan singkat setelah menyimpan. Bisa dipicu dari session flash atau event Livewire "notifikasi". --}}
<div x-data="{ tampil: false, pesan: '', jenis: 'sukses', t: null,
        buka(p, j = 'sukses') { this.pesan = p; this.jenis = j; this.tampil = true; clearTimeout(this.t); this.t = setTimeout(() => this.tampil = false, 3500) } }"
     x-init="@if (session('sukses')) buka(@js(session('sukses'))) @elseif (session('galat')) buka(@js(session('galat')), 'galat') @endif"
     x-on:notifikasi.window="buka($event.detail.pesan, $event.detail.jenis ?? 'sukses')"
     class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
    <div x-cloak x-show="tampil" x-transition.opacity.duration.200ms role="status"
         :class="jenis === 'galat' ? 'bg-rose-600' : 'bg-toska-700'"
         class="pointer-events-auto flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white shadow-lg">
        <span x-text="jenis === 'galat' ? '⚠️' : '✅'"></span>
        <span x-text="pesan"></span>
    </div>
</div>
