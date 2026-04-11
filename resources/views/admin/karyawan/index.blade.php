@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')

<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h6 class="mb-0" style="font-weight:600;">Daftar Karyawan</h6>
        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-body border-bottom py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nama karyawan..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="divisi_id" class="form-select form-select-sm">
                    <option value="">Semua Divisi</option>
                    @foreach($divisiList as $div)
                        <option value="{{ $div->id }}" {{ request('divisi_id') == $div->id ? 'selected' : '' }}>
                            {{ $div->nama_divisi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Karyawan</th>
                        <th>Email</th>
                        <th>Divisi</th>
                        <th>Gaji Harian</th>
                        <th>No. HP</th>
                        <th>Wajah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawanList as $i => $kar)
                        <tr>
                            <td class="text-muted small">{{ $karyawanList->firstItem() + $i }}</td>
                            <td style="font-weight:500;">{{ $kar->nama }}</td>
                            <td class="text-muted small">{{ $kar->user->email }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $kar->divisi->nama_divisi }}</span>
                            </td>
                            <td>{{ $kar->gaji_harian_format }}</td>
                            <td class="text-muted small">{{ $kar->no_hp ?? '-' }}</td>
                            <td>
                                @if($kar->dataWajah)
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Terdaftar</span>
                                @else
                                    <span class="badge bg-secondary">Belum</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $kar->status === 'aktif' ? 'success' : 'danger' }}">
                                    {{ ucfirst($kar->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.karyawan.show', $kar) }}"
                                       class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.karyawan.edit', $kar) }}"
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="Hapus"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHapus"
                                            data-id="{{ $kar->id }}"
                                            data-nama="{{ $kar->nama }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Tidak ada data karyawan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($karyawanList->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan {{ $karyawanList->firstItem() }}–{{ $karyawanList->lastItem() }}
                dari {{ $karyawanList->total() }} karyawan
            </small>
            {{ $karyawanList->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-600" style="font-weight:600;">Konfirmasi Hapus</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-trash text-danger fs-4"></i>
                </div>
                <p class="mb-1">Hapus karyawan <strong id="namaKaryawan"></strong>?</p>
                <small class="text-muted">Data karyawan, absensi, dan akun login akan ikut terhapus.</small>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <form id="formHapus" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('modalHapus').addEventListener('show.bs.modal', function (e) {
        const btn  = e.relatedTarget;
        const id   = btn.dataset.id;
        const nama = btn.dataset.nama;
        document.getElementById('namaKaryawan').textContent = nama;
        document.getElementById('formHapus').action = `/admin/karyawan/${id}`;
    });
</script>
@endpush
