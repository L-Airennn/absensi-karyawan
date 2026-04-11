<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CV. Ruslan Jaya Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 50%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }

        .login-logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #1b6ca8, #2563eb);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: .65rem 1rem;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
        }

        .input-group .form-control { border-radius: 0 10px 10px 0; }

        .btn-login {
            background: linear-gradient(135deg, #1b6ca8, #2563eb);
            border: none;
            border-radius: 10px;
            padding: .7rem;
            font-weight: 600;
            font-size: .9rem;
            letter-spacing: .3px;
            transition: opacity .2s, transform .1s;
        }

        .btn-login:hover { opacity: .9; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .form-label { font-size: .83rem; font-weight: 500; color: #374151; margin-bottom: .4rem; }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>

<div class="login-card">
    {{-- Logo --}}
    <div class="text-center mb-4">
        <div class="login-logo">
            <i class="bi bi-building text-white fs-4"></i>
        </div>
        <h5 class="fw-700 mb-0" style="color:#1e293b;font-weight:700;">CV. Ruslan Jaya Indonesia</h5>
        <p class="text-muted small mt-1 mb-0">Sistem Absensi & Penggajian Karyawan</p>
    </div>

    <div class="divider"></div>

    {{-- Alert error --}}
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Login --}}
    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       placeholder="email@ruslan-jaya.com"
                       value="{{ old('email') }}" autofocus>
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="passwordInput"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••">
                <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                        style="border-radius:0 10px 10px 0;border-left:none;">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login w-100 text-white">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
        </button>
    </form>

    <div class="divider"></div>

    <p class="text-center text-muted mb-0" style="font-size:.75rem;">
        &copy; {{ date('Y') }} CV. Ruslan Jaya Indonesia. All rights reserved.
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
</script>
</body>
</html>
