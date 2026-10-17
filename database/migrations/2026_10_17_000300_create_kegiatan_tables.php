<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu catatan bersama per kelas per hari
        Schema::create('kegiatan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['kelas_id', 'tanggal']);
        });

        Schema::create('kegiatan_harian_murid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_harian_id')->constrained('kegiatan_harian')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            $table->boolean('hadir')->default(false);
            $table->boolean('konsisten')->default(false);
            $table->boolean('jurnal')->default(false);
            $table->boolean('memahami')->default(false);
            $table->boolean('merangkum')->default(false);
            $table->boolean('menyampaikan')->default(false);
            $table->timestamps();
            $table->unique(['kegiatan_harian_id', 'murid_id']);
        });

        Schema::create('kegiatan_harian_jenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_harian_id')->constrained('kegiatan_harian')->cascadeOnDelete();
            $table->foreignId('jenis_kegiatan_id')->constrained('jenis_kegiatan')->cascadeOnDelete();
            $table->unique(['kegiatan_harian_id', 'jenis_kegiatan_id']);
        });

        // Jejak siapa mengisi/mengubah catatan harian
        Schema::create('riwayat_pengisian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_harian_id')->constrained('kegiatan_harian')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('aksi', 20); // membuat | mengubah
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('kasus_pembinaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('kondisi_awal');
            $table->foreignId('jenis_pelanggaran_id')->nullable()->constrained('jenis_pelanggaran')->nullOnDelete();
            $table->string('jenis_pelanggaran_lain')->nullable();
            $table->string('tingkat', 10); // ringan | sedang | berat
            $table->foreignId('tindakan_pembinaan_id')->nullable()->constrained('tindakan_pembinaan')->nullOnDelete();
            $table->string('tindakan_pembinaan_lain')->nullable();
            $table->text('komitmen')->nullable();
            $table->string('status_akhir', 20)->nullable(); // null = dalam pembinaan
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pemantauan_kasus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_pembinaan_id')->constrained('kasus_pembinaan')->cascadeOnDelete();
            $table->unsignedTinyInteger('minggu'); // 1-4
            $table->string('tingkat', 20); // belum | mulai | positif | konsisten
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['kasus_pembinaan_id', 'minggu']);
        });

        Schema::create('karya_literasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->date('tanggal');
            $table->foreignId('jenis_karya_id')->nullable()->constrained('jenis_karya')->nullOnDelete();
            $table->string('jenis_karya_lain')->nullable();
            $table->string('judul');
            $table->string('buku_sumber')->nullable();
            $table->foreignId('guru_pemilik_id')->nullable()->constrained('users')->nullOnDelete(); // karya milik guru
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pencatat
            $table->timestamps();
        });

        Schema::create('karya_murid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karya_literasi_id')->constrained('karya_literasi')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murid')->cascadeOnDelete();
            $table->unique(['karya_literasi_id', 'murid_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karya_murid');
        Schema::dropIfExists('karya_literasi');
        Schema::dropIfExists('pemantauan_kasus');
        Schema::dropIfExists('kasus_pembinaan');
        Schema::dropIfExists('riwayat_pengisian');
        Schema::dropIfExists('kegiatan_harian_jenis');
        Schema::dropIfExists('kegiatan_harian_murid');
        Schema::dropIfExists('kegiatan_harian');
    }
};
