<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Sparepart;
use App\Models\Category;
// Tambahkan ini:
use Symfony\Component\Process\Process; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 

class SearchController extends Controller
{
    public function index() 
    {
        // KOREKSI: Inisialisasi variabel untuk menghindari error Undefined Variable di Blade
        return view('search', [
            'results' => [
                'brands' => collect(), 
                'spareparts' => collect()
            ],
            'query' => null // Setel null atau string kosong
        ]); 
    }

    /**
     * Memproses request pencarian teks dari form.
     */
    public function performSearch(Request $request)
    {
        $query = $request->input('search_query');

        if (empty($query)) {
            return view('search', ['results' => [], 'query' => '']);
        }
        
        $searchTerm = '%' . $query . '%';
        
        // 1. Pencarian data Brand
        $brands = Brand::where('nama_brand', 'like', $searchTerm)
                        ->limit(5)
                        ->get();

        // 2. Pencarian data Sparepart
        $spareparts = Sparepart::with(['brand', 'category', 'images']) 
                                ->where(function ($q) use ($searchTerm) {
                                    $q->where('kode_barang', 'like', $searchTerm)
                                    ->orWhere('nama_barang', 'like', $searchTerm)
                                    ->orWhere('deskripsi', 'like', $searchTerm);
                                })
                                ->limit(20) 
                                ->get();

        // 3. Gabungkan hasil
        $results = [
            'brands' => $brands,
            'spareparts' => $spareparts,
        ];

        return view('search', [
            'results' => $results,
            'query' => $query
        ]);
    }

    /**
     * Memproses request pencarian berdasarkan gambar menggunakan CBIR Python/OpenCV.
     */
    public function performImageSearch(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);
        
        // 2. Simpan gambar query sementara
        $file = $request->file('image');
        $queryPath = $file->store('temp_query_images', 'local'); // Simpan di disk local
        $queryImagePath = storage_path('app/' . $queryPath);

        // 3. Jalankan Skrip Python Pencarian
        $command = [
            base_path('venv-tf/Scripts/python.exe'), // <-- Alamat BARU yang benar
            base_path('scripts/search_vector.py'),
            $queryImagePath,
        ];
        
        $process = new Process($command);
        $process->setTimeout(300);
        // KOREKSI: Set environment variable USERPROFILE sebelum menjalankan
        $process->setEnv(['USERPROFILE' => storage_path()]); // Arahkan ke folder storage
        $process->run();

        // Hapus gambar query sementara setelah digunakan
        Storage::disk('local')->delete($queryPath);

        // 4. Proses Hasil
        if (!$process->isSuccessful()) {
            Log::error("Python Search Failed: " . $process->getErrorOutput());
            return redirect()->route('search.index')->withErrors(['image_search' => 'Gagal memproses gambar. Terjadi kesalahan pada server.']);
        }

        $similarProductIds = json_decode(trim($process->getOutput()), true) ?? [];
        
        // Konversi ID dari string ke integer jika perlu
        $similarProductIds = array_map('intval', $similarProductIds);

        $products = collect();
        if (!empty($similarProductIds)) {
            $products = Sparepart::with(['brand', 'category', 'images'])
                                ->whereIn('id', $similarProductIds)
                                // Urutkan berdasarkan urutan dari ChromaDB
                                ->orderByRaw("FIELD(id, " . implode(',', $similarProductIds) . ")")
                                ->get();
        }
        
        // 5. Tampilkan Hasil
        $results = [
            'brands' => collect(),
            'spareparts' => $products,
        ];

        return view('search', [
            'results' => $results,
            'query' => "Pencarian Gambar",
            'is_image_search' => true 
        ]);
    }

    
}