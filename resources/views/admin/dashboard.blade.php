@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-white-50 small mb-1">Total Karyawan Aktif</p>
                    <h3 class="text-white fw-700 mb-0">{{ $totalKaryawan }}</h3>
                </div>
                <div class="stat-icon" style="background:rgba(255,255,255,.15);">
                    <i class="bi bi-people-fill text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100" style="background:linear-gradient(135deg,#10b981,#059669);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-white-50 small mb-1">Hadir Hari Ini</p>
                    <h3 class="text-white fw-700 mb-0">{{ $hadirHariIni }}</h3>
                    <small class="text-white-50">dari {{ $totalKaryawan }} karyawan</small>
                </div>
                <div class="stat-icon" style="background:rgba(255,255,255,.15);">
                    <i class="bi bi-person-check-fill text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-white-50 small mb-1">Izin Menunggu</p>
                    <h3 class="text-white fw-700 mb-0">{{ $izinPending }}</h3>
                    <small class="text-white-50">perlu persetujuan</small>
                </div>
                <div class="stat-icon" style="background:rgba(255,255,255,.15);">
                    <i class="bi bi-hourglass-split text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-white-50 small mb-1">Penggajian Bulan Ini</p>
                    <h3 class="text-white fw-700 mb-0">{{ $penggajianBulanIni }}</h3>
                    <small class="text-white-50">periode generate</small>
                </div>
                <div class="stat-icon" style="background:rgba(255,255,255,.15);">
                    <i class="bi bi-cash-stack text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Grafik Kehadiran 7 Hari --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600" style="font-weight:600;">Grafik Kehadiran 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <canvas id="grafikKehadiran" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Izin Pending --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600" style="font-weight:600;">Izin Menunggu Approval</h6>
                <a href="{{ route('admin.izin-sakit.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($izinTerbaru as $izin)
                    <div class="d-flex align-items-center px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-500 small" style="font-weight:500;">{{ $izin->karyawan->nama }}</div>
                            <div class="text-muted" style="font-size:.75rem;">
                                {{ ucfirst($izin->jenis) }} • {{ $izin->tanggal->format('d M Y') }}
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-all fs-4 d-block mb-1"></i>
                        <small>Tidak ada izin pending</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Absensi Hari Ini --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600" style="font-weight:600;">
                    Absensi Hari Ini — {{ $today->translatedFormat('d F Y') }}
                </h6>
                <a href="{{ route('admin.absensi.manual') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Input Manual
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Divisi</th>
                                <th>Jam Masuk</th>
                                <th>Sumber</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensiHariIni as $ab)
                                <tr>
                                    <td class="fw-500" style="font-weight:500;">{{ $ab->karyawan->nama }}</td>
                                    <td class="text-muted small">{{ $ab->karyawan->divisi->nama_divisi }}</td>
                                    <td>{{ $ab->jam_masuk ?? '-' }}</td>
                                    <td>
                                        @if($ab->sumber === 'face')
                                            <span class="badge bg-info"><i class="bi bi-camera me-1"></i>Face</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-pencil me-1"></i>Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ab->badge_status }}">{{ $ab->label_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                        Belum ada data absensi hari ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels  = @json(array_column($grafikHadir, 'tanggal'));
const hadir   = @json(array_column($grafikHadir, 'hadir'));
const tidak   = @json(array_column($grafikHadir, 'tidak'));

new Chart(document.getElementById('grafikKehadiran'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Hadir',
                data: hadir,
                backgroundColor: 'rgba(16,185,129,.8)',
                borderRadius: 6,
            },
            {
                label: 'Tidak Hadir',
                data: tidak,
                backgroundColor: 'rgba(239,68,68,.6)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
