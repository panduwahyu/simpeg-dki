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
        Schema::table('mandatory_uploads', function (Blueprint $table) {
            // tambahin kolom penilaian, default 0, cuma boleh 0 atau 1
            $table->boolean('penilaian')->default(0)->after('is_uploaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mandatory_uploads', function (Blueprint $table) {
            $table->dropColumn('penilaian');
        });
    }
};
