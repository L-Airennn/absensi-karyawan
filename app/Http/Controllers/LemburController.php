<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LemburController extends Controller
{
    /** Daftar semua lembur */
    public function index(Request $request)
    {
        $query = Lembur::with('karyawan.divisi');

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('search')) {
            $query->whereHas('karyawan', fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'));
        }

        $lemburList   = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();
        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        return view('admin.lembur.index', compact('lemburList', 'karyawanList'));
    }

    /** Form tambah lembur */
    public function create()
    {
        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.lembur.create', compact('karyawanList'));
    }

    /** Simpan lembur */
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal'     => 'required|date',
            'jam_lembur'  => 'required|numeric|min:0.5|max:12',
            'keterangan'  => 'nullable|string|max:255',
        ], [
            'jam_lembur.min' => 'Minimal lembur 0.5 jam.',
            'jam_lembur.max' => 'Maksimal lembur 12 jam.',
        ]);

        $karyawan   = Karyawan::findOrFail($request->karyawan_id);
        $upahLembur = $this->hitungUpahLembur($karyawan->gaji_harian, $request->jam_lembur);

        Lembur::create([
            'karyawan_id'  => $request->karyawan_id,
            'tanggal'      => $request->tanggal,
            'jam_lembur'   => $request->jam_lembur,
            'upah_lembur'  => $upahLembur,
            'keterangan'   => $request->keterangan,
            'dicatat_oleh' => Auth::id(),
        ]);

        return redirect()->route('admin.lembur.index')
            ->with('success', 'Data lembur berhasil disimpan.');
    }

    /** Form edit lembur */
    public function edit(Lembur $lembur)
    {
        $karyawanList = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.lembur.edit', compact('lembur', 'karyawanList'));
    }

    /** Update lembur */
    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawan,id',
            'tanggal'     => 'required|date',
            'jam_lembur'  => 'required|numeric|min:0.5|max:12',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        $karyawan   = Karyawan::findOrFail($request->karyawan_id);
        $upahLembur = $this->hitungUpahLembur($karyawan->gaji_harian, $request->jam_lembur);

        $lembur->update([
            'karyawan_id' => $request->karyawan_id,
            'tanggal'     => $request->tanggal,
            'jam_lembur'  => $request->jam_lembur,
            'upah_lembur' => $upahLembur,
            'keterangan'  => $request->keterangan,
        ]);

        return redirect()->route('admin.lembur.index')
            ->with('success', 'Data lembur berhasil diperbarui.');
    }

    /** Hapus lembur */
    public function destroy(Lembur $lembur)
    {
        $lembur->delete();
        return back()->with('success', 'Data lembur berhasil dihapus.');
    }

    /**
     * Hitung upah lembur.
     * Rumus: (gaji_harian / 8 jam) × 1.5 × jam_lembur
     */
    private function hitungUpahLembur(float $gajiHarian, float $jamLembur): float
    {
        $tarifPerJam = $gajiHarian / 8;
        return round($tarifPerJam * 1.5 * $jamLembur, 2);
    }

    /** API: hitung otomatis upah lembur (dipanggil dari form JS) */
    public function hitungUpah(Request $request)
    {
        $karyawan = Karyawan::findOrFail($request->karyawan_id);
        $upah     = $this->hitungUpahLembur($karyawan->gaji_harian, $request->jam_lembur ?? 0);

        return response()->json(['upah_lembur' => $upah]);
    }
}
