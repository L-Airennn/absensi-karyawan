<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    protected $fillable = [
        'nama_divisi',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Divisi memiliki banyak karyawan */
    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** Hitung jumlah karyawan aktif di divisi ini */
    public function jumlahKaryawanAktif(): int
    {
        return $this->karyawan()->where('status', 'aktif')->count();
    }
}
