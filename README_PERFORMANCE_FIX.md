# SSIS — Panduan Menjalankan (setelah perbaikan performa)

Dokumen ini menjelaskan cara menjalankan aplikasi **setelah perbaikan performa**
(pemindahan indexing ke queue + Python service persisten untuk ResNet50).

---

## Mengapa dulu lambat? (ringkas)

1. Setiap kali **tambah data dengan gambar**, Laravel menjalankan
   `python scripts/index_vector.py` **secara sinkron**. Setiap pemanggilan
   memuat ulang TensorFlow + bobot ResNet50 (~100 MB) dari nol → **> 2 menit**
   per request.
2. Karena server memakai `php artisan serve` (single-threaded) + `QUEUE_CONNECTION=sync`,
   semua request lain (login, pindah halaman) **mengantri** di belakang proses berat itu.
3. Bobot ResNet50 di cache ternyata **korup/terpotong** (57 MB, harusnya 94 MB),
   sehingga indexing sering gagal ("failed to get random numbers" / "cannot find path").

---

## Perubahan yang dilakukan

| Rekomendasi | Status | Implementasi |
|---|---|---|
| **1. Indexing di background (queue)** | ✅ | `app/Jobs/IndexSparepartVector.php`, `QUEUE_CONNECTION=database` |
| **2. Python service persisten (ResNet50 load sekali)** | ✅ | `scripts/resnet_service.py` (Flask), dipanggil via `app/Services/ResnetServiceClient.php` |
| **3. Perbaikan environment Python** | ✅ | env di-set di `resnet_service.py`; bobot korup diunduh ulang |
| **4. Re-index & sinkron hapus vektor** | ✅ | `php artisan spareparts:reindex`, endpoint `/delete` |
| **5. Server multi-worker (bukan artisan serve)** | ✅ | Gunakan Laragon (Apache/Nginx + PHP-FPM) — lihat bawah |
| **6. Flag `is_indexed` & status ke user** | ✅ | Kolom `indexed` di DataTables, pesan sukses di controller |

---

## Cara Menjalankan (3 jendela)

### 1) Python ResNet50 Service (WAJIB, biarkan terbuka)

```bat
scripts\start_resnet_service.bat
```

Tunggu sampai muncul: `[resnet_service] Model ResNet50 siap.` dan
`Running on http://127.0.0.1:5005`.

> Pertama kali start bisa 30–90 detik karena memuat model. Setelah itu
> setiap request index/search hanya < 1 detik.

### 2) Laravel Queue Worker (WAJIB, biarkan terbuka)

```bat
scripts\start_queue_worker.bat
```

Atau langsung: `php artisan queue:work --tries=3 --timeout=300`

Worker ini yang mengeksekusi indexing vektor di background.

### 3) Web Server

**Opsi A (disarankan): Laragon** — aktifkan Apache/Nginx + PHP-FPM, arahkan
document root ke folder `public/` proyek ini. Multi-worker → request tidak saling mengantri.

**Opsi B: `php artisan serve`** — hanya untuk uji cepat. Tetap single-threaded,
jadi jangan pakai untuk pemakaian nyata.

```powershell
php artisan serve
```

---

## Perintah Berguna

```powershell
# Re-index SEMUA sparepart ke ChromaDB (masuk queue)
php artisan spareparts:reindex

# Re-index langsung tanpa queue (butuh Python service hidup)
php artisan spareparts:reindex --sync

# Cek kesehatan Python service + jumlah vektor terindeks
curl http://127.0.0.1:5005/health

# Proses queue sekali (debug)
php artisan queue:work --once

# Lihat job yang gagal
php artisan queue:failed
```

---

## Catatan Teknis

- Konfigurasi service Python di `.env`: `PY_SERVICE_URL=http://127.0.0.1:5005`.
- Bila Python service **mati**, maka:
  - Tambah data tetap **cepat** (tidak memblokir) — hanya `is_indexed` tetap `0`.
  - Pencarian gambar akan menampilkan pesan error yang jelas (bukan hang).
- Kolom `is_indexed` di tabel sparepart menandai apakah vektor sudah masuk ChromaDB.
- Saat gambar utama dihapus, vektor lama ikut dihapus/di-index ulang secara otomatis.

### Pencarian Gambar: skor kemiripan & urutan hasil
- ChromaDB memakai metrik **cosine** (`hnsw:space=cosine`) dan vektor fitur
  di-**L2-normalisasi**, sehingga skor kemiripan akurat (0–100%).
- `search_vector` mengembalikan `ids` **dan** `scores`, yang sudah diurutkan dari
  **paling mirip**. Hasil paling mirip ditampilkan **paling kiri** dan diberi
  badge *"Paling Mirip"*.
- Rumus skor: `similarity = 1 - cosine_distance` → dikali 100.

> ⚠️ **Penting bila collection lama masih memakai metrik L2:** hapus collection
> lalu re-index agar skor benar:
>
> ```powershell
> # 1) Hentikan service, hapus collection lama
> Get-Process python | Stop-Process -Force
> venv-tf\Scripts\python.exe -c "import chromadb; c=chromadb.PersistentClient(path=r'storage\app\chroma_db'); c.delete_collection('sparepart_images'); print('deleted')"
>
> # 2) Jalankan service + re-index
> scripts\start_resnet_service.bat        # jendela 1
> php artisan spareparts:reindex --sync   # setelah service siap
> ```