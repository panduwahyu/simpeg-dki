<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi (hapus kolom about).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // hapus kolom about
            $table->dropColumn('about');
        });
    }

    /**
     * Rollback migrasi (kembalikan kolom about kalau di-rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // tambahkan lagi kolom about saat rollback
            $table->text('about')->nullable();
        });
    }
};
