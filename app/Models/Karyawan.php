<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'user_id',
        'nama',
        'divisi_id',
        'gaji_harian',
        'no_hp',
        'status',
    ];

    protected $casts = [
        'gaji_harian' => 'decimal:2',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    /** Karyawan dimiliki oleh 1 user */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Karyawan tergabung dalam 1 divisi */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    /** Karyawan memiliki banyak data absensi */
    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    /** Karyawan memiliki 1 data wajah (face encoding) */
    public function dataWajah()
    {
        return $this->hasOne(DataWajah::class);
    }

    /** Karyawan memiliki banyak pengajuan izin/sakit */
    public function izinSakit()
    {
        return $this->hasMany(IzinSakit::class);
    }

    /** Karyawan memiliki banyak data lembur */
    public function lembur()
    {
        return $this->hasMany(Lembur::class);
    }

    /** Karyawan memiliki banyak detail penggajian */
    public function detailPenggajian()
    {
        return $this->hasMany(DetailPenggajian::class);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /** Cek apakah karyawan sudah mendaftarkan wajah */
    public function sudahDaftarWajah(): bool
    {
        return $this->dataWajah()->exists();
    }

    /** Hitung total hadir dalam rentang tanggal tertentu */
    public function totalHadir(string $periodeAwal, string $periodeAkhir): int
    {
        return $this->absensi()
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->where('status', 'hadir')
            ->count();
    }

    /** Hitung total upah lembur dalam rentang tanggal tertentu */
    public function totalUpahLembur(string $periodeAwal, string $periodeAkhir): float
    {
        return $this->lembur()
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->sum('upah_lembur');
    }

    /** Hitung total gaji: (hadir × gaji_harian) + upah lembur */
    public function hitungTotalGaji(string $periodeAwal, string $periodeAkhir): float
    {
        $totalHadir     = $this->totalHadir($periodeAwal, $periodeAkhir);
        $upahLembur     = $this->totalUpahLembur($periodeAwal, $periodeAkhir);
        $gajiPokok      = $totalHadir * $this->gaji_harian;

        return $gajiPokok + $upahLembur;
    }

    /** Format gaji harian ke Rupiah */
    public function getGajiHarianFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji_harian, 0, ',', '.');
    }
}
