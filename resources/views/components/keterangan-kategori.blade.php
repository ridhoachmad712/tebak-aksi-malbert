@use('App\Models\Pengaturan')

<div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
    <span>Kategori:</span>
    <span class="lencana bg-emerald-100 text-emerald-800">Sangat Baik ≥ {{ (int) Pengaturan::angka('batas_sangat_baik') }}%</span>
    <span class="lencana bg-sky-100 text-sky-800">Baik ≥ {{ (int) Pengaturan::angka('batas_baik') }}%</span>
    <span class="lencana bg-amber-100 text-amber-800">Cukup ≥ {{ (int) Pengaturan::angka('batas_cukup') }}%</span>
    <span class="lencana bg-rose-100 text-rose-800">Perlu Bimbingan &lt; {{ (int) Pengaturan::angka('batas_cukup') }}%</span>
</div>
