{{-- Ikon tab browser: logo sekolah bila diatur admin, selain itu ikon bawaan TEBAK AKSI --}}
@if ($logo = \App\Models\Pengaturan::ambil('logo_sekolah'))
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@endif
