<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // contoh: 11, 1101, 110101
            $table->string('nama');
            $table->enum('tingkat', ['provinsi', 'kabupaten', 'kecamatan', 'desa']);
            $table->string('kode_induk')->nullable(); // relasi ke wilayah induk
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
