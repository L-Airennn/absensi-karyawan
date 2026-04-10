<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\IzinSakit;
use App\Models\Penggajian;
use App\Models\Lembur;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /** Dashboard Admin */
    public function admin()
    {
        $today  = Carbon::today();
        $bulan  = $today->month;
        $tahun  = $today->year;

        // Statistik cepat
        $totalKaryawan      = Karyawan::where('status', 'aktif')->count();
        $hadirHariIni       = Absensi::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $izinPending        = IzinSakit::where('status', 'pending')->count();
        $penggajianBulanIni = Penggajian::whereMonth('tanggal_generate', $bulan)
                                         ->whereYear('tanggal_generate', $tahun)
                                         ->count();

        // Absensi hari ini (untuk tabel ringkasan)
        $absensiHariIni = Absensi::with('karyawan.divisi')
            ->whereDate('tanggal', $today)
            ->latest()
            ->take(10)
            ->get();

        // Izin/sakit pending terbaru
        $izinTerbaru = IzinSakit::with('karyawan')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Data grafik kehadiran 7 hari terakhir
        $grafikHadir = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);
            $grafikHadir[] = [
                'tanggal' => $tgl->format('d/m'),
                'hadir'   => Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count(),
                'tidak'   => Absensi::whereDate('tanggal', $tgl)->where('status', 'tidak_hadir')->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'izinPending',
            'penggajianBulanIni',
            'absensiHariIni',
            'izinTerbaru',
            'grafikHadir',
            'today'
        ));
    }

    /** Dashboard Karyawan */
    public function karyawan()
    {
        $user      = Auth::user();
        $karyawan  = $user->karyawan;
        $today     = Carbon::today();
        $bulan     = $today->month;
        $tahun     = $today->year;

        // Absensi hari ini
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Rekap bulan ini
        $totalHadirBulanIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'hadir')
            ->count();

        $totalIzinBulanIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('status', ['izin', 'sakit'])
            ->count();

        $totalLemburBulanIni = Lembur::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('jam_lembur');

        // 5 absensi terakhir
        $absensiTerakhir = Absensi::where('karyawan_id', $karyawan->id)
            ->orderByDesc('tanggal')
            ->take(5)
            ->get();

        // Izin/sakit terbaru
        $izinTerbaru = IzinSakit::where('karyawan_id', $karyawan->id)
            ->latest()
            ->take(3)
            ->get();

        // Estimasi gaji bulan ini
        $periodeAwal  = $today->copy()->startOfMonth()->format('Y-m-d');
        $periodeAkhir = $today->format('Y-m-d');
        $estimasiGaji = $karyawan->hitungTotalGaji($periodeAwal, $periodeAkhir);

        return view('karyawan.dashboard', compact(
            'karyawan',
            'absensiHariIni',
            'totalHadirBulanIni',
            'totalIzinBulanIni',
            'totalLemburBulanIni',
            'absensiTerakhir',
            'izinTerbaru',
            'estimasiGaji',
            'today'
        ));
    }
}
