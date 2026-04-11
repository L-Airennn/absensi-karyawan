@extends('layouts.karyawan')

@section('title', 'Registrasi Wajah')
@section('page-title', 'Registrasi Wajah')

@push('styles')
<style>
    #videoEl { width:100%; border-radius:14px; border:3px solid #e2e8f0; display:block; }
    #overlay { position:absolute; top:0; left:0; border-radius:14px; pointer-events:none; }
    #video-wrap { position:relative; max-width:460px; margin:0 auto; }
    .step-badge {
        width:28px; height:28px;
        background:#0f4c75;
        border-radius:50%;
        color:#fff;
        font-size:.8rem;
        font-weight:700;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="row g-3">

            {{-- Panel kamera --}}
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0" style="font-weight:600;">
                            <i class="bi bi-person-bounding-box me-2"></i>Daftarkan Wajah Anda
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="loadingMsg" class="alert alert-info mb-3">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Memuat model AI...
                        </div>

                        <div id="video-wrap" class="mb-3">
                            <video id="videoEl" autoplay muted playsinline></video>
                            <canvas id="overlay"></canvas>
                        </div>

                        <div id="progress" class="mb-3" style="display:none;">
                            <div class="progress" style="height:8px;border-radius:8px;">
                                <div id="progressBar" class="progress-bar bg-success" style="width:0%;transition:width .3s;"></div>
                            </div>
                            <small class="text-muted mt-1 d-block" id="progressLabel">0/10 sampel</small>
                        </div>

                        <button id="btnAmbil" class="btn btn-primary px-4 me-2" disabled>
                            <i class="bi bi-camera me-1"></i> Ambil Sampel
                        </button>
                        <button id="btnSimpan" class="btn btn-success px-4" style="display:none;">
                            <i class="bi bi-save me-1"></i> Simpan Wajah
                        </button>
                    </div>
                </div>
            </div>

            {{-- Panduan & status --}}
            <div class="col-md-5">
                {{-- Status saat ini --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 style="font-weight:600;" class="mb-3">Status Registrasi</h6>
                        @if($dataWajah)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="{{ $dataWajah->foto_url }}" alt="Foto wajah"
                                     style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                                <div>
                                    <div class="badge bg-success mb-1"><i class="bi bi-check-lg me-1"></i>Terdaftar</div>
                                    <div class="text-muted small">Terakhir diperbarui:<br>{{ $dataWajah->updated_at->format('d M Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Mendaftar ulang akan mengganti data wajah lama.
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="bi bi-person-x text-muted fs-3 d-block mb-1"></i>
                                <p class="text-muted small mb-0">Belum ada data wajah terdaftar.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Panduan --}}
                <div class="card">
                    <div class="card-body">
                        <h6 style="font-weight:600;" class="mb-3">Cara Registrasi</h6>
                        @foreach([
                            'Pastikan pencahayaan ruangan cukup terang.',
                            'Posisikan wajah di tengah frame kamera.',
                            'Klik "Ambil Sampel" sebanyak 10x dari berbagai sudut.',
                            'Klik "Simpan Wajah" setelah 10 sampel terkumpul.',
                        ] as $no => $tip)
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <div class="step-badge mt-1">{{ $no + 1 }}</div>
                                <p class="mb-0 small text-muted">{{ $tip }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL  = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
const MAX_SAMPLE = 10;
let descriptors  = [];
let capturedBlob = null;
let video, canvas;

async function init() {
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);

    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
    video = document.getElementById('videoEl');
    canvas = document.getElementById('overlay');
    video.srcObject = stream;

    await new Promise(r => video.onloadedmetadata = r);
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;

    document.getElementById('loadingMsg').style.display = 'none';
    document.getElementById('btnAmbil').disabled = false;
    document.getElementById('progress').style.display = 'block';

    // Live detection preview
    setInterval(async () => {
        const detections = await faceapi
            .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks();

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const resized = faceapi.resizeResults(detections, { width: canvas.width, height: canvas.height });
        faceapi.draw.drawFaceLandmarks(canvas, resized);
    }, 300);
}

document.getElementById('btnAmbil').addEventListener('click', async function () {
    if (descriptors.length >= MAX_SAMPLE) return;

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        alert('Wajah tidak terdeteksi. Posisikan wajah di depan kamera.');
        return;
    }

    descriptors.push(Array.from(detection.descriptor));

    // Simpan snapshot pertama sebagai foto
    if (descriptors.length === 1) {
        const snap = document.createElement('canvas');
        snap.width  = video.videoWidth;
        snap.height = video.videoHeight;
        snap.getContext('2d').drawImage(video, 0, 0);
        snap.toBlob(b => capturedBlob = b, 'image/jpeg', 0.85);
    }

    const pct = (descriptors.length / MAX_SAMPLE) * 100;
    document.getElementById('progressBar').style.width = pct + '%';
    document.getElementById('progressLabel').textContent = `${descriptors.length}/${MAX_SAMPLE} sampel`;

    if (descriptors.length >= MAX_SAMPLE) {
        this.disabled = true;
        document.getElementById('btnSimpan').style.display = 'inline-block';
    }
});

document.getElementById('btnSimpan').addEventListener('click', async function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    // Rata-rata descriptor
    const avgDescriptor = descriptors[0].map((_, i) =>
        descriptors.reduce((sum, d) => sum + d[i], 0) / descriptors.length
    );

    // Foto ke base64
    const reader = new FileReader();
    reader.readAsDataURL(capturedBlob);
    reader.onloadend = async () => {
        try {
            const res = await fetch('{{ route("karyawan.registrasi-wajah.simpan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    foto_wajah: reader.result,
                    face_encoding: avgDescriptor,
                }),
            });

            const data = await res.json();
            if (res.ok) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Gagal menyimpan. Coba lagi.');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Wajah';
            }
        } catch (err) {
            alert('Gagal menghubungi server.');
        }
    };
});

init().catch(err => {
    document.getElementById('loadingMsg').className = 'alert alert-danger mb-3';
    document.getElementById('loadingMsg').textContent = 'Gagal memuat model: ' + err.message;
});
</script>
@endpush
