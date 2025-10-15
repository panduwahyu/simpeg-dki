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
        Schema::table('users', function (Blueprint $table) {
            // Gunakan string supaya fleksibel: "Aktif", "Tidak Aktif - Pensiun", "Tidak Aktif - Meninggal", dll.
            $table->string('keaktifan', 100)
                  ->default('Aktif')
                  ->after('email')
                  ->comment('Status keaktifan: Aktif / Tidak Aktif - Pensiun / Tidak Aktif - Meninggal / dll');
        });

        // Kalau ingin mengisi nilai default untuk user yang sudah ada (opsional)
        DB::table('users')->whereNull('keaktifan')->update(['keaktifan' => 'Aktif']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('keaktifan');
        });
    }
};
