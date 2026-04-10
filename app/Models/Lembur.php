<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_lembur',
        'upah_lembur',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'jam_lembur'  => 'decimal:2',
        'upah_lembur' => 'decimal:2',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Lembur dimiliki oleh 1 karyawan */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /** Admin yang mencatat lembur */
    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    // ── Scope ────────────────────────────────────────────────────────────────

    public function scopePeriode($query, string $awal, string $akhir)
    {
        return $query->whereBetween('tanggal', [$awal, $akhir]);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** Format upah lembur ke Rupiah */
    public function getUpahFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->upah_lembur, 0, ',', '.');
    }
}
