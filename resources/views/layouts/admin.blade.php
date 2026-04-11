<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — CV. Ruslan Jaya Indonesia</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a2236;
            --sidebar-hover: #2d3a52;
            --sidebar-active: #2563eb;
            --topbar-height: 60px;
            --primary: #2563eb;
        }

        * { font-family: 'Inter', sans-serif; }

        body { background: #f1f5f9; min-height: 100vh; }

        /* ── Sidebar ── */
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
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        #sidebar .sidebar-brand h6 {
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            margin: 0;
            letter-spacing: .3px;
        }

        #sidebar .sidebar-brand small {
            color: rgba(255,255,255,.45);
            font-size: .7rem;
        }

        #sidebar .nav-label {
            color: rgba(255,255,255,.35);
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: .75rem 1.5rem .25rem;
        }

        #sidebar .nav-link {
            color: rgba(255,255,255,.65);
            padding: .55rem 1.5rem;
            border-radius: 0;
            font-size: .84rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: all .2s;
        }

        #sidebar .nav-link i { font-size: 1rem; width: 20px; }

        #sidebar .nav-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }

        #sidebar .nav-link.active {
            color: #fff;
            background: var(--sidebar-active);
            font-weight: 500;
        }

        /* ── Main content ── */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin .3s ease;
        }

        /* ── Topbar ── */
        #topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 999;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* ── Cards ── */
        .stat-card {
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Alert ── */
        .alert { border: none; border-radius: 10px; }

        /* ── Table ── */
        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding: .85rem 1rem;
        }

        .table tbody td { padding: .85rem 1rem; vertical-align: middle; }

        .table tbody tr:hover { background: #f8fafc; }

        /* ── Badge ── */
        .badge { font-size: .72rem; font-weight: 500; padding: .35em .65em; border-radius: 6px; }

        /* ── Btn ── */
        .btn { border-radius: 8px; font-size: .85rem; font-weight: 500; }
        .btn-sm { border-radius: 6px; }

        /* ── Card ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; border-radius: 12px 12px 0 0 !important; }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* ── Scrollbar sidebar ── */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ SIDEBAR ══ --}}
<nav id="sidebar">
    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:34px;height:34px;background:#2563eb;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-building text-white" style="font-size:.9rem;"></i>
            </div>
            <div>
                <h6>CV. Ruslan Jaya</h6>
                <small>Sistem Absensi & Penggajian</small>
            </div>
        </div>
    </div>

    {{-- Admin Info --}}
    <div class="px-3 py-3 border-bottom" style="border-color:rgba(255,255,255,.08)!important;">
        <div class="d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;background:#374151;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-person-fill text-white" style="font-size:.85rem;"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:.8rem;font-weight:500;">{{ auth()->user()->nama }}</div>
                <div style="color:rgba(255,255,255,.4);font-size:.68rem;">Administrator</div>
            </div>
        </div>
    </div>

    <ul class="nav flex-column mt-1">
        <li class="nav-label">Menu Utama</li>

        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>

        <li class="nav-label mt-2">Manajemen SDM</li>

        <li class="nav-item">
            <a href="{{ route('admin.karyawan.index') }}"
               class="nav-link {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Data Karyawan
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.divisi.index') }}"
               class="nav-link {{ request()->routeIs('admin.divisi.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Divisi
            </a>
        </li>

        <li class="nav-label mt-2">Kehadiran</li>

        <li class="nav-item">
            <a href="{{ route('admin.absensi.index') }}"
               class="nav-link {{ request()->routeIs('admin.absensi.index') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Data Absensi
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.absensi.manual') }}"
               class="nav-link {{ request()->routeIs('admin.absensi.manual') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> Input Manual
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.izin-sakit.index') }}"
               class="nav-link {{ request()->routeIs('admin.izin-sakit.*') ? 'active' : '' }}">
                <i class="bi bi-file-medical"></i> Izin & Sakit
                @php $pending = \App\Models\IzinSakit::where('status','pending')->count(); @endphp
                @if($pending > 0)
                    <span class="badge bg-danger ms-auto">{{ $pending }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.lembur.index') }}"
               class="nav-link {{ request()->routeIs('admin.lembur.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Lembur
            </a>
        </li>

        <li class="nav-label mt-2">Keuangan</li>

        <li class="nav-item">
            <a href="{{ route('admin.penggajian.index') }}"
               class="nav-link {{ request()->routeIs('admin.penggajian.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Penggajian
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.laporan.index') }}"
               class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Laporan
            </a>
        </li>

        <li class="nav-label mt-2">Akun</li>

        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start"
                        style="color:rgba(255,255,255,.65);">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </button>
            </form>
        </li>
    </ul>
</nav>

{{-- ══ MAIN CONTENT ══ --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="page-title">@yield('page-title', 'Dashboard')</h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    {{-- Page content --}}
    <div class="p-4">
        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile sidebar toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>

@stack('scripts')
</body>
</html>
