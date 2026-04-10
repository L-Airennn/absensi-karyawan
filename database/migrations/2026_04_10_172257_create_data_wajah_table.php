<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('data_wajah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');
            $table->string('foto_wajah');         // path file foto
            $table->json('face_encoding');         // array descriptor dari face-api.js
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_wajah');
    }
};