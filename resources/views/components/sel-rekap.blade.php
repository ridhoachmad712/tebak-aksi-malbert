@props(['jumlah', 'n', 'persen'])

<td class="px-3 py-2 text-center">
    <x-lencana-kategori :persen="$persen" :teks="$n ? $jumlah.'/'.$n : '-'" />
</td>
