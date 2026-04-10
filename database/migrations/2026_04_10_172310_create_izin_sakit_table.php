
Saya ingin membuat sistem berbasis web menggunakan framework Laravel untuk kebutuhan Kerja Praktik (KP).
Tolong bantu saya membuat sistem secara bertahap, dimulai dari struktur database (migration), model, controller, hingga tampilan blade.
🏢 DESKRIPSI SISTEM
Sistem yang akan dibuat adalah:
Sistem Absensi dan Penggajian Karyawan berbasis Web
Studi kasus: CV. Ruslan Jaya Indonesia (usaha produksi mie ayam dan pangsit)
🧠 METODE PENGEMBANGAN
Gunakan pendekatan:
Prototype Model
👥 ROLE SISTEM
1. Admin
* login
* CRUD data karyawan
* input absensi (manual checklist)
* melihat absensi face recognition
* input lembur
* generate penggajian (harian, mingguan, bulanan)
* menghapus penggajian (dengan konfirmasi)
* melihat laporan
* approve/reject izin & sakit
2. Karyawan
* login
* presensi menggunakan kamera (face recognition)
* registrasi wajah
* melihat absensi pribadi
* melihat slip gaji
* mengajukan izin / sakit
🔑 FITUR UTAMA
1. Authentication
* login admin & karyawan
* role-based access
2. Manajemen Karyawan
* tambah, edit, hapus, list karyawan
* divisi menggunakan dropdown
* divisi bisa ditambahkan
3. Absensi
A. Face Recognition
* menggunakan face-api.js
* simpan:
   * foto wajah
   * face encoding
B. Manual (Admin)
* checklist per tanggal
* sebagai backup absensi
4. Izin / Sakit
* field:
   * tanggal
   * jenis
   * keterangan
   * bukti
* status:
   * pending
   * approved
   * rejected
* perlu approval admin
5. Lembur
* jam lembur
* upah lembur
6. Penggajian
* harian / mingguan / bulanan
* rumus:
   * gaji = (hadir × gaji harian) + lembur
* bisa:
   * generate
   * delete (soft delete)
   * generate ulang
7. Laporan
* absensi bulanan
* penggajian bulanan
* export PDF (DOMPDF)
8. Dashboard
Admin & karyawan sesuai kebutuhan
🗄️ STRUKTUR DATABASE (BAHASA INDONESIA)
1. users
* id
* nama
* email
* password
* role
2. karyawan
* id
* user_id
* nama
* divisi_id
* gaji_harian
* no_hp
* status
3. divisi
* id
* nama_divisi
4. absensi
* id
* karyawan_id
* tanggal
* status (hadir / tidak_hadir)
* sumber (face / manual)
5. data_wajah
* id
* karyawan_id
* foto_wajah
* face_encoding (JSON)
6. izin_sakit
* id
* karyawan_id
* tanggal
* jenis (izin / sakit)
* keterangan
* bukti
* status (pending / disetujui / ditolak)
7. lembur
* id
* karyawan_id
* tanggal
* jam_lembur
* upah_lembur
8. penggajian
* id
* tipe (harian / mingguan / bulanan)
* periode_awal
* periode_akhir
* tanggal_generate
* deleted_at (soft delete)
9. detail_penggajian
* id
* penggajian_id
* karyawan_id
* total_hadir
* total_lembur
* total_gaji
⚙️ TEKNIS
* Laravel MVC
* Migration & Eloquent
* Blade
* DOMPDF
* face-api.js
* responsive web
🎯 OUTPUT YANG DIMINTA
1. Migration
2. Model + relasi
3. Controller
4. Routing
5. Blade UI
6. Auth + role
🚀 MULAI DARI
👉 Langkah 1: Migration Database (versi final Bahasa Indonesia)

Show more
Apr 10

Reading the docx skill to understand best practices
Reading the docx skill to understand best practices
Baik! Saya akan membantu Anda membangun sistem ini secara bertahap. Mari kita mulai dari Langkah 1: Migration Database.

Saya akan membuat semua file migration lengkap untuk sistem ini.


Created 10 files, ran a command, read a file
Created 10 files, ran a command, read a file
✅ Langkah 1 Selesai — Migration Database
Berikut ringkasan 9 migration + 1 seeder yang telah dibuat:

📋 Urutan Migration (urutan penting karena foreign key)
#	File	Tabel	Keterangan
1	create_users_table	users	Auth, role admin/karyawan
2	create_divisi_table	divisi	Dibuat lebih dulu karena direferensikan karyawan
3	create_karyawan_table	karyawan	FK ke users & divisi
4	create_absensi_table	absensi	FK ke karyawan, unique per hari
5	create_data_wajah_table	data_wajah	Simpan path foto & JSON encoding face-api.js
6	create_izin_sakit_table	izin_sakit	Status pending/disetujui/ditolak
7	create_lembur_table	lembur	Jam & upah lembur
8	create_penggajian_table	penggajian	softDeletes() untuk generate/delete/ulang
9	create_detail_penggajian_table	detail_penggajian	Rincian gaji per karyawan per periode
🔑 Poin Penting Desain
absensi punya kolom sumber (face/manual) dan unique(['karyawan_id', 'tanggal']) — mencegah duplikasi
data_wajah kolom face_encoding bertipe JSON — siap menampung descriptor array dari face-api.js
izin_sakit menyimpan siapa admin yang memproses (diproses_oleh) dan kapan (diproses_pada)
penggajian menggunakan softDeletes() sehingga data bisa dihapus dan di-generate ulang
detail_penggajian memisahkan gaji_pokok, total_upah_lembur, dan total_gaji agar laporan lebih detail
🚀 Cara Pakai
bash
# Salin file ke folder database/migrations/ dan database/seeders/
php artisan migrate
php artisan db:seed
Siap lanjut ke Langkah 2: Model + Relasi Eloquent? Saya akan membuat semua model dengan relasi belongsTo, hasMany, dan hasOne yang lengkap.


2024 01 01 000001 create users table
Code · PHP 

2024 01 01 000002 create divisi table
Code · PHP 

2024 01 01 000003 create karyawan table
Code · PHP 

2024 01 01 000004 create absensi table
Code · PHP 

2024 01 01 000005 create data wajah table
Code · PHP 

2024 01 01 000006 create izin sakit table
Code · PHP 

2024 01 01 000007 create lembur table
Code · PHP 

2024 01 01 000008 create penggajian table
Code · PHP 

2024 01 01 000009 create detail penggajian table
Code · PHP 

Databaseseeder
Code · PHP 





Claude is AI and can make mistakes. Please double-check responses.
2024 01 01 000006 create izin sakit table · PHP
Copy

<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('izin_sakit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['izin', 'sakit']);
            $table->text('keterangan');
            $table->string('bukti')->nullable();   // path file bukti (surat dokter, dll)
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])
                  ->default('pending');
            $table->foreignId('diproses_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('diproses_pada')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }
 
    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_sakit');
    }
};
 
