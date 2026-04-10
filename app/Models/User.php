<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** User bisa punya 1 data karyawan */
    public function karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }

    /** Izin/sakit yang diproses oleh user (admin) ini */
    public function izinDiproses()
    {
        return $this->hasMany(IzinSakit::class, 'diproses_oleh');
    }

    /** Lembur yang dicatat oleh user (admin) ini */
    public function lemburDicatat()
    {
        return $this->hasMany(Lembur::class, 'dicatat_oleh');
    }

    /** Penggajian yang digenerate oleh user (admin) ini */
    public function penggajianDigenerate()
    {
        return $this->hasMany(Penggajian::class, 'digenerate_oleh');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }
}
