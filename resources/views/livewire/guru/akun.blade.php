<div class="mx-auto max-w-md space-y-5">
    <h1 class="judul-halaman">Ubah kata sandi</h1>
    <form wire:submit="simpan" class="kartu space-y-4 p-6">
        <div>
            <label for="lama" class="label">Kata sandi lama</label>
            <input id="lama" type="password" wire:model="sandiLama" autocomplete="current-password" class="isian">
            @error('sandiLama') <p class="galat">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="baru" class="label">Kata sandi baru</label>
            <input id="baru" type="password" wire:model="sandiBaru" autocomplete="new-password" class="isian">
            @error('sandiBaru') <p class="galat">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="ulang" class="label">Ulangi kata sandi baru</label>
            <input id="ulang" type="password" wire:model="sandiBaru_confirmation" autocomplete="new-password" class="isian">
        </div>
        <button class="tombol-utama w-full">Simpan</button>
    </form>
</div>
