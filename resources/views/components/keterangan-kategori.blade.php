@use('App\Models\Pengaturan')

<p class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
    <span>Kategori:</span>
    <x-lencana-kategori :persen="100" :teks="'Sangat Baik ≥ '.(int) Pengaturan::angka('batas_sangat_baik').'%'" />
    <x-lencana-kategori :persen="(float) Pengaturan::angka('batas_baik')" :teks="'Baik ≥ '.(int) Pengaturan::angka('batas_baik').'%'" />
    <x-lencana-kategori :persen="(float) Pengaturan::angka('batas_cukup')" :teks="'Cukup ≥ '.(int) Pengaturan::angka('batas_cukup').'%'" />
    <x-lencana-kategori :persen="0" :teks="'Perlu Bimbingan < '.(int) Pengaturan::angka('batas_cukup').'%'" />
</p>
