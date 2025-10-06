<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            // Tambah kolom penilai_id, tipe bigint (sesuaikan tipe user_id)
            $table->unsignedBigInteger('penilai_id')->nullable()->after('id');

            // Optional: kalau mau bikin foreign key ke tabel users
            $table->foreign('penilai_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen', function (Blueprint $table) {
            // Drop foreign key dulu sebelum kolom
            $table->dropForeign(['penilai_id']);
            $table->dropColumn('penilai_id');
        });
    }
};
