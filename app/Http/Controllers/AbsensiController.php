<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\DataWajah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    //  ADMIN — Input Absensi Manual (Checklist)
    // ═══════════════════════════════════════════════════════════════════════

    /** Halaman input absensi manual per tanggal */
    public function inputManual(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));

        $karyawanList = Karyawan::with(['divisi', 'absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])->where('status', 'aktif')->orderBy('nama')->get();

        return view('admin.absensi.manual', compact('karyawanList', 'tanggal'));
    }

    /** Simpan absensi manual (checklist) */
    public function simpanManual(Request $request)
    {
        $request->validate([
            'tanggal'   => 'required|date',
            'absensi'   => 'nullable|array',
            'absensi.*' => 'in:hadir,tidak_hadir,izin,sakit',
        ]);

        $tanggal      = $request->tanggal;
        $dataAbsensi  = $request->input('absensi', []);
        $karyawanList = Karyawan::where('status', 'aktif')->pluck('id');

        foreach ($karyawanList as $karyawanId) {
            $status = $dataAbsensi[$karyawanId] ?? 'tidak_hadir';

            Absensi::updateOrCreate(
                ['karyawan_id' => $karyawanId, 'tanggal' => $tanggal],
                ['status' => $status, 'sumber' => 'manual']
            );
        }

        return back()->with('success', "Absensi tanggal {$tanggal} berhasil disimpan.");
    }

    /** Daftar absensi semua karyawan (admin view) */
    public function indexAdmin(Request $request)
    {
        $bulan  = $request->input('bulan', Carbon::now()->month);
        $tahun  = $request->input('tahun', Carbon::now()->year);
        $search = $request->input('search');

        $query = Absensi::with('karyawan.divisi')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($search) {
            $query->whereHas('karyawan', fn($q) => $q->where('nama', 'like', "%$search%"));
        }

        $absensiList = $query->orderByDesc('tanggal')->paginate(20)->withQueryString();

        // Statistik bulan ini
        $statistik = [
            'hadir'       => Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'hadir')->count(),
            'tidak_hadir' => Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'tidak_hadir')->count(),
            'izin'        => Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'izin')->count(),
            'sakit'       => Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'sakit')->count(),
        ];

        return view('admin.absensi.index', compact('absensiList', 'bulan', 'tahun', 'statistik'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  KARYAWAN — Presensi Face Recognition
    // ═══════════════════════════════════════════════════════════════════════

    /** Halaman presensi dengan kamera */
    public function presensi()
    {
        $karyawan       = Auth::user()->karyawan;
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $sudahDaftarWajah = $karyawan->sudahDaftarWajah();

        return view('karyawan.presensi', compact('karyawan', 'absensiHariIni', 'sudahDaftarWajah'));
    }

    /** API: ambil semua face encoding untuk pencocokan di browser */
    public function getFaceEncodings()
    {
        $data = DataWajah::with('karyawan:id,nama')
            ->get()
            ->map(fn($dw) => [
                'karyawan_id'   => $dw->karyawan_id,
                'nama'          => $dw->karyawan->nama,
                'face_encoding' => $dw->face_encoding,
            ]);

        return response()->json($data);
    }

    /** API: simpan hasil presensi face recognition dari browser */
    public function simpanFace(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'jam_masuk'   => 'nullable|date_format:H:i:s',
        ]);

        $today = Carbon::today()->format('Y-m-d');

        // Cegah presensi ganda
        $existing = Absensi::where('karyawan_id', $request->karyawan_id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Anda sudah melakukan presensi hari ini.'], 422);
        }

        $absensi = Absensi::create([
            'karyawan_id' => $request->karyawan_id,
            'tanggal'     => $today,
            'status'      => 'hadir',
            'sumber'      => 'face',
            'jam_masuk'   => $request->jam_masuk ?? now()->format('H:i:s'),
        ]);

        return response()->json([
            'message' => 'Presensi berhasil dicatat.',
            'data'    => $absensi,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  KARYAWAN — Registrasi Wajah
    // ═══════════════════════════════════════════════════════════════════════

    /** Halaman registrasi wajah */
    public function registrasiWajah()
    {
        $karyawan = Auth::user()->karyawan;
        $dataWajah = $karyawan->dataWajah;
        return view('karyawan.registrasi-wajah', compact('karyawan', 'dataWajah'));
    }

    /** API: simpan data wajah (foto + encoding) dari browser */
    public function simpanWajah(Request $request)
    {
        $request->validate([
            'foto_wajah'    => 'required|string',    // base64 image
            'face_encoding' => 'required|array',
        ]);

        $karyawan = Auth::user()->karyawan;

        // Decode dan simpan foto
        $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
        $gambar  = base64_decode($base64);
        $namaFile = 'wajah/' . $karyawan->id . '_' . time() . '.jpg';
        \Storage::disk('public')->put($namaFile, $gambar);

        // Hapus foto lama jika ada
        if ($karyawan->dataWajah && $karyawan->dataWajah->foto_wajah) {
            \Storage::disk('public')->delete($karyawan->dataWajah->foto_wajah);
        }

        // Simpan atau update data wajah
        DataWajah::updateOrCreate(
            ['karyawan_id' => $karyawan->id],
            [
                'foto_wajah'    => $namaFile,
                'face_encoding' => $request->face_encoding,
            ]
        );

        return response()->json(['message' => 'Data wajah berhasil disimpan.']);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  KARYAWAN — Riwayat Absensi Pribadi
    // ═══════════════════════════════════════════════════════════════════════

    public function riwayat(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        $bulan    = $request->input('bulan', Carbon::now()->month);
        $tahun    = $request->input('tahun', Carbon::now()->year);

        $absensiList = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        $rekap = [
            'hadir'       => $absensiList->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensiList->where('status', 'tidak_hadir')->count(),
            'izin'        => $absensiList->where('status', 'izin')->count(),
            'sakit'       => $absensiList->where('status', 'sakit')->count(),
        ];

        return view('karyawan.absensi.riwayat', compact('absensiList', 'rekap', 'bulan', 'tahun'));
    }
}
