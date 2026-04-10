<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\DetailPenggajian;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /** Halaman pilih laporan */
    public function index()
    {
        return view('admin.laporan.index');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  Laporan Absensi Bulanan
    // ═══════════════════════════════════════════════════════════════════════

    /** Tampilkan laporan absensi bulanan di browser */
    public function absensi(Request $request)
    {
        $bulan  = $request->input('bulan', Carbon::now()->month);
        $tahun  = $request->input('tahun', Carbon::now()->year);

        $periodeAwal  = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $periodeAkhir = $periodeAwal->copy()->endOfMonth();
        $jumlahHari   = $periodeAwal->daysInMonth;

        // Semua karyawan aktif beserta absensi bulan ini
        $karyawanList = Karyawan::with(['divisi', 'absensi' => function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }])->where('status', 'aktif')->orderBy('nama')->get();

        // Mapping karyawan → array tanggal → status
        $dataAbsensi = [];
        foreach ($karyawanList as $kar) {
            $row = [];
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $tgl = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $ab  = $kar->absensi->firstWhere('tanggal', $tgl);
                $row[$d] = $ab ? $ab->status : null;
            }
            $dataAbsensi[$kar->id] = $row;
        }

        return view('admin.laporan.absensi', compact(
            'karyawanList',
            'dataAbsensi',
            'bulan',
            'tahun',
            'jumlahHari',
            'periodeAwal',
            'periodeAkhir'
        ));
    }

    /** Export laporan absensi ke PDF */
    public function absensiPdf(Request $request)
    {
        $bulan  = $request->input('bulan', Carbon::now()->month);
        $tahun  = $request->input('tahun', Carbon::now()->year);

        $periodeAwal  = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $periodeAkhir = $periodeAwal->copy()->endOfMonth();
        $jumlahHari   = $periodeAwal->daysInMonth;

        $karyawanList = Karyawan::with(['divisi', 'absensi' => function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }])->where('status', 'aktif')->orderBy('nama')->get();

        $dataAbsensi = [];
        foreach ($karyawanList as $kar) {
            $row = [];
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $tgl = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $ab  = $kar->absensi->firstWhere('tanggal', $tgl);
                $row[$d] = $ab ? $ab->status : null;
            }
            $dataAbsensi[$kar->id] = $row;
        }

        $pdf = Pdf::loadView('admin.laporan.absensi-pdf', compact(
            'karyawanList', 'dataAbsensi', 'bulan', 'tahun', 'jumlahHari', 'periodeAwal', 'periodeAkhir'
        ))->setPaper('a4', 'landscape');

        $namaFile = 'laporan-absensi-' . $periodeAwal->format('Y-m') . '.pdf';

        return $pdf->download($namaFile);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  Laporan Penggajian Bulanan
    // ═══════════════════════════════════════════════════════════════════════

    /** Tampilkan laporan penggajian di browser */
    public function penggajian(Request $request)
    {
        $penggajianId = $request->input('penggajian_id');

        // Dropdown pilihan penggajian
        $penggajianOptions = Penggajian::orderByDesc('periode_awal')->get();

        $penggajian    = null;
        $detailList    = collect();
        $totalKeseluruhan = 0;

        if ($penggajianId) {
            $penggajian = Penggajian::with(['detailPenggajian.karyawan.divisi', 'generator'])
                ->findOrFail($penggajianId);

            $detailList       = $penggajian->detailPenggajian->sortBy('karyawan.nama');
            $totalKeseluruhan = $detailList->sum('total_gaji');
        }

        return view('admin.laporan.penggajian', compact(
            'penggajianOptions',
            'penggajian',
            'detailList',
            'totalKeseluruhan'
        ));
    }

    /** Export laporan penggajian ke PDF */
    public function penggajianPdf(Penggajian $penggajian)
    {
        $penggajian->load(['detailPenggajian.karyawan.divisi', 'generator']);

        $detailList       = $penggajian->detailPenggajian->sortBy('karyawan.nama');
        $totalKeseluruhan = $detailList->sum('total_gaji');

        $pdf = Pdf::loadView('admin.laporan.penggajian-pdf', compact(
            'penggajian', 'detailList', 'totalKeseluruhan'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'slip-gaji-' . $penggajian->periode_awal->format('Y-m') . '.pdf';

        return $pdf->download($namaFile);
    }

    /** Export slip gaji per karyawan ke PDF */
    public function slipPdf(DetailPenggajian $detailPenggajian)
    {
        $detailPenggajian->load(['penggajian', 'karyawan.divisi']);

        $pdf = Pdf::loadView('admin.laporan.slip-pdf', compact('detailPenggajian'))
            ->setPaper([0, 0, 595, 420], 'portrait'); // ukuran setengah A4

        $namaFile = 'slip-' . $detailPenggajian->karyawan->nama . '-' . $detailPenggajian->penggajian->periode_awal->format('Y-m') . '.pdf';

        return $pdf->download($namaFile);
    }
}
