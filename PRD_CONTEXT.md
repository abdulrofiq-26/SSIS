# SSIS (Sparepart Stock Information System) - Project Overview & Structure

Dokumen ini menjelaskan secara spesifik tujuan, fitur utama, dan struktur teknis dari proyek **SSIS** untuk membantu dalam penyusunan ulang *Product Requirements Document (PRD)*.

## 1. Tujuan Proyek
Proyek SSIS adalah sebuah aplikasi berbasis web yang dibangun menggunakan framework **Laravel**. Tujuan utama dari sistem ini adalah untuk mengelola Master Data suku cadang (*spareparts*), merek (*brands*), dan kategori, serta menyediakan fitur pencarian tingkat lanjut (termasuk pencarian berbasis gambar) untuk mempermudah identifikasi dan manajemen inventaris suku cadang. Sistem ini juga dilengkapi dengan manajemen otentikasi dan otorisasi pengguna yang dinamis berbasis peran (*role-based access control*).

## 2. Fitur Utama (Berdasarkan Analisis Kode)

### A. Manajemen Pengguna & Hak Akses (User Access Management)
- **Otentikasi & Profil**: Login, registrasi (opsional), pengaturan profil, dan reset password.
- **Manajemen Role**: Pembuatan, pengubahan, dan penghapusan *Role* (misal: Admin, User, dll).
- **Manajemen Permission**: Manajemen hak akses mendetail dan pemetaannya terhadap peran dan pengguna menggunakan library `spatie/laravel-permission`.

### B. Master Data Management (Manajemen Data Induk)
- **Manajemen Merek (Brands)**: CRUD (Create, Read, Update, Delete) data merek, dilengkapi dengan fitur unduh template dan **Import data via Excel**.
- **Manajemen Suku Cadang (Spareparts)**: CRUD data suku cadang, unggah/hapus multi-gambar (*Sparepart Images*), serta fitur Import dari Excel.
- **Manajemen Kategori (Categories)**: Pengkategorian suku cadang.
- *DataTables Integration*: Menampilkan data master dalam bentuk tabel interaktif dengan proses *server-side* yang cepat menggunakan `yajra/laravel-datatables-oracle`.

### C. Fitur Pencarian (Search Features)
- **Pencarian Teks**: Pencarian standar untuk mencari suku cadang berdasarkan nama, kategori, atau merek.
- **Pencarian Gambar (Image Search / CBIR)**: Terdapat *endpoint* khusus untuk mencari suku cadang dengan mengunggah gambar (detail teknologi di bagian 3.C).

### D. Fitur Pendukung Lainnya
- **Log Viewer**: Untuk memantau error dan log sistem secara langsung dari antarmuka web.

---

## 3. Struktur Teknis Proyek (Tech Stack & Architecture)

### Teknologi yang Digunakan:
- **Backend**: Laravel 10, PHP ^8.1
- **Database**: Relasional (MySQL/PostgreSQL) via Eloquent ORM.
- **Frontend / UI**: Blade Templates, Tailwind CSS (via Vite), SweetAlert (untuk konfirmasi aksi).
- **Library Penting**:
  - `spatie/laravel-permission` (Hak Akses)
  - `yajra/laravel-datatables-oracle` (Tabel Server-side)
  - `maatwebsite/excel` (Import/Export Excel)

### Struktur Direktori & Komponen Penting:
Proyek ini mengadopsi arsitektur MVC (Model-View-Controller) bawaan Laravel. Berikut adalah pemetaan fungsionalnya:

#### 1. Models (`app/Models/`)
Mendefinisikan entitas database dan relasinya:
- `Brand.php` : Model untuk Merek.
- `Category.php` : Model untuk Kategori.
- `Sparepart.php` : Model utama Suku Cadang.
- `SparepartImage.php` : Model untuk menyimpan foto/gambar suku cadang terkait.
- `User.php` : Model Pengguna sistem.

#### 2. Controllers (`app/Http/Controllers/`)
Menangani logika bisnis dan aliran data:
- `UserController.php`, `RoleController.php`, `PermissionController.php` : Menangani modul manajemen pengguna dan hak akses.
- `BrandController.php`, `CategoryController.php` : Menangani logika CRUD Master Data.
- `SparepartController.php`, `SparepartImageController.php` : Mengelola logika data suku cadang termasuk unggah/hapus file gambar dan impor Excel.
- `SearchController.php` : Mengontrol logika fitur pencarian teks dan pemrosesan *Image Search*.

#### 3. Routes (`routes/web.php`)
Mendefinisikan URL (titik akses) aplikasi:
- Dikelompokkan dengan rapi menggunakan awalan (prefix) seperti `manage_account/` untuk pengguna dan `master_data/` untuk *Brands* & *Spareparts*.
- Dilindungi oleh *middleware* otentikasi (`auth`) dan pengecekan otorisasi (*permissions*).

### 3.C. Teknologi Pencarian Gambar (Image Search / CBIR)

Fitur pencarian sparepart berdasarkan gambar/foto menggunakan pendekatan **CBIR (Content-Based Image Retrieval)**, yaitu pencarian berdasarkan kemiripan visual, bukan teks. Implementasinya adalah kolaborasi antara Laravel (PHP) dan Python:

- **Pendekatan**: *Feature Extraction* + *Vector Similarity Search* (Nearest Neighbor).
- **Model Deep Learning**: **ResNet50** (pre-trained pada dataset ImageNet, dari `tensorflow.keras.applications`), dipakai sebagai *feature extractor* tanpa lapisan klasifikasi atas (`include_top=False, pooling='avg'`). Setiap gambar diubah menjadi **vektor fitur 2048-dimensi** yang merepresentasikan ciri visualnya.
- **Framework ML**: **TensorFlow / Keras** — dijalankan di virtual environment Python terpisah (`venv-tf/`).
- **Vector Database**: **ChromaDB** (mode `PersistentClient`) — menyimpan seluruh vektor fitur gambar sparepart di `storage/app/chroma_db/` dalam collection `sparepart_images`, dan melakukan pencarian kemiripan vektor (*similarity search*).
- **Image Processing**: **Pillow (PIL)** dan utilitas pra-proses Keras — gambar query di-resize ke 224x224 piksel lalu diproses dengan `preprocess_input` ResNet50.
- **Integrasi Laravel ↔ Python**:
  - Laravel menyimpan sementara gambar unggahan ke disk `local` (`temp_query_images`), lalu mengeksekusi skrip Python via `Symfony\Component\Process\Process` dengan interpreter `venv-tf/Scripts/python.exe` (timeout 300 detik).
  - Skrip Python mengembalikan daftar ID sparepart paling mirip (default 5 hasil) dalam format **JSON** ke stdout.
  - Laravel mengambil data sparepart dari MySQL berdasarkan ID tersebut dan mengurutkannya sesuai ranking dari ChromaDB (`orderByRaw FIELD`).
- **Alur Kerja (Pipeline)**:
  1. *Indexing* — saat gambar sparepart diunggah, skrip `scripts/index_vector.py` mengekstrak vektor fitur dan menyimpannya ke ChromaDB dengan ID sparepart sebagai key.
  2. *Query* — pengguna mengunggah foto, skrip `scripts/search_vector.py` mengekstrak vektor gambar tersebut dan mencari 5 vektor terdekat di ChromaDB.
  3. *Render* — Laravel menampilkan sparepart hasil pencarian beserta merek, kategori, dan gambarnya.
- **File Terkait**:
  - `scripts/index_vector.py` : Indexing vektor gambar ke ChromaDB.
  - `scripts/search_vector.py` : Ekstraksi vektor gambar query + similarity search.
  - `app/Http/Controllers/SearchController.php` : Method `performImageSearch()` sebagai jembatan Laravel → Python.
  - `venv-tf/` : Virtual environment Python berisi TensorFlow, ChromaDB, Pillow, NumPy.

#### 4. Seeders (`database/seeders/`)
- `UserRolePermissionSeeder.php` : Skrip untuk membuat pengguna `Admin` default dengan peran `admin` beserta hak akses awalnya secara otomatis pada saat inisialisasi basis data.

---
*Dokumen ini dibuat otomatis berdasarkan struktur kode terkini dari repositori lokal Anda untuk keperluan penyusunan PRD.*
