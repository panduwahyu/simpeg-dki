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
            $table->string('nip')->nullable()->after('role');
            $table->string('unit_kerja')->nullable()->after('nip');
            $table->string('jabatan')->nullable()->after('unit_kerja');
            $table->string('pangkat')->nullable()->after('jabatan');
            $table->string('golongan')->nullable()->after('pangkat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'unit_kerja', 'jabatan', 'pangkat', 'golongan']);
        });
    }
};
