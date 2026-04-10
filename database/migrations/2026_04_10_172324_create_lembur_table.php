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
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');
            $table->date('tanggal');
            $table->decimal('jam_lembur', 5, 2);       // contoh: 2.5 jam
            $table->decimal('upah_lembur', 10, 2);     // total upah lembur (Rp)
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur');
    }
};