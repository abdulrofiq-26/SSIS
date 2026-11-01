<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SparepartImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SparepartsImport;
use App\Exports\SparepartsTemplateExport;
use Yajra\DataTables\Facades\DataTables;

use App\Jobs\IndexSparepartVector;
use App\Services\ResnetServiceClient;
use Illuminate\Support\Facades\Log;

class SparepartController extends Controller
{
    /**
     * Menampilkan daftar sparepart.
     */
    public function index()
    {
        // ⭐ DIUBAH: Tidak perlu kirim data, DataTables yang akan ambil
        return view('spareparts.index');
    }

    /**
     * Data untuk Datatables (Server-Side Processing).
     */
    public function data(Request $request)
    {
        // Join dengan brand dan category untuk searching/sorting
        $query = Sparepart::select('spareparts.*', 'brands.nama_brand', 'categories.nama_kategori')
            ->leftJoin('brands', 'spareparts.brand_id', '=', 'brands.id')
            ->leftJoin('categories', 'spareparts.category_id', '=', 'categories.id')
            ->with('images'); // Eager load relasi images

        return DataTables::of($query)
            // Filter
            ->filter(function ($query) use ($request) {
                if ($request->filled('search')) {
                    $search = $request->search['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('spareparts.kode_barang', 'like', "%{$search}%")
                          ->orWhere('spareparts.nama_barang', 'like', "%{$search}%")
                          ->orWhere('brands.nama_brand', 'like', "%{$search}%")
                          ->orWhere('categories.nama_kategori', 'like', "%{$search}%");
                    });
                }
            })
            // Kolom IDD (untuk ID unik di JS)
            ->addColumn('idd', function ($sparepart) {
                return $sparepart->id;
            })
            // Kolom Stok (Gabungan)
            ->addColumn('stok', function ($sparepart) {
                return $sparepart->jumlah_stok . ' ' . $sparepart->jumlah_satuan;
            })
            // Kolom Gambar (Hanya ambil path gambar pertama)
            ->addColumn('gambar', function ($sparepart) {
                $mainImage = $sparepart->images->first();
                return $mainImage ? $mainImage->path : null;
            })
            // REK 6: Kolom status indexing vektor (tampil ke user, tidak silent fail)
            ->addColumn('indexed', function ($sparepart) {
                return $sparepart->is_indexed ? 1 : 0;
            })
            // Kolom Aksi (Placeholder)
            ->addColumn('action', function ($sparepart) {
                return '';
            })
            // Tentukan kolom mana yang bukan dari DB / perlu di-render
            ->rawColumns(['idd', 'action', 'gambar', 'indexed'])
            ->make(true);
    }

    public function create()
    {
        $brands = Brand::orderBy('nama_brand')->get();
        $categories = Category::orderBy('nama_kategori')->get();
        return view('spareparts.create', compact('brands', 'categories'));
    }

    /**
     * Menyimpan data sparepart baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kode_barang' => ['required', 'string', 'max:255', 'unique:spareparts,kode_barang'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:255'],
            'jumlah_stok' => ['nullable', 'integer', 'min:0'],
            'jumlah_satuan' => ['nullable', 'string', 'max:50'],
            'gambar_tambahan' => ['required', 'array', 'min:1'],
            'gambar_tambahan.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        $dataToStore = array_diff_key($validatedData, array_flip(['gambar_tambahan']));
        $sparepart = Sparepart::create($dataToStore);

        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);

            $sparepart->refresh();

            $mainImage = $sparepart->images()->orderBy('order', 'asc')->first();
            if ($mainImage) {
                // REK 1: Indexing dijalankan di BACKGROUND (queue), TIDAK memblokir request.
                IndexSparepartVector::dispatch($sparepart->id, $mainImage->path);
            }
        }

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan! Gambar sedang diproses untuk pencarian visual.');
    }

    protected function saveImages(array $files, int $sparepartId)
    {
        $lastOrder = SparepartImage::where('sparepart_id', $sparepartId)->max('order') ?? -1;
        $order = $lastOrder + 1;
        foreach ($files as $file) {
            $path = $file->store('spareparts', 'public'); 
            SparepartImage::create([
                'sparepart_id' => $sparepartId,
                'path' => $path, 
                'order' => $order++,
            ]);
        }
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Sparepart $sparepart)
    {
        $sparepart->load('images');
        $brands = Brand::orderBy('nama_brand')->get();
        $categories = Category::orderBy('nama_kategori')->get();
        return view('spareparts.edit', compact('sparepart', 'brands', 'categories'));
    }

    /**
     * Mengupdate data sparepart.
     */
    public function update(Request $request, Sparepart $sparepart)
    {
        $validatedData = $request->validate([
            'kode_barang' => ['required', 'string', 'max:255', Rule::unique('spareparts', 'kode_barang')->ignore($sparepart->id)],
            'nama_barang' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:255'],
            'jumlah_stok' => ['nullable', 'integer', 'min:0'],
            'jumlah_satuan' => ['nullable', 'string', 'max:50'],
            'gambar_tambahan' => ['nullable', 'array'], 
            'gambar_tambahan.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);
        
        $dataToUpdate = array_diff_key($validatedData, array_flip(['gambar_tambahan'])); 
        $sparepart->update($dataToUpdate);
        
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);

            $sparepart->refresh();

            $mainImage = $sparepart->images()->orderBy('order', 'asc')->first();
            if ($mainImage) {
                // REK 1: Indexing di background (queue), tidak memblokir request.
                IndexSparepartVector::dispatch($sparepart->id, $mainImage->path);
            }
        }
        
        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil diperbarui!');
    }

    /**
     * Menghapus gambar tambahan via AJAX.
     */
    public function deleteImage(SparepartImage $image): JsonResponse
    {
        try {
            $sparepartId = $image->sparepart_id;
            $isMainImage = $image->sparepart
                && $image->sparepart->images()->orderBy('order', 'asc')->first()?->id === $image->id;

            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();

            // REK 4 & 6: Sinkronkan ulang vektor bila gambar utama dihapus.
            $sparepart = Sparepart::find($sparepartId);
            if ($sparepart) {
                $nextMainImage = $sparepart->images()->orderBy('order', 'asc')->first();

                if ($isMainImage) {
                    if ($nextMainImage) {
                        // Masih ada gambar lain -> index ulang gambar utama yang baru.
                        $sparepart->update(['is_indexed' => false]);
                        IndexSparepartVector::dispatch($sparepart->id, $nextMainImage->path);
                    } else {
                        // Tidak ada gambar tersisa -> hapus vektor lama dari ChromaDB.
                        try {
                            app(ResnetServiceClient::class)->deleteVector($sparepart->id);
                        } catch (\Throwable $e) {
                            Log::warning('Gagal menghapus vektor ChromaDB (deleteImage) #' . $sparepart->id . ': ' . $e->getMessage());
                        }
                        $sparepart->update(['is_indexed' => false, 'vector_path' => null]);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus.', 'imageId' => $image->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    /**
    * Menghapus data sparepart (Via AJAX dari SweetAlert).
    */
    public function delete(Request $request)
    {
        $sparepart = Sparepart::find($request->id);

        if (!$sparepart) {
            return response()->json(['success' => false, 'message' => 'Sparepart tidak ditemukan.'], 404);
        }

        try {
            // REK 4: Hapus vektor terkait dari ChromaDB via Python service.
            try {
                app(ResnetServiceClient::class)->deleteVector($sparepart->id);
            } catch (\Throwable $e) {
                Log::warning('Gagal menghapus vektor ChromaDB untuk sparepart #' . $sparepart->id . ': ' . $e->getMessage());
            }

            // Hapus file vektor .pkl (legacy) bila ada
            if ($sparepart->vector_path && Storage::disk('public')->exists($sparepart->vector_path)) {
                Storage::disk('public')->delete($sparepart->vector_path);
            }

            // Hapus semua gambar terkait
            $sparepart->images->each(function ($image) {
                Storage::disk('public')->delete($image->path);
                // Hapus record gambar (jika model SparepartImage tidak di-cascade delete)
                $image->delete();
            });

            // Hapus record sparepart
            $sparepart->delete();

            return response()->json(['success' => true, 'message' => 'Sparepart berhasil dihapus.'], 200);

        } catch (\Exception $e) {
            // Tangkap jika ada error relasi (jika sparepart dipakai di tabel lain)
            if (str_contains($e->getMessage(), 'SQLSTATE[23000]')) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus: Sparepart ini mungkin digunakan oleh data lain.'], 422);
            }
            return response()->json(['success' => false, 'message' => 'Gagal menghapus sparepart: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        // ... (Kode import Anda tidak berubah)
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new SparepartsImport, $request->file('file'));
            return redirect()->route('spareparts.index')->with('success', 'Import data sparepart berhasil dilakukan!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return redirect()->route('spareparts.index')
                    ->with('error', 'Gagal impor! Terdapat ' . count($failures) . ' kesalahan validasi.')
                    ->with('import_errors', $errors);
        } catch (\Exception $e) {
            return redirect()->route('spareparts.index')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $filename = 'template_import_sparepart_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new SparepartsTemplateExport, $filename);
    }

    // REK 1 & 2: Indexing kini ditangani oleh App\Jobs\IndexSparepartVector
    // yang memanggil Python service (ResNetServiceClient) di background.
    // Method _runVectorExtraction (pemanggilan `python script.py` sinkron)
    // SUDAH DIHAPUS karena menjadi penyebab utama lambatnya request.
}