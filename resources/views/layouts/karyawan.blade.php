<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Karyawan') — CV. Ruslan Jaya Indonesia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 240px;
            --sidebar-bg: #0f4c75;
            --sidebar-hover: #1b6ca8;
            --sidebar-active: #1b6ca8;
            --topbar-height: 60px;
        }

        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f4f8; min-height: 100vh; }

        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform .3s ease;
            overflow-y: auto;
        }

        #sidebar .sidebar-brand {
            padding: 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .nav-link-sidebar {
            color: rgba(255,255,255,.7);
            padding: .55rem 1.25rem;
            font-size: .84rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: all .2s;
            text-decoration: none;
        }

        .nav-link-sidebar i { font-size: 1rem; width: 20px; }
        .nav-link-sidebar:hover { color: #fff; background: var(--sidebar-hover); }
        .nav-link-sidebar.active { color: #fff; background: var(--sidebar-active); font-weight: 500; }

        .nav-label {
            color: rgba(255,255,255,.35);
            font-size: .63rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: .75rem 1.25rem .2rem;
        }

        #main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        #topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #dde3ea;
            position: sticky;
            top: 0;
            z-index: 999;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #eef2f7; border-radius: 12px 12px 0 0 !important; }
        .btn { border-radius: 8px; font-size: .85rem; font-weight: 500; }
        .badge { font-size: .72rem; font-weight: 500; border-radius: 6px; }
        .alert { border: none; border-radius: 10px; }
        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .73rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .table tbody td { vertical-align: middle; font-size: .875rem; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:34px;height:34px;background:#1b6ca8;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-person-badge text-white" style="font-size:.9rem;"></i>
            </div>
            <div>
                <div style="color:#fff;font-weight:700;font-size:.82rem;">CV. Ruslan Jaya</div>
                <div style="color:rgba(255,255,255,.45);font-size:.68rem;">Portal Karyawan</div>
            </div>
        </div>
    </div>

    {{-- Profil karyawan --}}
    <div class="px-3 py-3" style="border-bottom:1px solid rgba(255,255,255,.1)">
        <div style="color:#fff;font-size:.82rem;font-weight:500;">{{ auth()->user()->nama }}</div>
        <div style="color:rgba(255,255,255,.45);font-size:.7rem;">
            {{ auth()->user()->karyawan?->divisi?->nama_divisi ?? 'Karyawan' }}
        </div>
    </div>

    <div class="mt-1">
        <div class="nav-label">Menu</div>

        <a href="{{ route('karyawan.dashboard') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <a href="{{ route('karyawan.presensi') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.presensi') ? 'active' : '' }}">
            <i class="bi bi-camera"></i> Presensi
        </a>

        <a href="{{ route('karyawan.registrasi-wajah') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.registrasi-wajah') ? 'active' : '' }}">
            <i class="bi bi-person-bounding-box"></i> Registrasi Wajah
        </a>

        <a href="{{ route('karyawan.absensi.riwayat') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.absensi.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Riwayat Absensi
        </a>

        <div class="nav-label mt-2">Pengajuan</div>

        <a href="{{ route('karyawan.izin-sakit.index') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.izin-sakit.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Izin & Sakit
        </a>

        <div class="nav-label mt-2">Keuangan</div>

        <a href="{{ route('karyawan.slip-gaji.index') }}"
           class="nav-link-sidebar {{ request()->routeIs('karyawan.slip-gaji.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Slip Gaji
        </a>

        <div class="nav-label mt-2">Akun</div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link-sidebar border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-left"></i> Keluar
            </button>
        </form>
    </div>
</nav>

<div id="main-content">
    <div id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span style="font-size:1rem;font-weight:600;color:#1e293b;">@yield('page-title', 'Dashboard')</span>
        </div>
        <span class="text-muted small">{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>

    <div class="p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
@stack('scripts')
</body>
</html>
