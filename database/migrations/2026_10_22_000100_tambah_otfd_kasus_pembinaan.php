<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pencatatan pelanggaran memakai OTFD: Observation = kondisi_awal, Desire = komitmen,
// ditambah Thought (pikiran) dan Feeling (perasaan) murid
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasus_pembinaan', function (Blueprint $table) {
            $table->text('pikiran')->nullable()->after('kondisi_awal');
            $table->text('perasaan')->nullable()->after('pikiran');
        });
    }

    public function down(): void
    {
        Schema::table('kasus_pembinaan', function (Blueprint $table) {
            $table->dropColumn(['pikiran', 'perasaan']);
        });
    }
};
