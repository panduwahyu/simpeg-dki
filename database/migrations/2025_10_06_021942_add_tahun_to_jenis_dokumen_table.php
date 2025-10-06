<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jenis_dokumen', function (Blueprint $table) {
            $table->integer('tahun')->after('deskripsi')->nullable();
        });
    }

    public function down()
    {
        Schema::table('jenis_dokumen', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
