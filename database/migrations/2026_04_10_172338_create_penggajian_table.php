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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['harian', 'mingguan', 'bulanan']);
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->timestamp('tanggal_generate')->useCurrent();
            $table->foreignId('digenerate_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('keterangan')->nullable();
            $table->softDeletes();   // deleted_at untuk soft delete
            $table->timestamps();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};