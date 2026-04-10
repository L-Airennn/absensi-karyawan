<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'status',
        'sumber',
        'jam_masuk',
        'jam_keluar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Absensi dimiliki oleh 1 karyawan */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // ── Scope ────────────────────────────────────────────────────────────────

    /** Filter hanya yang hadir */
    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    /** Filter berdasarkan bulan dan tahun */
    public function scopeBulanIni($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)
                     ->whereYear('tanggal', $tahun);
    }

    /** Filter berdasarkan rentang tanggal */
    public function scopePeriode($query, string $awal, string $akhir)
    {
        return $query->whereBetween('tanggal', [$awal, $akhir]);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** Label badge Bootstrap sesuai status */
    public function getBadgeStatusAttribute(): string
    {
        return match ($this->status) {
            'hadir'        => 'success',
            'tidak_hadir'  => 'danger',
            'izin'         => 'warning',
            'sakit'        => 'info',
            default        => 'secondary',
        };
    }

    /** Label teks status dalam Bahasa Indonesia */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'hadir'        => 'Hadir',
            'tidak_hadir'  => 'Tidak Hadir',
            'izin'         => 'Izin',
            'sakit'        => 'Sakit',
            default        => 'Tidak Diketahui',
        };
    }
}
