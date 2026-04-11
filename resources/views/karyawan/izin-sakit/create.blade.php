@extends('layouts.karyawan')

@section('title', 'Ajukan Izin/Sakit')
@section('page-title', 'Ajukan Izin/Sakit')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0" style="font-weight:600;">
                    <i class="bi bi-file-earmark-text me-2"></i>Form Pengajuan Izin / Sakit
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('karyawan.izin-sakit.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">
                            Tanggal <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal') }}"
                               min="{{ now()->format('Y-m-d') }}">
                        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">
                            Jenis Pengajuan <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="jenisIzin"
                                       value="izin" {{ old('jenis') === 'izin' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenisIzin">
                                    <span class="badge bg-warning text-dark">Izin</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="jenisSakit"
                                       value="sakit" {{ old('jenis') === 'sakit' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenisSakit">
                                    <span class="badge bg-info">Sakit</span>
                                </label>
                            </div>
                        </div>
                        @error('jenis') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small" style="font-weight:500;">
                            Keterangan <span class="text-danger">*</span>
                        </label>
                        <textarea name="keterangan" rows="4"
                                  class="form-control @error('keterangan') is-invalid @enderror"
                                  placeholder="Jelaskan alasan izin/sakit Anda...">{{ old('keterangan') }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small" style="font-weight:500;">
                            Bukti Pendukung <span class="text-muted">(opsional, maks 2MB)</span>
                        </label>
                        <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf"
                               class="form-control @error('bukti') is-invalid @enderror">
                        <div class="form-text">Format: JPG, PNG, atau PDF</div>
                        @error('bukti') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('karyawan.izin-sakit.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
