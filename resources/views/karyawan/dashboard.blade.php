@extends('layouts.karyawan')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Selamat datang --}}
<div class="card mb-4" style="background:linear-gradient(135deg,#0f4c75,#1b6ca8);border:none;">
    <div class="card-body d-flex align-items-center gap-3 py-3">
        <div style="width:50px;height:50px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-person-fill text-white fs-4"></i>
        </div>
        <div>
            <div class="text-white fw-600" style="font-weight:600;font-size:1rem;">Selamat datang, {{ $karyawan->nama }}!</div>
            <div style="color:rgba(255,255,255,.65);font-size:.82rem;">
                {{ $karyawan->divisi->nama_divisi }} &bull; {{ $today->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <div class="ms-auto">
            @if($absensiHariIni && $absensiHariIni->status === 'hadir')
                <span class="badge bg-success fs-6 py-2 px-3">
                    <i class="bi bi-check-circle me-1"></i>Sudah Presensi
                </span>
            @else
                <a href="{{ route('karyawan.presensi') }}" class="btn btn-warning fw-600" style="font-weight:600;">
                    <i class="bi bi-camera me-1"></i>Presensi Sekarang
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Stat bulan ini --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:700;color:#10b981;">{{ $totalHadirBulanIni }}</div>
            <div class="text-muted small">Hari Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:700;color:#f59e0b;">{{ $totalIzinBulanIni }}</div>
            <div class="text-muted small">Izin/Sakit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.8rem;font-weight:700;color:#8b5cf6;">{{ number_format($totalLemburBulanIni, 1) }}</div>
            <div class="text-muted small">Jam Lembur</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.4rem;font-weight:700;color:#2563eb;">Rp {{ number_format($estimasiGaji, 0, ',', '.') }}</div>
            <div class="text-muted small">Estimasi Gaji</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Riwayat absensi terakhir --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0" style="font-weight:600;">Absensi Terakhir</h6>
                <a href="{{ route('karyawan.absensi.riwayat') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensiTerakhir as $ab)
                            <tr>
                                <td class="small">{{ $ab->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="small">{{ $ab->jam_masuk ?? '-' }}</td>
                                <td><span class="badge bg-{{ $ab->badge_status }}">{{ $ab->label_status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted small">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Izin terbaru --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0" style="font-weight:600;">Pengajuan Izin/Sakit</h6>
                <a href="{{ route('karyawan.izin-sakit.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajukan
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izinTerbaru as $izin)
                            <tr>
                                <td class="small">{{ $izin->tanggal->format('d M Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $izin->label_jenis }}</span></td>
                                <td><span class="badge bg-{{ $izin->badge_status }}">{{ ucfirst($izin->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted small">Belum ada pengajuan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Shortcut menu --}}
    <div class="col-12">
        <div class="row g-2">
            @foreach([
                ['route' => 'karyawan.presensi',         'icon' => 'camera',          'label' => 'Presensi',       'color' => '#0f4c75'],
                ['route' => 'karyawan.registrasi-wajah', 'icon' => 'person-bounding-box','label' => 'Daftar Wajah', 'color' => '#0369a1'],
                ['route' => 'karyawan.absensi.riwayat',  'icon' => 'calendar3',       'label' => 'Riwayat Absensi','color' => '#059669'],
                ['route' => 'karyawan.izin-sakit.create','icon' => 'file-earmark-text','label' => 'Ajukan Izin',  'color' => '#d97706'],
                ['route' => 'karyawan.slip-gaji.index',  'icon' => 'receipt',          'label' => 'Slip Gaji',     'color' => '#7c3aed'],
            ] as $menu)
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route($menu['route']) }}"
                       class="card text-center text-decoration-none p-3 h-100"
                       style="transition:transform .2s;hover:transform:translateY(-2px);">
                        <div style="width:44px;height:44px;background:{{ $menu['color'] }}1a;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                            <i class="bi bi-{{ $menu['icon'] }}" style="font-size:1.2rem;color:{{ $menu['color'] }};"></i>
                        </div>
                        <div class="small" style="font-weight:500;color:#1e293b;">{{ $menu['label'] }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
