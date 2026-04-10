<?php

namespace App\Http\Controllers;

use App\Models\Penggajian;
use App\Models\DetailPenggajian;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    //  ADMIN
    // ═══════════════════════════════════════════════════════════════════════

    /** Daftar penggajian */
    public function index(Request $request)
    {
        $query = Penggajian::with(['generator', 'detailPenggajian']);

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $penggajianList = $query->orderByDesc('tanggal_generate')->paginate(10)->withQueryString();

        return view('admin.penggajian.index', compact('penggajianList'));
    }

    /** Form generate penggajian */
    public function create()
    {
        return view('admin.penggajian.create');
    }

    /** Proses generate penggajian */
    public function generate(Request $request)
    {
        $request->validate([
            'tipe'         => 'required|in:harian,mingguan,bulanan',
            'periode_awal' => 'required|date',
            'periode_akhir'=> 'required|date|after_or_equal:periode_awal',
            'keterangan'   => 'nullable|string|max:255',
        ], [
            'periode_akhir.after_or_equal' => 'Periode akhir harus setelah periode awal.',
        ]);

        $periodeAwal  = $request->periode_awal;
        $periodeAkhir = $request->periode_akhir;

        // Cegah duplikasi periode yang sama
        $existing = Penggajian::where('tipe', $request->tipe)
            ->where('periode_awal', $periodeAwal)
            ->where('periode_akhir', $periodeAkhir)
            ->exists();

        if ($existing) {
            return back()->with('error', 'Penggajian untuk periode ini sudah pernah digenerate. Hapus dulu jika ingin generate ulang.')->withInput();
        }

        DB::transaction(function () use ($request, $periodeAwal, $periodeAkhir) {
            // Buat header penggajian
            $penggajian = Penggajian::create([
                'tipe'              => $request->tipe,
                'periode_awal'      => $periodeAwal,
                'periode_akhir'     => $periodeAkhir,
                'tanggal_generate'  => now(),
                'digenerate_oleh'   => Auth::id(),
                'keterangan'        => $request->keterangan,
            ]);

            // Hitung gaji tiap karyawan aktif
            $karyawanList = Karyawan::where('status', 'aktif')->get();

            foreach ($karyawanList as $karyawan) {
                $totalHadir = Absensi::where('karyawan_id', $karyawan->id)
                    ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                    ->where('status', 'hadir')
                    ->count();

                $lemburData = Lembur::where('karyawan_id', $karyawan->id)
                    ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                    ->selectRaw('SUM(jam_lembur) as total_jam, SUM(upah_lembur) as total_upah')
                    ->first();

                $totalJamLembur  = $lemburData->total_jam  ?? 0;
                $totalUpahLembur = $lemburData->total_upah ?? 0;
                $gajiPokok       = $totalHadir * $karyawan->gaji_harian;
                $totalGaji       = $gajiPokok + $totalUpahLembur;

                DetailPenggajian::create([
                    'penggajian_id'     => $penggajian->id,
                    'karyawan_id'       => $karyawan->id,
                    'total_hadir'       => $totalHadir,
                    'total_jam_lembur'  => $totalJamLembur,
                    'gaji_pokok'        => $gajiPokok,
                    'total_upah_lembur' => $totalUpahLembur,
                    'total_gaji'        => $totalGaji,
                ]);
            }
        });

        return redirect()->route('admin.penggajian.index')
            ->with('success', 'Penggajian berhasil digenerate.');
    }

    /** Detail penggajian */
    public function show(Penggajian $penggajian)
    {
        $penggajian->load(['detailPenggajian.karyawan.divisi', 'generator']);

        $totalKeseluruhan = $penggajian->detailPenggajian->sum('total_gaji');

        return view('admin.penggajian.show', compact('penggajian', 'totalKeseluruhan'));
    }

    /** Hapus penggajian (soft delete) dengan konfirmasi */
    public function destroy(Request $request, Penggajian $penggajian)
    {
        $request->validate([
            'konfirmasi' => 'required|in:HAPUS',
        ], [
            'konfirmasi.in' => 'Ketik HAPUS untuk konfirmasi.',
        ]);

        $penggajian->delete(); // soft delete

        return redirect()->route('admin.penggajian.index')
            ->with('success', 'Penggajian berhasil dihapus. Data dapat direstore jika diperlukan.');
    }

    /** Restore penggajian yang sudah dihapus */
    public function restore($id)
    {
        $penggajian = Penggajian::onlyTrashed()->findOrFail($id);
        $penggajian->restore();

        return redirect()->route('admin.penggajian.index')
            ->with('success', 'Penggajian berhasil dipulihkan.');
    }

    /** Daftar penggajian yang sudah dihapus */
    public function trashed()
    {
        $trashedList = Penggajian::onlyTrashed()
            ->with('generator')
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('admin.penggajian.trashed', compact('trashedList'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  KARYAWAN — Slip Gaji
    // ═══════════════════════════════════════════════════════════════════════

    /** Daftar slip gaji karyawan */
    public function slipGaji(Request $request)
    {
        $karyawan = Auth::user()->karyawan;

        $slipList = DetailPenggajian::with('penggajian')
            ->where('karyawan_id', $karyawan->id)
            ->whereHas('penggajian', fn($q) => $q->whereNull('deleted_at'))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('karyawan.slip-gaji.index', compact('slipList', 'karyawan'));
    }

    /** Detail slip gaji karyawan */
    public function detailSlip(DetailPenggajian $detailPenggajian)
    {
        $karyawan = Auth::user()->karyawan;

        // Pastikan slip ini milik karyawan yang login
        if ($detailPenggajian->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        $detailPenggajian->load(['penggajian', 'karyawan.divisi']);

        return view('karyawan.slip-gaji.detail', compact('detailPenggajian'));
    }
}
