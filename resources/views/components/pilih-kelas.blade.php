@props(['daftar', 'model' => 'kelasId'])

{{-- Guru dengan satu kelas langsung melihat nama kelasnya; guru dengan beberapa kelas memilih dari daftar. --}}
@if ($daftar->count() > 1)
    <label {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
        <span class="text-sm font-bold text-slate-600">Kelas</span>
        <select wire:model.live="{{ $model }}" class="isian w-auto pr-9 font-bold">
            @foreach ($daftar as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
    </label>
@elseif ($daftar->count() === 1)
    <span {{ $attributes->merge(['class' => 'text-sm font-bold text-slate-700']) }}>Kelas {{ $daftar->first()->nama }}</span>
@endif
