<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20); // contoh: 2026/2027
            $table->date('mulai');
            $table->date('selesai');
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('tingkat'); // 1-6
            $table->string('nama', 20); // contoh: 4A
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'nama']);
        });

        // Penugasan guru mata pelajaran ke kelas
        Schema::create('kelas_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['kelas_id', 'user_id']);
        });

        Schema::create('murid', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nis', 30)->nullable();
            $table->string('nisn', 30)->nullable();
            $table->string('jenis_kelamin', 1)->nullable(); // L | P
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Keanggotaan murid di kelas per tahun ajaran (mendukung kenaikan kelas)
        Schema::create('kelas_murid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            $table->unique(['kelas_id', 'murid_id']);
        });

        Schema::create('hari_libur', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan');
            $table->timestamps();
        });

        Schema::create('pengaturan', function (Blueprint $table) {
            $table->string('kunci', 60)->primary();
            $table->text('nilai')->nullable();
        });

        Schema::create('baseline_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester'); // 1 = Ganjil, 2 = Genap
            $table->unsignedInteger('jumlah');
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baseline_pelanggaran');
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('hari_libur');
        Schema::dropIfExists('kelas_murid');
        Schema::dropIfExists('murid');
        Schema::dropIfExists('kelas_guru');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('tahun_ajaran');
    }
};
