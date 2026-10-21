<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Patch SQL dapat diimpor lebih dahulu melalui phpMyAdmin.
        if (! Schema::hasTable('rekap_historis_bulanan')) {
            Schema::create('rekap_historis_bulanan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
                $table->date('bulan');
                $table->json('data');
                $table->string('sumber');
                $table->text('catatan')->nullable();
                $table->timestamps();
                $table->unique(['tahun_ajaran_id', 'bulan']);
            });
        }

        if (! Schema::hasTable('rencana_rekonsiliasi_kelas')) {
            Schema::create('rencana_rekonsiliasi_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
                $table->date('bulan');
                $table->json('data');
                $table->string('status', 30)->default('perlu_verifikasi');
                $table->timestamps();
                $table->unique(['kelas_id', 'bulan']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_rekonsiliasi_kelas');
        Schema::dropIfExists('rekap_historis_bulanan');
    }
};
