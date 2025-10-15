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

// KOREKSI: Tambahkan 'use' statement yang dibutuhkan untuk indexing
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class SparepartController extends Controller
{
    /**
     * Menampilkan daftar sparepart.
     */
    public function index()
    {
        $spareparts = Sparepart::with(['brand', 'category', 'images'])->paginate(10); 
        $brands = Brand::orderBy('nama_brand')->get(); 
        $categories = Category::orderBy('nama_kategori')->get();
        return view('spareparts.index', compact('spareparts', 'brands', 'categories'));
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

            // KOREKSI: Panggil fungsi indexing setelah gambar disimpan
            // Kita ambil gambar pertama sebagai "gambar utama" untuk diindeks
            $mainImage = $sparepart->images()->orderBy('order', 'asc')->first();
            if ($mainImage) {
                $this->_runVectorExtraction($sparepart, $mainImage->path);
            }
        }

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan!');
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

            // KOREKSI: Panggil ulang fungsi indexing jika ada gambar baru
            // Ini penting agar "sidik jari visual" sparepart selalu yang terbaru
            $sparepart->refresh(); // Muat ulang data sparepart beserta gambar barunya
            $mainImage = $sparepart->images()->orderBy('order', 'asc')->first();
            if ($mainImage) {
                $this->_runVectorExtraction($sparepart, $mainImage->path);
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
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
            return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus.', 'imageId' => $image->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus data sparepart.
     */
    public function destroy(Sparepart $sparepart)
    {
        // KOREKSI: Hapus juga file vektor .pkl saat sparepart dihapus
        if ($sparepart->vector_path && Storage::disk('public')->exists($sparepart->vector_path)) {
            Storage::disk('public')->delete($sparepart->vector_path);
        }

        $sparepart->images->each(function ($image) {
            Storage::disk('public')->delete($image->path);
        });
        $sparepart->delete();
        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil dihapus!');
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

    // KOREKSI: Tambahkan fungsi private untuk menjalankan skrip Python
    /**
     * Menjalankan skrip Python untuk mengekstrak vektor fitur dari gambar.
     */
    private function _runVectorExtraction(Sparepart $sparepart, string $imagePath)
    {
        $absoluteImagePath = storage_path('app/public/' . $imagePath);
        $vectorFileName = 'sparepart_' . $sparepart->id . '_' . time() . '.pkl';
        $outputVectorPath = storage_path('app/public/vectors/' . $vectorFileName);

        $command = [
            base_path('venv/Scripts/python.exe'),
            base_path('scripts/extract_vector.py'),
            $absoluteImagePath,
            $outputVectorPath,
        ];

        $process = new Process($command);
        $process->run();

        if ($process->isSuccessful()) {
            // Hapus file vektor lama jika ada sebelum menyimpan yang baru
            if ($sparepart->vector_path && Storage::disk('public')->exists($sparepart->vector_path)) {
                Storage::disk('public')->delete($sparepart->vector_path);
            }
            
            $sparepart->is_indexed = true;
            $sparepart->vector_path = 'vectors/' . $vectorFileName; // Simpan path relatif
            $sparepart->save();
        } else {
            Log::error('Vector Extraction Failed for sparepart ID ' . $sparepart->id . ': ' . $process->getErrorOutput());
        }
    }
}