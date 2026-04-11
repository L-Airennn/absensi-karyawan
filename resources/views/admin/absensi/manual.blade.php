@extends('layouts.admin')

@section('title', 'Input Absensi Manual')
@section('page-title', 'Input Absensi Manual')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-pencil-square me-2"></i>Checklist Absensi</h6>

            {{-- Pilih tanggal --}}
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Tanggal:</label>
                <input type="date" name="tanggal" class="form-control form-control-sm"
                       value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}">
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.85rem;">
            <i class="bi bi-info-circle-fill"></i>
            Menampilkan absensi untuk: <strong class="ms-1">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</strong>
        </div>

        <form action="{{ route('admin.absensi.manual.simpan') }}" method="POST">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Nama Karyawan</th>
                            <th>Divisi</th>
                            <th class="text-center" style="width:100px">
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Hadir</span>
                            </th>
                            <th class="text-center" style="width:120px">
                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Tidak Hadir</span>
                            </th>
                            <th class="text-center" style="width:90px">
                                <span class="text-warning"><i class="bi bi-clock me-1"></i>Izin</span>
                            </th>
                            <th class="text-center" style="width:90px">
                                <span class="text-info"><i class="bi bi-hospital me-1"></i>Sakit</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawanList as $i => $kar)
                            @php
                                $absensi = $kar->absensi->first();
                                $status  = $absensi ? $absensi->status : 'tidak_hadir';
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div style="font-weight:500;">{{ $kar->nama }}</div>
                                    @if($absensi && $absensi->sumber === 'face')
                                        <small class="text-info"><i class="bi bi-camera me-1"></i>Face Recognition</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $kar->divisi->nama_divisi }}</span>
                                </td>

                                @foreach(['hadir', 'tidak_hadir', 'izin', 'sakit'] as $opt)
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="radio"
                                                   name="absensi[{{ $kar->id }}]"
                                                   value="{{ $opt }}"
                                                   {{ $status === $opt ? 'checked' : '' }}
                                                   {{ ($absensi && $absensi->sumber === 'face' && $opt !== 'hadir') ? '' : '' }}>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Tidak ada karyawan aktif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($karyawanList->count() > 0)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Total: {{ $karyawanList->count() }} karyawan</small>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Absensi
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

@endsection
