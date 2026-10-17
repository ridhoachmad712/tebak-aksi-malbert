@use('App\Models\Pengaturan')

<p class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
    <span>Warna hanya untuk nilai rendah:</span>
    <x-lencana-kategori :persen="(float) Pengaturan::angka('batas_cukup')" :teks="'Cukup '.(int) Pengaturan::angka('batas_cukup').'–'.((int) Pengaturan::angka('batas_baik') - 1).'%'" />
    <x-lencana-kategori :persen="0" :teks="'Perlu Bimbingan < '.(int) Pengaturan::angka('batas_cukup').'%'" />
    <span>Nilai tanpa warna sudah Baik (≥ {{ (int) Pengaturan::angka('batas_baik') }}%) atau Sangat Baik (≥ {{ (int) Pengaturan::angka('batas_sangat_baik') }}%).</span>
</p>
