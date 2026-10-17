<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kondisi awal kasus pembinaan menjadi opsional agar guru tidak wajib mengetik
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasus_pembinaan', function (Blueprint $table) {
            $table->text('kondisi_awal')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kasus_pembinaan', function (Blueprint $table) {
            $table->text('kondisi_awal')->nullable(false)->change();
        });
    }
};
