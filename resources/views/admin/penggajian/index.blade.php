@extends('layouts.admin')

@section('title', 'Penggajian')
@section('page-title', 'Manajemen Penggajian')

@section('content')

<div class="row g-3">
    {{-- Form Generate --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-plus-circle me-2"></i>Generate Penggajian</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.penggajian.generate') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">Tipe Penggajian <span class="text-danger">*</span></label>
                        <select name="tipe" id="tipePenggajian" class="form-select @error('tipe') is-invalid @enderror">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="harian"   {{ old('tipe') === 'harian'   ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ old('tipe') === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="bulanan"  {{ old('tipe') === 'bulanan'  ? 'selected' : '' }}>Bulanan</option>
                        </select>
                        @error('tipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">Periode Awal <span class="text-danger">*</span></label>
                        <input type="date" name="periode_awal" id="periodeAwal"
                               class="form-control @error('periode_awal') is-invalid @enderror"
                               value="{{ old('periode_awal') }}">
                        @error('periode_awal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">Periode Akhir <span class="text-danger">*</span></label>
                        <input type="date" name="periode_akhir" id="periodeAkhir"
                               class="form-control @error('periode_akhir') is-invalid @enderror"
                               value="{{ old('periode_akhir') }}">
                        @error('periode_akhir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"
                                  placeholder="Opsional...">{{ old('keterangan') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-lightning-charge me-1"></i> Generate Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Penggajian --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0" style="font-weight:600;">Riwayat Penggajian</h6>
                <a href="{{ route('admin.penggajian.trashed') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-trash me-1"></i> Sampah
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Periode</th>
                                <th>Karyawan</th>
                                <th>Total Gaji</th>
                                <th>Digenerate</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penggajianList as $pg)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $pg->badge_tipe }}">{{ $pg->label_tipe }}</span>
                                    </td>
                                    <td class="small">{{ $pg->periode_label }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $pg->detailPenggajian->count() }} orang</span>
                                    </td>
                                    <td style="font-weight:500;">
                                        Rp {{ number_format($pg->total_gaji_keseluruhan, 0, ',', '.') }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $pg->tanggal_generate->format('d M Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.penggajian.show', $pg) }}"
                                               class="btn btn-sm btn-outline-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.laporan.penggajian.pdf', $pg) }}"
                                               class="btn btn-sm btn-outline-success" title="Download PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="{{ $pg->id }}"
                                                    data-periode="{{ $pg->periode_label }}"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-cash-stack fs-3 d-block mb-2"></i>
                                        Belum ada data penggajian
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($penggajianList->hasPages())
                <div class="card-footer">{{ $penggajianList->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Hapus dengan Konfirmasi Ketik --}}
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="font-weight:600;">Hapus Penggajian</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formHapus" method="POST">
                @csrf @method('DELETE')
                <div class="modal-body text-center">
                    <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <p class="mb-1">Hapus penggajian periode <strong id="periodePg"></strong>?</p>
                    <small class="text-muted d-block mb-3">Data akan dipindahkan ke sampah dan bisa dipulihkan.</small>
                    <div class="text-start">
                        <label class="form-label small" style="font-weight:500;">
                            Ketik <code>HAPUS</code> untuk konfirmasi:
                        </label>
                        <input type="text" name="konfirmasi" class="form-control" placeholder="HAPUS">
                        @error('konfirmasi')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-isi periode berdasarkan tipe
    document.getElementById('tipePenggajian').addEventListener('change', function () {
        const tipe = this.value;
        const awal  = document.getElementById('periodeAwal');
        const akhir = document.getElementById('periodeAkhir');
        const now   = new Date();
        const y = now.getFullYear(), m = now.getMonth(), d = now.getDate();

        if (tipe === 'harian') {
            const tgl = now.toISOString().split('T')[0];
            awal.value  = tgl;
            akhir.value = tgl;
        } else if (tipe === 'mingguan') {
            const day   = now.getDay();
            const mon   = new Date(now); mon.setDate(d - (day === 0 ? 6 : day - 1));
            const sun   = new Date(mon); sun.setDate(mon.getDate() + 6);
            awal.value  = mon.toISOString().split('T')[0];
            akhir.value = sun.toISOString().split('T')[0];
        } else if (tipe === 'bulanan') {
            awal.value  = `${y}-${String(m + 1).padStart(2, '0')}-01`;
            akhir.value = new Date(y, m + 1, 0).toISOString().split('T')[0];
        }
    });

    document.getElementById('modalHapus').addEventListener('show.bs.modal', e => {
        const btn = e.relatedTarget;
        document.getElementById('periodePg').textContent = btn.dataset.periode;
        document.getElementById('formHapus').action = `/admin/penggajian/${btn.dataset.id}`;
    });
</script>
@endpush
