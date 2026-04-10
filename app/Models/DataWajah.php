<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataWajah extends Model
{
    use HasFactory;

    protected $table = 'data_wajah';

    protected $fillable = [
        'karyawan_id',
        'foto_wajah',
        'face_encoding',
    ];

    protected $casts = [
        'face_encoding' => 'array',   // otomatis encode/decode JSON
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Data wajah dimiliki oleh 1 karyawan */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** URL foto wajah untuk ditampilkan di blade */
    public function getFotoUrlAttribute(): string
    {
        return asset('storage/' . $this->foto_wajah);
    }
}
