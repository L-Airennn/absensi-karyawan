<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Karyawan;

class DatabaseSeeder extends Seeder
{
    /**
     * Isi database dengan data awal.
     */
    public function run(): void
    {
        // ── 1. Buat akun Admin ───────────────────────────────────────────────
        $admin = User::create([
            'nama'     => 'Administrator',
            'email'    => 'admin@ruslan-jaya.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // ── 2. Buat Divisi awal ──────────────────────────────────────────────
        $divisiData = [
            ['nama_divisi' => 'Produksi Mie Ayam'],
            ['nama_divisi' => 'Produksi Pangsit'],
            ['nama_divisi' => 'Pengemasan'],
            ['nama_divisi' => 'Pengiriman'],
            ['nama_divisi' => 'Administrasi'],
        ];

        foreach ($divisiData as $divisi) {
            Divisi::create($divisi);
        }

        // ── 3. Buat 1 akun Karyawan contoh ──────────────────────────────────
        $userKaryawan = User::create([
            'nama'     => 'Budi Santoso',
            'email'    => 'budi@ruslan-jaya.com',
            'password' => Hash::make('karyawan123'),
            'role'     => 'karyawan',
        ]);

        Karyawan::create([
            'user_id'     => $userKaryawan->id,
            'nama'        => 'Budi Santoso',
            'divisi_id'   => 1, // Produksi Mie Ayam
            'gaji_harian' => 100000,
            'no_hp'       => '081234567890',
            'status'      => 'aktif',
        ]);
    }
}