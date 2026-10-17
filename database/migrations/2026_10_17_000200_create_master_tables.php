<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspek_karakter', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jenis_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('aspek_karakter_id')->nullable()->constrained('aspek_karakter')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('tindakan_pembinaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jenis_karya', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenjang', 10)->default('semua'); // rendah | tinggi | semua
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jenis_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kegiatan');
        Schema::dropIfExists('jenis_karya');
        Schema::dropIfExists('tindakan_pembinaan');
        Schema::dropIfExists('jenis_pelanggaran');
        Schema::dropIfExists('aspek_karakter');
    }
};
