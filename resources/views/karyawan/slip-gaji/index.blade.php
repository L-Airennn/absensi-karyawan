@extends('layouts.karyawan')

@section('title', 'Slip Gaji')
@section('page-title', 'Slip Gaji')

@section('content')

<div class="card">
    <div class="card-header">
        <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-receipt me-2"></i>Riwayat Slip Gaji</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Tipe</th>
                        <th>Hadir</th>
                        <th>Gaji Pokok</th>
                        <th>Lembur</th>
                        <th>Total Gaji</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slipList as $detail)
                        <tr>
                            <td class="small">{{ $detail->penggajian->periode_label }}</td>
                            <td><span class="badge bg-{{ $detail->penggajian->badge_tipe }}">{{ $detail->penggajian->label_tipe }}</span></td>
                            <td>{{ $detail->total_hadir }} hari</td>
                            <td>{{ $detail->gaji_pokok_format }}</td>
                            <td>{{ $detail->total_upah_lembur_format }}</td>
                            <td style="font-weight:600;color:#059669;">{{ $detail->total_gaji_format }}</td>
                            <td>
                                <a href="{{ route('karyawan.slip-gaji.detail', $detail) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                                Belum ada slip gaji
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($slipList->hasPages())
        <div class="card-footer">{{ $slipList->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@endsection
