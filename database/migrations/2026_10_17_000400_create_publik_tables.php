<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Foto untuk kegiatan harian, karya, diseminasi, atau unggahan galeri langsung
        Schema::create('foto', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('fotoable');
            $table->string('path');
            $table->string('keterangan')->nullable();
            $table->boolean('tampil_galeri')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('konten', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20)->default('berita'); // berita | halaman
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('ringkasan', 500)->nullable();
            $table->longText('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->boolean('terbit')->default(false);
            $table->boolean('tampil_menu')->default(false);
            $table->timestamp('terbit_pada')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('diseminasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('tanggal');
            $table->string('tempat')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('terbit')->default(true);
            $table->timestamps();
        });

        Schema::create('manual_book', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file')->nullable();
            $table->string('untuk', 20)->default('publik'); // publik | guru
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('kontak')->nullable();
            $table->string('kategori', 30);
            $table->text('isi');
            $table->string('status', 20)->default('baru'); // baru | diproses | selesai
            $table->text('tindak_lanjut')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
        Schema::dropIfExists('manual_book');
        Schema::dropIfExists('diseminasi');
        Schema::dropIfExists('konten');
        Schema::dropIfExists('foto');
    }
};
