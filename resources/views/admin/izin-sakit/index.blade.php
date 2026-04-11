@extends('layouts.admin')

@section('title', 'Izin & Sakit')
@section('page-title', 'Manajemen Izin & Sakit')

@section('content')

@if($totalPending > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Terdapat <strong>{{ $totalPending }} pengajuan</strong> yang menunggu persetujuan Anda.
    </div>
@endif

<div class="card">
    <div class="card-header">
        {{-- Filter --}}
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nama karyawan..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-2">
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="izin"  {{ request('jenis') === 'izin'  ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status') === 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('admin.izin-sakit.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($izinList as $i => $izin)
                        <tr>
                            <td class="text-muted small">{{ $izinList->firstItem() + $i }}</td>
                            <td>
                                <div style="font-weight:500;">{{ $izin->karyawan->nama }}</div>
                                <small class="text-muted">{{ $izin->karyawan->divisi->nama_divisi }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $izin->jenis === 'sakit' ? 'info' : 'warning text-dark' }}">
                                    {{ $izin->label_jenis }}
                                </span>
                            </td>
                            <td>{{ $izin->tanggal->format('d M Y') }}</td>
                            <td class="small" style="max-width:200px;">
                                <span title="{{ $izin->keterangan }}">
                                    {{ \Str::limit($izin->keterangan, 50) }}
                                </span>
                            </td>
                            <td>
                                @if($izin->bukti_url)
                                    <a href="{{ $izin->bukti_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-paperclip"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $izin->badge_status }}">
                                    {{ ucfirst($izin->status) }}
                                </span>
                            </td>
                            <td>
                                @if($izin->isPending())
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-success"
                                                data-bs-toggle="modal" data-bs-target="#modalApprove"
                                                data-id="{{ $izin->id }}"
                                                data-nama="{{ $izin->karyawan->nama }}"
                                                title="Setujui">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalReject"
                                                data-id="{{ $izin->id }}"
                                                data-nama="{{ $izin->karyawan->nama }}"
                                                title="Tolak">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        {{ $izin->diproses_pada?->format('d M Y') ?? '-' }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data pengajuan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($izinList->hasPages())
        <div class="card-footer">
            {{ $izinList->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- Modal Approve --}}
<div class="modal fade" id="modalApprove" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="font-weight:600;">Setujui Pengajuan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formApprove" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-2">Setujui pengajuan dari <strong id="namaApprove"></strong>?</p>
                    <label class="form-label small" style="font-weight:500;">Catatan (opsional)</label>
                    <textarea name="catatan_admin" class="form-control" rows="2"
                              placeholder="Catatan untuk karyawan..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="font-weight:600;">Tolak Pengajuan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReject" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-2">Tolak pengajuan dari <strong id="namaReject"></strong>?</p>
                    <label class="form-label small" style="font-weight:500;">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan_admin" class="form-control" rows="2"
                              placeholder="Berikan alasan penolakan..." required></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-x-lg me-1"></i>Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('modalApprove').addEventListener('show.bs.modal', e => {
        const btn = e.relatedTarget;
        document.getElementById('namaApprove').textContent = btn.dataset.nama;
        document.getElementById('formApprove').action = `/admin/izin-sakit/${btn.dataset.id}/approve`;
    });

    document.getElementById('modalReject').addEventListener('show.bs.modal', e => {
        const btn = e.relatedTarget;
        document.getElementById('namaReject').textContent = btn.dataset.nama;
        document.getElementById('formReject').action = `/admin/izin-sakit/${btn.dataset.id}/reject`;
    });
</script>
@endpush
