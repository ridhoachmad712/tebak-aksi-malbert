// Pesan bawaan Livewire saat sesi habis (419) berbahasa Inggris; ganti dengan pesan bahasa Indonesia.
let sesiHabisDitangani = false;

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status !== 419) {
                return;
            }

            preventDefault();
            if (sesiHabisDitangani) {
                return;
            }
            sesiHabisDitangani = true;

            if (window.confirm('Sesi Anda sudah berakhir karena halaman terlalu lama terbuka.\nMuat ulang halaman sekarang? Centang yang belum tersimpan perlu diulang.')) {
                window.location.reload();
            }
        });
    });
});
