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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit'])
                  ->default('tidak_hadir');
            $table->enum('sumber', ['face', 'manual'])->default('manual');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Satu karyawan hanya boleh 1 record absensi per hari
            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};