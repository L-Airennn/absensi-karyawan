<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /** Daftar semua karyawan */
    public function index(Request $request)
    {
        $query = Karyawan::with(['user', 'divisi']);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $karyawanList = $query->orderBy('nama')->paginate(10)->withQueryString();
        $divisiList   = Divisi::orderBy('nama_divisi')->get();

        return view('admin.karyawan.index', compact('karyawanList', 'divisiList'));
    }

    /** Form tambah karyawan */
    public function create()
    {
        $divisiList = Divisi::orderBy('nama_divisi')->get();
        return view('admin.karyawan.create', compact('divisiList'));
    }

    /** Simpan karyawan baru */
    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6|confirmed',
            'divisi_id'   => 'required|exists:divisi,id',
            'gaji_harian' => 'required|numeric|min:0',
            'no_hp'       => 'nullable|string|max:20',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama.required'        => 'Nama wajib diisi.',
            'email.required'       => 'Email wajib diisi.',
            'email.unique'         => 'Email sudah digunakan.',
            'password.required'    => 'Password wajib diisi.',
            'password.confirmed'   => 'Konfirmasi password tidak cocok.',
            'divisi_id.required'   => 'Divisi wajib dipilih.',
            'gaji_harian.required' => 'Gaji harian wajib diisi.',
        ]);

        DB::transaction(function () use ($request) {
            // Buat akun user
            $user = User::create([
                'nama'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'karyawan',
            ]);

            // Buat data karyawan
            Karyawan::create([
                'user_id'     => $user->id,
                'nama'        => $request->nama,
                'divisi_id'   => $request->divisi_id,
                'gaji_harian' => $request->gaji_harian,
                'no_hp'       => $request->no_hp,
                'status'      => $request->status,
            ]);
        });

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /** Detail karyawan */
    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['user', 'divisi', 'dataWajah']);
        return view('admin.karyawan.show', compact('karyawan'));
    }

    /** Form edit karyawan */
    public function edit(Karyawan $karyawan)
    {
        $divisiList = Divisi::orderBy('nama_divisi')->get();
        return view('admin.karyawan.edit', compact('karyawan', 'divisiList'));
    }

    /** Update karyawan */
    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nama'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email,' . $karyawan->user_id,
            'password'    => 'nullable|min:6|confirmed',
            'divisi_id'   => 'required|exists:divisi,id',
            'gaji_harian' => 'required|numeric|min:0',
            'no_hp'       => 'nullable|string|max:20',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        DB::transaction(function () use ($request, $karyawan) {
            // Update user
            $dataUser = [
                'nama'  => $request->nama,
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $dataUser['password'] = Hash::make($request->password);
            }
            $karyawan->user->update($dataUser);

            // Update karyawan
            $karyawan->update([
                'nama'        => $request->nama,
                'divisi_id'   => $request->divisi_id,
                'gaji_harian' => $request->gaji_harian,
                'no_hp'       => $request->no_hp,
                'status'      => $request->status,
            ]);
        });

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /** Hapus karyawan */
    public function destroy(Karyawan $karyawan)
    {
        DB::transaction(function () use ($karyawan) {
            $karyawan->user->delete(); // cascade ke karyawan
        });

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    // ── CRUD Divisi ──────────────────────────────────────────────────────────

    public function divisiIndex()
    {
        $divisiList = Divisi::withCount('karyawan')->orderBy('nama_divisi')->get();
        return view('admin.divisi.index', compact('divisiList'));
    }

    public function divisiStore(Request $request)
    {
        $request->validate(['nama_divisi' => 'required|string|max:100|unique:divisi,nama_divisi']);
        Divisi::create(['nama_divisi' => $request->nama_divisi]);

        return back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function divisiUpdate(Request $request, Divisi $divisi)
    {
        $request->validate([
            'nama_divisi' => 'required|string|max:100|unique:divisi,nama_divisi,' . $divisi->id,
        ]);
        $divisi->update(['nama_divisi' => $request->nama_divisi]);

        return back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function divisiDestroy(Divisi $divisi)
    {
        if ($divisi->karyawan()->exists()) {
            return back()->with('error', 'Divisi tidak bisa dihapus karena masih memiliki karyawan.');
        }
        $divisi->delete();
        return back()->with('success', 'Divisi berhasil dihapus.');
    }
}
