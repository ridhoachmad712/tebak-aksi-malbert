{{-- Kesimpulan singkat agar kepala sekolah langsung tahu keadaan periode ini. Butuh: $kalimat --}}
<section class="kartu border-l-4 border-l-slate-800 p-5" aria-label="Ringkasan" wire:loading.class="opacity-60">
    <h2 class="text-xs font-extrabold uppercase tracking-wide text-slate-600">Ringkasan</h2>
    <div class="mt-1 space-y-1 text-base leading-relaxed text-slate-900">
        @foreach ($kalimat as $k)
            <p @class(['font-bold' => $loop->first])>{{ $k }}</p>
        @endforeach
    </div>
</section>
