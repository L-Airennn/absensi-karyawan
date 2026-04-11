@extends('layouts.karyawan')

@section('title', 'Presensi')
@section('page-title', 'Presensi Face Recognition')

@push('styles')
<style>
    #video-container {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }
    #videoEl {
        width: 100%;
        border-radius: 16px;
        border: 3px solid #e2e8f0;
        display: block;
    }
    #overlay {
        position: absolute;
        top: 0; left: 0;
        border-radius: 16px;
        pointer-events: none;
    }
    #statusBox {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,.65);
        color: #fff;
        padding: .35rem .9rem;
        border-radius: 20px;
        font-size: .78rem;
        white-space: nowrap;
    }
    .btn-presensi {
        background: linear-gradient(135deg, #0f4c75, #1b6ca8);
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: .75rem 2rem;
        font-weight: 600;
        font-size: .95rem;
        transition: opacity .2s;
    }
    .btn-presensi:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Sudah presensi hari ini --}}
        @if($absensiHariIni && $absensiHariIni->status === 'hadir')
            <div class="card text-center p-5 mb-3">
                <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-check-circle-fill text-success fs-2"></i>
                </div>
                <h5 style="font-weight:700;">Anda Sudah Presensi!</h5>
                <p class="text-muted mb-1">
                    Presensi hari ini tercatat pukul
                    <strong>{{ $absensiHariIni->jam_masuk ?? '-' }}</strong>
                </p>
                <p class="text-muted small">
                    Sumber: {{ $absensiHariIni->sumber === 'face' ? '📷 Face Recognition' : '✏️ Input Manual' }}
                </p>
            </div>
        @elseif(!$sudahDaftarWajah)
            {{-- Belum daftar wajah --}}
            <div class="card text-center p-5">
                <div style="width:72px;height:72px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-person-bounding-box text-warning fs-2"></i>
                </div>
                <h5 style="font-weight:700;">Wajah Belum Terdaftar</h5>
                <p class="text-muted">Anda perlu mendaftarkan wajah terlebih dahulu sebelum melakukan presensi.</p>
                <a href="{{ route('karyawan.registrasi-wajah') }}" class="btn btn-warning mt-2 px-4">
                    <i class="bi bi-camera me-1"></i> Daftar Wajah Sekarang
                </a>
            </div>
        @else
            {{-- Kamera presensi --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-camera me-2"></i>Presensi dengan Wajah</h6>
                </div>
                <div class="card-body text-center">
                    <div id="loadingModels" class="alert alert-info mb-3">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Memuat model pengenalan wajah...
                    </div>

                    <div id="video-container" class="mb-3">
                        <video id="videoEl" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                        <div id="statusBox">Mendeteksi wajah...</div>
                    </div>

                    <div id="hasilDeteksi" class="mb-3" style="display:none;">
                        <div class="alert alert-success" id="alertCocok">
                            <i class="bi bi-person-check-fill me-2"></i>
                            Wajah terdeteksi: <strong id="namaDeteksi"></strong>
                        </div>
                    </div>

                    <button id="btnPresensi" class="btn-presensi" disabled>
                        <i class="bi bi-fingerprint me-2"></i> Lakukan Presensi
                    </button>

                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Posisikan wajah Anda di depan kamera dan tunggu deteksi otomatis.
                    </p>
                </div>
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
@if($sudahDaftarWajah && !($absensiHariIni && $absensiHariIni->status === 'hadir'))
{{-- face-api.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
const THRESHOLD = 0.5;

let detectedKaryawanId = null;
let detectedNama       = null;

async function mulaiKamera() {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
    const video  = document.getElementById('videoEl');
    video.srcObject = stream;
    await new Promise(r => video.onloadedmetadata = r);

    const canvas  = document.getElementById('overlay');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.style.width  = video.offsetWidth  + 'px';
    canvas.style.height = video.offsetHeight + 'px';

    return video;
}

async function loadModels() {
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
}

async function getFaceEncodings() {
    const res  = await fetch('{{ route("karyawan.presensi.encodings") }}');
    const data = await res.json();
    return data.map(d => ({
        karyawan_id: d.karyawan_id,
        nama: d.nama,
        matcher: new faceapi.FaceMatcher(
            [new faceapi.LabeledFaceDescriptors(d.nama, [new Float32Array(d.face_encoding)])],
            THRESHOLD
        )
    }));
}

async function init() {
    await loadModels();
    const video    = await mulaiKamera();
    const encodings = await getFaceEncodings();

    document.getElementById('loadingModels').style.display = 'none';

    setInterval(async () => {
        const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptors();

        const canvas = document.getElementById('overlay');
        const ctx    = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (detections.length === 0) {
            document.getElementById('statusBox').textContent = 'Tidak ada wajah terdeteksi';
            document.getElementById('hasilDeteksi').style.display = 'none';
            document.getElementById('btnPresensi').disabled = true;
            detectedKaryawanId = null;
            return;
        }

        const resized = faceapi.resizeResults(detections, { width: canvas.width, height: canvas.height });
        faceapi.draw.drawDetections(canvas, resized);

        const descriptor = detections[0].descriptor;
        let cocok = null;
        let bestDist = Infinity;

        for (const enc of encodings) {
            const match = enc.matcher.findBestMatch(descriptor);
            if (match.label !== 'unknown' && match.distance < bestDist) {
                bestDist = match.distance;
                cocok    = enc;
            }
        }

        if (cocok) {
            document.getElementById('statusBox').textContent = `✓ ${cocok.nama}`;
            document.getElementById('namaDeteksi').textContent = cocok.nama;
            document.getElementById('hasilDeteksi').style.display = 'block';
            document.getElementById('btnPresensi').disabled = false;
            detectedKaryawanId = cocok.karyawan_id;
            detectedNama       = cocok.nama;
        } else {
            document.getElementById('statusBox').textContent = 'Wajah tidak dikenali';
            document.getElementById('hasilDeteksi').style.display = 'none';
            document.getElementById('btnPresensi').disabled = true;
            detectedKaryawanId = null;
        }
    }, 1000);
}

document.getElementById('btnPresensi').addEventListener('click', async function () {
    if (!detectedKaryawanId) return;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    try {
        const res = await fetch('{{ route("karyawan.presensi.simpan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                karyawan_id: detectedKaryawanId,
                jam_masuk: new Date().toTimeString().slice(0, 8),
            }),
        });

        const data = await res.json();
        if (res.ok) {
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-fingerprint me-2"></i> Lakukan Presensi';
        }
    } catch (err) {
        alert('Gagal menghubungi server.');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-fingerprint me-2"></i> Lakukan Presensi';
    }
});

init().catch(console.error);
</script>
@endif
@endpush
