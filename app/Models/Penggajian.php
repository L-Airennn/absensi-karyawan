<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penggajian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penggajian';

    protected $fillable = [
        'tipe',
        'periode_awal',
        'periode_akhir',
        'tanggal_generate',
        'digenerate_oleh',
        'keterangan',
    ];

    protected $casts = [
        'periode_awal'     => 'date',
        'periode_akhir'    => 'date',
        'tanggal_generate' => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Penggajian memiliki banyak detail (per karyawan) */
    public function detailPenggajian()
    {
        return $this->hasMany(DetailPenggajian::class);
    }

    /** Admin yang meng-generate penggajian ini */
    public function generator()
    {
        return $this->belongsTo(User::class, 'digenerate_oleh');
    }

    // ── Scope ────────────────────────────────────────────────────────────────

    public function scopeBulanan($query)
    {
        return $query->where('tipe', 'bulanan');
    }

    public function scopeMingguan($query)
    {
        return $query->where('tipe', 'mingguan');
    }

    public function scopeHarian($query)
    {
        return $query->where('tipe', 'harian');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** Total seluruh gaji dalam 1 periode penggajian */
    public function getTotalGajiKeseluruhanAttribute(): float
    {
        return $this->detailPenggajian()->sum('total_gaji');
    }

    /** Format periode: "01 Jan 2025 – 31 Jan 2025" */
    public function getPeriodeLabelAttribute(): string
    {
        return $this->periode_awal->translatedFormat('d M Y')
            . ' – '
            . $this->periode_akhir->translatedFormat('d M Y');
    }

    public function getLabelTipeAttribute(): string
    {
        return match ($this->tipe) {
            'harian'   => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
            default    => '-',
        };
    }

    public function getBadgeTipeAttribute(): string
    {
        return match ($this->tipe) {
            'harian'   => 'info',
            'mingguan' => 'warning',
            'bulanan'  => 'primary',
            default    => 'secondary',
        };
    }
}
