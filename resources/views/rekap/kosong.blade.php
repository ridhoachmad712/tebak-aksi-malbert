{{-- Baris kosong bersama untuk tabel rekap. Butuh: $kolom --}}
<tr>
    <td colspan="{{ $kolom }}" class="px-4 py-8 text-center text-slate-600">
        @if ($hanyaPerhatian && $rekap['murid']->isNotEmpty())
            Tidak ada murid yang perlu perhatian pada periode ini. Hapus centang "Hanya yang perlu perhatian" untuk melihat semua murid.
        @else
            Belum ada murid aktif di kelas ini. Admin dapat menambahkan murid di Halaman Admin, menu Murid.
        @endif
    </td>
</tr>
