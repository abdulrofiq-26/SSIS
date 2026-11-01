# SSIS — Sistem Informasi Sparepart (Laravel + CBIR Image Search)

Aplikasi fullstack untuk manajemen data sparepart dengan fitur **pencarian gambar (CBIR / Content-Based Image Retrieval)** menggunakan **ResNet50 (TensorFlow)** + **ChromaDB** sebagai vector database.

## Arsitektur Sistem

Sistem ini terdiri dari beberapa bagian yang harus berjalan bersamaan:

| Komponen | Teknologi | Port / Cara Jalan | Fungsi |
|---|---|---|---|
| Backend Web | Laravel 10 (PHP ≥ 8.1) | `http://localhost:8000` | CRUD sparepart, auth, role, import/export Excel |
| Mesin Pencari Gambar | Python (Flask + TensorFlow ResNet50 + ChromaDB) | `http://127.0.0.1:5005` | Ekstraksi fitur gambar & pencarian gambar mirip |
| Queue Worker | Laravel Queue (database driver) | CLI `php artisan queue:work` | Memproses indexing vektor gambar di background |
| Frontend Assets | Vite + Tailwind + Alpine.js | `npm run dev` / `build` | UI aplikasi |

Alur pencarian gambar:
1. User mengunggah gambar query di aplikasi Laravel.
2. Laravel memanggil Flask service (`PY_SERVICE_URL`) untuk mengekstrak vektor fitur ResNet50.
3. ChromaDB mencari gambar sparepart paling mirip (cosine similarity).
4. Hasil ID sparepart dikembalikan ke Laravel untuk ditampilkan.

---

## Prasyarat

Pastikan sudah terinstal di komputer (Windows):

- **PHP ≥ 8.1** + ekstensi umum (pdo_mysql, gd, mbstring, fileinfo) — cek: `php -v`
- **Composer** — cek: `composer -V`
- **Node.js + npm** — cek: `node -v`
- **MySQL / MariaDB** berjalan di `127.0.0.1:3306`
- **Python 3.10–3.11** (proyek ini sudah menyediakan virtual environment `venv-tf\`)

---

## 1. Instalasi (sekali saja)

```powershell
# 1. Install dependency PHP
composer install

# 2. Install dependency frontend
npm install

# 3. Salin file environment
Copy-Item .env.example .env
php artisan key:generate
```

### 1.1 Konfigurasi `.env`

Sesuaikan bagian berikut:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ssis          # buat dulu database-nya di MySQL
DB_USERNAME=root
DB_PASSWORD=isi_password_anda

QUEUE_CONNECTION=database

# URL Python ResNet service (jangan diubah kalau port 5005 tetap dipakai)
PY_SERVICE_URL=http://127.0.0.1:5005
```

### 1.2 Migrasi & seeding database

```powershell
php artisan migrate --seed
```

### 1.3 Link storage (agar gambar sparepart bisa diakses)

```powershell
php artisan storage:link
```

### 1.4 Virtual environment Python (`venv-tf`)

Jika folder `venv-tf\` sudah ada, lewati langkah ini. Jika belum:

```powershell
python -m venv venv-tf
venv-tf\Scripts\pip install flask tensorflow chromadb numpy pillow
```

---

## 2. Menjalankan Sistem (harus semuanya aktif)

Buka **4 terminal / window PowerShell** terpisah dari root proyek `SSIS`, jalankan secara berurutan:

### 2.1 Mesin pencari gambar (Python ResNet50 service)

Cara termudah — dobel-klik file:

```
scripts\start_resnet_service.bat
```

Atau manual:

```powershell
venv-tf\Scripts\python.exe scripts\resnet_service.py
```

- Tunggu sampai muncul `Model ResNet50 siap.` dan service berjalan di `http://127.0.0.1:5005`.
- Loading pertama bisa 30–90 detik (memuat bobot ResNet50 ±100MB). **Biarkan jendela ini tetap terbuka.**
- Tes apakah hidup: buka `http://127.0.0.1:5005/health` di browser → harus muncul `{"status": "ok", "indexed": <n>}`.

### 2.2 Queue worker (background indexing)

```powershell
# atau dobel-klik scripts\start_queue_worker.bat
php artisan queue:work --tries=3 --timeout=300
```

Biarkan jendela ini tetap terbuka. Worker inilah yang memproses job `IndexSparepartVector` setiap kali gambar sparepart diunggah/diubah.

### 2.3 Backend Laravel

```powershell
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`.

### 2.4 Frontend (Vite)

Mode pengembangan (hot reload):

```powershell
npm run dev
```

Atau untuk produksi (sekali build, tidak perlu `npm run dev` lagi):

```powershell
npm run build
```

> Kalau `npm run dev` aktif sementara `php artisan serve` jalan, Laravel otomatis memakai file `public/hot`.

---

## 3. Indexing Gambar ke Mesin Pencari

Vektor fitur disimpan di `storage\app\chroma_db\`.

- **Otomatis**: setiap upload/edit gambar sparepart, queue worker mengindeksnya lewat job `IndexSparepartVector`.
- **Manual / reindex massal** (service Python harus hidup dulu):

```powershell
php artisan sparepart-images:index   # indeks gambar sparepart
php artisan spareparts:reindex       # reindex massal
```

Verifikasi jumlah gambar terindeks via endpoint `/health` Python service, atau langsung lewat pencarian di UI aplikasi.

---

## 4. Urutan Menjalankan yang Benar (ringkasan)

1. MySQL aktif (XAMPP/Laragon/services).
2. `venv-tf\Scripts\python.exe scripts\resnet_service.py` → tunggu "Model ResNet50 siap".
3. `php artisan queue:work`
4. `php artisan serve`
5. `npm run dev` (atau `npm run build` untuk produksi).

**Urutan penting**: Python service harus hidup **sebelum** queue worker memproses job indexing, dan **sebelum** fitur pencarian gambar dipakai.

---

## 5. Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| Pencarian gambar error / timeout | Python service belum jalan | Pastikan `http://127.0.0.1:5005/health` merespons |
| `failed to get random numbers` saat ekstraksi vektor | Environment Python dari PHP tidak lengkap | Jalankan sebagai service Flask `resnet_service.py` (bukan script CLI lama) — sudah tertangani otomatis |
| Upload gambar tapi tidak muncul di hasil pencarian | Queue worker mati / job gagal | Cek `php artisan queue:failed`, jalankan `php artisan queue:retry all`, pastikan worker jalan |
| Hasil pencarian kosong semua | Database Chroma kosong | Jalankan `php artisan sparepart-images:index` (service Python harus hidup dulu) |
| Gambar tidak tampil | Storage link belum dibuat | `php artisan storage:link` |
| Model ResNet lambat memuat / error bobot | Internet lambat / cache Keras rusak | Bobot tersimpan di `%USERPROFILE%\.keras\models`; hapus bila korup lalu restart service |
| `npm run dev` error | Dependency belum terinstall | `npm install` |
| Port 5005 dipakai aplikasi lain | Konflik port | Ubah `PY_SERVICE_URL` di `.env` dan port di `scripts/resnet_service.py` |

Cek log aplikasi di `storage\logs\laravel.log` dan log Python pada jendela service.

---

## 6. Perintah Berguna

```powershell
php artisan migrate:fresh --seed     # reset database
php artisan queue:failed             # lihat job gagal
php artisan queue:retry all          # ulangi job gagal
php artisan tinker                   # REPL Laravel
```

## 7. Struktur Penting Proyek

```
SSIS/
├── app/
│   ├── Jobs/IndexSparepartVector.php         # job indexing vektor (queue)
│   ├── Services/ResnetServiceClient.php      # client HTTP ke Python service
│   └── Http/Controllers/SearchController.php # endpoint pencarian gambar
├── scripts/
│   ├── resnet_service.py                     # Flask ResNet50 + ChromaDB (port 5005)
│   ├── start_resnet_service.bat              # launcher service Python
│   └── start_queue_worker.bat                # launcher queue worker
├── venv-tf/                                  # virtual environment Python
├── storage/app/chroma_db/                    # database vektor ChromaDB
└── resources/                                # frontend (Blade + JS + CSS)
```
