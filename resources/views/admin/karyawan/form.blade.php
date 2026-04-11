@extends('layouts.admin')

@section('title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('page-title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0" style="font-weight:600;">
                    <i class="bi bi-{{ isset($karyawan) ? 'pencil' : 'person-plus' }} me-2"></i>
                    {{ isset($karyawan) ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ isset($karyawan) ? route('admin.karyawan.update', $karyawan) : route('admin.karyawan.store') }}"
                      method="POST">
                    @csrf
                    @if(isset($karyawan)) @method('PUT') @endif

                    <div class="row g-3">
                        {{-- Nama --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $karyawan->nama ?? '') }}"
                                   placeholder="Nama lengkap karyawan">
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $karyawan->user->email ?? '') }}"
                                   placeholder="email@ruslan-jaya.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Password {{ isset($karyawan) ? '(kosongkan jika tidak diubah)' : '' }}
                                @if(!isset($karyawan)) <span class="text-danger">*</span> @endif
                            </label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 6 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Konfirmasi Password
                            </label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Ulangi password">
                        </div>

                        {{-- Divisi --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Divisi <span class="text-danger">*</span>
                            </label>
                            <select name="divisi_id" class="form-select @error('divisi_id') is-invalid @enderror">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisiList as $div)
                                    <option value="{{ $div->id }}"
                                        {{ old('divisi_id', $karyawan->divisi_id ?? '') == $div->id ? 'selected' : '' }}>
                                        {{ $div->nama_divisi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('divisi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Gaji Harian --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Gaji Harian (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="gaji_harian" min="0" step="1000"
                                       class="form-control @error('gaji_harian') is-invalid @enderror"
                                       value="{{ old('gaji_harian', $karyawan->gaji_harian ?? '') }}"
                                       placeholder="100000">
                                @error('gaji_harian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- No HP --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">No. HP</label>
                            <input type="text" name="no_hp"
                                   class="form-control @error('no_hp') is-invalid @enderror"
                                   value="{{ old('no_hp', $karyawan->no_hp ?? '') }}"
                                   placeholder="08xxxxxxxxxx">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label class="form-label fw-500" style="font-weight:500;font-size:.85rem;">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="aktif"    {{ old('status', $karyawan->status ?? 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status', $karyawan->status ?? '')       === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>
                            {{ isset($karyawan) ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
