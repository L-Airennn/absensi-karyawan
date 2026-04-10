<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenggajian extends Model
{
    use HasFactory;

    protected $table = 'detail_penggajian';

    protected $fillable = [
        'penggajian_id',
        'karyawan_id',
        'total_hadir',
        'total_jam_lembur',
        'gaji_pokok',
        'total_upah_lembur',
        'total_gaji',
    ];

    protected $casts = [
        'total_jam_lembur'  => 'decimal:2',
        'gaji_pokok'        => 'decimal:2',
        'total_upah_lembur' => 'decimal:2',
        'total_gaji'        => 'decimal:2',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Detail dimiliki oleh 1 penggajian (header) */
    public function penggajian()
    {
        return $this->belongsTo(Penggajian::class);
    }

    /** Detail untuk 1 karyawan */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    public function getTotalGajiFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_gaji, 0, ',', '.');
    }

    public function getGajiPokokFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getTotalUpahLemburFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_upah_lembur, 0, ',', '.');
    }
}
