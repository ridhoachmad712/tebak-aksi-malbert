{{-- Pesan singkat setelah menyimpan. Bisa dipicu dari session flash atau event Livewire "notifikasi". --}}
<div x-data="{ tampil: false, pesan: '', jenis: 'sukses', t: null,
        buka(p, j = 'sukses') { this.pesan = p; this.jenis = j; this.tampil = true; clearTimeout(this.t); this.t = setTimeout(() => this.tampil = false, 4000) } }"
     x-init="@if (session('sukses')) buka(@js(session('sukses'))) @elseif (session('galat')) buka(@js(session('galat')), 'galat') @endif"
     x-on:notifikasi.window="buka($event.detail.pesan, $event.detail.jenis ?? 'sukses')"
     class="pointer-events-none fixed inset-x-0 top-3 z-50 flex justify-center px-4">
    <div x-cloak x-show="tampil" x-transition.opacity.duration.150ms role="status"
         :class="jenis === 'galat' ? 'bg-rose-700' : 'bg-slate-800'"
         class="pointer-events-auto flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-bold text-white shadow-lg">
        <x-heroicon-o-check-circle x-show="jenis !== 'galat'" class="h-5 w-5 shrink-0" />
        <x-heroicon-o-exclamation-triangle x-show="jenis === 'galat'" class="h-5 w-5 shrink-0" />
        <span x-text="pesan"></span>
    </div>
</div>
