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
use App\Exports\SparepartsTemplateExport; // Tambahkan ini

class SparepartController extends Controller
{
    /**
     * Menampilkan daftar sparepart.
     */
    public function index()
    {
        // Memuat relasi images untuk gambar utama di tabel index
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

        // Buat Sparepart utama (hapus input file dari data utama)
        $dataToStore = array_diff_key($validatedData, array_flip(['gambar_tambahan']));
        $sparepart = Sparepart::create($dataToStore);

        // Proses dan simpan gambar-gambar tambahan
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);
        }

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan!');
    }

    protected function saveImages(array $files, int $sparepartId)
    {
        $lastOrder = SparepartImage::where('sparepart_id', $sparepartId)->max('order') ?? -1;
        $order = $lastOrder + 1;

        foreach ($files as $file) {
            // Menyimpan ke disk 'public'
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
        // Memuat relasi images untuk ditampilkan di form edit
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
            'kode_barang' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('spareparts', 'kode_barang')->ignore($sparepart->id)
            ],
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

        
        // --- 2. UPDATE DATA SPAREPART UTAMA ---
        // Hapus input file dari data update
        $dataToUpdate = array_diff_key($validatedData, array_flip(['gambar_tambahan'])); 
        $sparepart->update($dataToUpdate);
        
        // --- 3. PROSES TAMBAH GAMBAR BARU ---
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);
        }
        
        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil diperbarui!');
    }

    /**
     * Menghapus gambar tambahan via AJAX.
     */
    public function deleteImage(SparepartImage $image): JsonResponse
    {
        try {
            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            
            // Hapus record dari database
            $image->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Gambar berhasil dihapus.', 
                'imageId' => $image->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus gambar: ' . $e->getMessage()
            ], 500);
        }
    }

    
    /**
     * Menghapus data sparepart.
     */
    public function destroy(Sparepart $sparepart)
    {
        // Hapus file fisik semua gambar terkait
        $sparepart->images->each(function ($image) {
            Storage::disk('public')->delete($image->path);
        });

        // Hapus record Sparepart
        $sparepart->delete();

        // Ganti JSON Response dengan redirect kembali ke index
        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        
        try {
            // Gunakan facade Excel untuk mengimpor data
            Excel::import(new SparepartsImport, $request->file('file'));

            return redirect()->route('spareparts.index')->with('success', 'Import data sparepart berhasil dilakukan!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            
            // Kumpulkan pesan error validasi baris Excel
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            
            // Redirect dengan pesan error
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
        
        // Gunakan facade Excel untuk mendownload file
        return Excel::download(new SparepartsTemplateExport, $filename);
    }

}