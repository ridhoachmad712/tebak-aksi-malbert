@props(['periode'])

<select wire:model.live="periode" aria-label="Jenis periode" class="isian w-auto pr-9 font-bold">
    <option value="bulan">Per bulan</option>
    <option value="semester">Per semester</option>
</select>
@if ($periode === 'semester')
    <select wire:model.live="semester" aria-label="Semester" class="isian w-auto pr-9 font-bold">
        <option value="1">Semester Ganjil</option>
        <option value="2">Semester Genap</option>
    </select>
@else
    <select wire:model.live="bulan" aria-label="Bulan" class="isian w-auto pr-9 font-bold">
        @foreach (\App\Services\Rekap::daftarBulan() as $nilai => $label)
            <option value="{{ $nilai }}">{{ $label }}</option>
        @endforeach
    </select>
@endif
