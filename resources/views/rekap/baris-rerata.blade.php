{{-- Baris rata-rata kelas di bawah tabel. Butuh: $kolom (daftar kunci persen) --}}
<tfoot class="border-t-2 border-slate-300 bg-slate-50">
    <tr>
        <td class="px-4 py-2"></td>
        <th scope="row" class="px-3 py-2 text-left font-extrabold">Rata-rata kelas</th>
        @foreach ($kolom as $k)
            <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$rerata[$k]" /></td>
        @endforeach
    </tr>
</tfoot>
