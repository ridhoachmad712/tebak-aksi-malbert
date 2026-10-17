@php $logo = \App\Models\Pengaturan::ambil('logo_sekolah'); @endphp
<span style="display:inline-flex;align-items:center;gap:.5rem;font-weight:800;font-size:1.05rem">
    @if ($logo)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="" style="height:2rem;width:2rem;object-fit:contain">
    @else
        <img src="{{ asset('favicon.svg') }}" alt="" style="height:2rem;width:2rem">
    @endif
    <span>TEBAK AKSI <span style="font-weight:600;opacity:.6;font-size:.85rem">Admin</span></span>
</span>
