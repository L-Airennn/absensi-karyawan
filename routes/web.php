<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\IzinSakitController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
//  AUTH — Tamu saja (belum login)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/',      [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',[AuthController::class, 'login'])->name('login.post');
});

// Logout (harus sudah login)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ═══════════════════════════════════════════════════════════════════════════
//  ADMIN — Hanya role admin
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // ── Manajemen Karyawan ───────────────────────────────────────────────
    Route::resource('karyawan', KaryawanController::class);

    // ── Manajemen Divisi ─────────────────────────────────────────────────
    Route::prefix('divisi')->as('divisi.')->group(function () {
        Route::get('/',              [KaryawanController::class, 'divisiIndex']) ->name('index');
        Route::post('/',             [KaryawanController::class, 'divisiStore']) ->name('store');
        Route::put('/{divisi}',      [KaryawanController::class, 'divisiUpdate'])->name('update');
        Route::delete('/{divisi}',   [KaryawanController::class, 'divisiDestroy'])->name('destroy');
    });

    // ── Absensi ──────────────────────────────────────────────────────────
    Route::prefix('absensi')->as('absensi.')->group(function () {
        Route::get('/',              [AbsensiController::class, 'indexAdmin'])   ->name('index');
        Route::get('/manual',        [AbsensiController::class, 'inputManual'])  ->name('manual');
        Route::post('/manual',       [AbsensiController::class, 'simpanManual']) ->name('manual.simpan');
    });

    // ── Izin & Sakit ─────────────────────────────────────────────────────
    Route::prefix('izin-sakit')->as('izin-sakit.')->group(function () {
        Route::get('/',                      [IzinSakitController::class, 'indexAdmin'])->name('index');
        Route::post('/{izinSakit}/approve',  [IzinSakitController::class, 'approve'])  ->name('approve');
        Route::post('/{izinSakit}/reject',   [IzinSakitController::class, 'reject'])   ->name('reject');
    });

    // ── Lembur ───────────────────────────────────────────────────────────
    Route::resource('lembur', LemburController::class)->except(['show']);
    Route::get('/lembur/hitung-upah', [LemburController::class, 'hitungUpah'])->name('lembur.hitung-upah');

    // ── Penggajian ───────────────────────────────────────────────────────
    Route::prefix('penggajian')->as('penggajian.')->group(function () {
        Route::get('/',                  [PenggajianController::class, 'index'])  ->name('index');
        Route::get('/buat',              [PenggajianController::class, 'create']) ->name('create');
        Route::post('/generate',         [PenggajianController::class, 'generate'])->name('generate');
        Route::get('/{penggajian}',      [PenggajianController::class, 'show'])   ->name('show');
        Route::delete('/{penggajian}',   [PenggajianController::class, 'destroy'])->name('destroy');
        Route::get('/sampah',            [PenggajianController::class, 'trashed'])->name('trashed');
        Route::post('/{id}/restore',     [PenggajianController::class, 'restore'])->name('restore');
    });

    // ── Laporan ──────────────────────────────────────────────────────────
    Route::prefix('laporan')->as('laporan.')->group(function () {
        Route::get('/',                          [LaporanController::class, 'index'])        ->name('index');
        Route::get('/absensi',                   [LaporanController::class, 'absensi'])      ->name('absensi');
        Route::get('/absensi/pdf',               [LaporanController::class, 'absensiPdf'])   ->name('absensi.pdf');
        Route::get('/penggajian',                [LaporanController::class, 'penggajian'])   ->name('penggajian');
        Route::get('/penggajian/{penggajian}/pdf',[LaporanController::class, 'penggajianPdf'])->name('penggajian.pdf');
        Route::get('/slip/{detailPenggajian}/pdf',[LaporanController::class, 'slipPdf'])     ->name('slip.pdf');
    });
});

// ═══════════════════════════════════════════════════════════════════════════
//  KARYAWAN — Hanya role karyawan
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('karyawan')
    ->as('karyawan.')
    ->middleware(['auth', 'role:karyawan'])
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'karyawan'])->name('dashboard');

    // ── Presensi Face Recognition ────────────────────────────────────────
    Route::get('/presensi',             [AbsensiController::class, 'presensi'])       ->name('presensi');
    Route::post('/presensi/simpan',     [AbsensiController::class, 'simpanFace'])     ->name('presensi.simpan');
    Route::get('/presensi/encodings',   [AbsensiController::class, 'getFaceEncodings'])->name('presensi.encodings');

    // ── Registrasi Wajah ─────────────────────────────────────────────────
    Route::get('/registrasi-wajah',     [AbsensiController::class, 'registrasiWajah'])->name('registrasi-wajah');
    Route::post('/registrasi-wajah',    [AbsensiController::class, 'simpanWajah'])    ->name('registrasi-wajah.simpan');

    // ── Riwayat Absensi ──────────────────────────────────────────────────
    Route::get('/absensi',              [AbsensiController::class, 'riwayat'])        ->name('absensi.riwayat');

    // ── Izin & Sakit ─────────────────────────────────────────────────────
    Route::prefix('izin-sakit')->as('izin-sakit.')->group(function () {
        Route::get('/',          [IzinSakitController::class, 'indexKaryawan'])->name('index');
        Route::get('/buat',      [IzinSakitController::class, 'create'])       ->name('create');
        Route::post('/',         [IzinSakitController::class, 'store'])        ->name('store');
        Route::delete('/{izinSakit}', [IzinSakitController::class, 'destroy'])->name('destroy');
    });

    // ── Slip Gaji ────────────────────────────────────────────────────────
    Route::get('/slip-gaji',                       [PenggajianController::class, 'slipGaji'])   ->name('slip-gaji.index');
    Route::get('/slip-gaji/{detailPenggajian}',    [PenggajianController::class, 'detailSlip']) ->name('slip-gaji.detail');
});
