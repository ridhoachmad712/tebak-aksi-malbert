@include('errors.tata-letak', [
    'kode' => '419',
    'judul' => 'Sesi Anda sudah berakhir',
    'pesan' => 'Halaman terlalu lama terbuka sehingga sesi berakhir demi keamanan. Buka lagi halaman tadi, lalu ulangi langkah terakhir. Bila diminta, masuk kembali.',
    'aksi' => new \Illuminate\Support\HtmlString('<a href="'.e(url()->previous()).'" class="utama">Buka lagi halaman tadi</a>'),
])
