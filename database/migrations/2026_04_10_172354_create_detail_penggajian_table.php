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
        Schema::create('detail_penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penggajian_id')
                  ->constrained('penggajian')
                  ->onDelete('cascade');
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');
            $table->integer('total_hadir')->default(0);
            $table->decimal('total_jam_lembur', 6, 2)->default(0);
            $table->decimal('gaji_pokok', 12, 2)->default(0);   // total_hadir × gaji_harian
            $table->decimal('total_upah_lembur', 12, 2)->default(0);
            $table->decimal('total_gaji', 12, 2)->default(0);   // gaji_pokok + total_upah_lembur
            $table->timestamps();

            // Satu karyawan hanya boleh muncul 1x per periode penggajian
            $table->unique(['penggajian_id', 'karyawan_id']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penggajian');
    }
};