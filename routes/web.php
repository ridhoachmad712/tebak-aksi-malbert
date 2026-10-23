<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MonevCetakController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\RekapUnduhController;
use App\Livewire\DasborKepsek;
use App\Livewire\Guru;
use App\Livewire\Monev;
use App\Livewire\MonevKelas;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::controller(PublikController::class)->group(function () {
    Route::get('/', 'beranda')->name('beranda');
    Route::get('/halaman/{slug}', 'halaman')->name('halaman');
    Route::get('/berita', 'berita')->name('berita');
    Route::get('/galeri', 'galeri')->name('galeri');
    Route::get('/karya-literasi', 'karya')->name('karya.publik');
    Route::get('/diseminasi', 'diseminasi')->name('diseminasi');
    Route::get('/diseminasi/{diseminasi}', 'diseminasiDetail')->name('diseminasi.detail');
    Route::get('/manual-book', 'manual')->name('manual');
    Route::get('/pengaduan', 'formPengaduan')->name('pengaduan');
    Route::post('/pengaduan', 'kirimPengaduan')->middleware('throttle:10,1');
});

// Masuk, daftar, keluar
Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/masuk', 'formMasuk')->name('login');
        Route::post('/masuk', 'masuk')->middleware('throttle:20,1');
        Route::get('/daftar', 'formDaftar')->name('daftar');
        Route::post('/daftar', 'daftar')->middleware('throttle:10,1');
    });
    Route::middleware('auth')->group(function () {
        Route::get('/arah', 'arah')->name('masuk.arah');
        Route::view('/pilih-halaman', 'pilih-halaman')->middleware('peran:admin')->name('admin.pilih');
        Route::post('/keluar', 'keluar')->name('keluar');
    });
});

// Halaman guru (admin juga boleh membuka untuk koreksi data)
Route::middleware(['auth', 'peran:guru,admin'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', Guru\Beranda::class)->name('beranda');
    Route::get('/kegiatan/{kelas?}', Guru\KegiatanHarianForm::class)->name('kegiatan');
    Route::get('/pembinaan', Guru\PembinaanIndex::class)->name('pembinaan');
    Route::get('/pembinaan/baru', Guru\PembinaanForm::class)->name('pembinaan.baru');
    Route::get('/pembinaan/{kasus}', Guru\PembinaanDetail::class)->name('pembinaan.detail');
    Route::get('/pembinaan/{kasus}/ubah', Guru\PembinaanForm::class)->name('pembinaan.ubah');
    Route::get('/karya', Guru\KaryaIndex::class)->name('karya');
    Route::get('/karya/baru', Guru\KaryaForm::class)->name('karya.baru');
    Route::get('/karya/{karya}/ubah', Guru\KaryaForm::class)->name('karya.ubah');
});

Route::middleware(['auth', 'peran:guru,admin,kepsek'])->group(function () {
    Route::get('/guru/rekap', Guru\RekapKelas::class)->name('guru.rekap');
    Route::get('/rekap/unduh', RekapUnduhController::class)->name('rekap.unduh');
    Route::get('/akun', Guru\Akun::class)->name('guru.akun');
    Route::redirect('/panduan', '/manual-book')->name('guru.panduan');
});

Route::middleware(['auth', 'peran:admin,kepsek'])->group(function () {
    Route::get('/dashboard', DasborKepsek::class)->name('kepsek.dasbor');
    Route::get('/monev', Monev::class)->name('monev');
    Route::get('/monev/cetak', MonevCetakController::class)->name('monev.cetak');
    Route::get('/monev/kelas/{kelas}', MonevKelas::class)->name('monev.kelas');
});
