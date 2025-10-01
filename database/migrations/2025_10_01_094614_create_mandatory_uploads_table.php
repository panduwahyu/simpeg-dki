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
        Schema::create('mandatory_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periode')->onDelete('cascade');
            $table->boolean('is_uploaded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandatory_uploads');
    }
};
