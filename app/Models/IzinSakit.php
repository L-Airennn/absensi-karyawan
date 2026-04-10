<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinSakit extends Model
{
    use HasFactory;

    protected $table = 'izin_sakit';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jenis',
        'keterangan',
        'bukti',
        'status',
        'diproses_oleh',
        'diproses_pada',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'diproses_pada' => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Izin/sakit dimiliki oleh 1 karyawan */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /** Admin yang memproses pengajuan ini */
    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    // ── Scope ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    public function getBadgeStatusAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'warning',
            'disetujui'  => 'success',
            'ditolak'    => 'danger',
            default      => 'secondary',
        };
    }

    public function getLabelJenisAttribute(): string
    {
        return match ($this->jenis) {
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            default => '-',
        };
    }

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti ? asset('storage/' . $this->bukti) : null;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
