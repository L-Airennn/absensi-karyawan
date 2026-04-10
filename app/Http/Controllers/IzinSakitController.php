<?php

namespace App\Http\Controllers;

use App\Models\IzinSakit;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IzinSakitController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    //  ADMIN
    // ═══════════════════════════════════════════════════════════════════════

    /** Daftar semua pengajuan izin/sakit (admin) */
    public function indexAdmin(Request $request)
    {
        $query = IzinSakit::with('karyawan.divisi');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('search')) {
            $query->whereHas('karyawan', fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'));
        }

        $izinList    = $query->latest()->paginate(15)->withQueryString();
        $totalPending = IzinSakit::where('status', 'pending')->count();

        return view('admin.izin-sakit.index', compact('izinList', 'totalPending'));
    }

    /** Approve pengajuan */
    public function approve(Request $request, IzinSakit $izinSakit)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $izinSakit->update([
            'status'        => 'disetujui',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
            'catatan_admin' => $request->catatan_admin,
        ]);

        // Otomatis update status absensi jadi izin/sakit
        Absensi::updateOrCreate(
            ['karyawan_id' => $izinSakit->karyawan_id, 'tanggal' => $izinSakit->tanggal],
            ['status' => $izinSakit->jenis, 'sumber' => 'manual']
        );

        return back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    /** Reject pengajuan */
    public function reject(Request $request, IzinSakit $izinSakit)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:255',
        ]);

        $izinSakit->update([
            'status'        => 'ditolak',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  KARYAWAN
    // ═══════════════════════════════════════════════════════════════════════

    /** Daftar izin/sakit milik karyawan yang login */
    public function indexKaryawan(Request $request)
    {
        $karyawan = Auth::user()->karyawan;

        $izinList = IzinSakit::where('karyawan_id', $karyawan->id)
            ->latest()
            ->paginate(10);

        return view('karyawan.izin-sakit.index', compact('izinList'));
    }

    /** Form pengajuan izin/sakit */
    public function create()
    {
        return view('karyawan.izin-sakit.create');
    }

    /** Simpan pengajuan izin/sakit */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date|after_or_equal:today',
            'jenis'      => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:500',
            'bukti'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'tanggal.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'bukti.max'              => 'Ukuran file maksimal 2MB.',
        ]);

        $karyawan = Auth::user()->karyawan;

        // Cek apakah sudah ada pengajuan di tanggal yang sama
        $existing = IzinSakit::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $request->tanggal)
            ->whereIn('status', ['pending', 'disetujui'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki pengajuan pada tanggal tersebut.')->withInput();
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-izin', 'public');
        }

        IzinSakit::create([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $request->tanggal,
            'jenis'       => $request->jenis,
            'keterangan'  => $request->keterangan,
            'bukti'       => $buktiPath,
            'status'      => 'pending',
        ]);

        return redirect()->route('karyawan.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim.');
    }

    /** Hapus pengajuan (hanya jika masih pending) */
    public function destroy(IzinSakit $izinSakit)
    {
        $karyawan = Auth::user()->karyawan;

        if ($izinSakit->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        if (!$izinSakit->isPending()) {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak bisa dihapus.');
        }

        if ($izinSakit->bukti) {
            Storage::disk('public')->delete($izinSakit->bukti);
        }

        $izinSakit->delete();

        return back()->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}
