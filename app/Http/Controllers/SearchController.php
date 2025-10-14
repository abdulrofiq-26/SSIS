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
        return view('search'); 
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
        // 1. Validasi Gambar Query
        $request->validate([
            'image' => 'required|image|max:5120', // Maksimal 5MB
        ]);
        
        // 2. Simpan Gambar Query Sementara
        $queryPath = $request->file('image')->store('temp_query_images');
        $queryImagePath = storage_path('app/' . $queryPath);

        // 3. Siapkan Metadata Vektor Produk untuk Python
        // Ambil hanya produk yang sudah di-index
        $indexedProducts = Sparepart::where('is_indexed', true)->get(['id', 'vector_path']);
        
        $metadata = $indexedProducts->map(function ($product) {
            return [
                'id' => $product->id,
                // Pastikan path ini benar-benar mengarah ke file .pkl
                'vector_path' => storage_path('app/public/' . $product->vector_path), 
            ];
        })->toArray();

        // Simpan metadata ke file JSON sementara
        $timestamp = time();
        $metadataJsonPath = storage_path("app/temp_query_metadata_{$timestamp}.json");
        file_put_contents($metadataJsonPath, json_encode($metadata));

        // 4. Jalankan Skrip Python Pencarian
        $command = [
            'python3',
            base_path('scripts/image_search.py'),
            $queryImagePath,
            $metadataJsonPath
        ];
        
        $process = new Process($command);
        $process->setTimeout(60); 
        $process->run();

        // 5. Proses Hasil
        $similarProductIds = [];
        $queryDisplay = "Pencarian Gambar"; // Label untuk View

        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $similarProductIds = json_decode(trim($output), true) ?? [];
        } else {
            // Jika Python error, log error dan redirect
            Log::error("Python Search Failed. Path: {$queryImagePath}. Output: " . $process->getErrorOutput());
            Storage::delete($queryPath);
            if (file_exists($metadataJsonPath)) unlink($metadataJsonPath);
            return redirect()->route('search.index')->withErrors(['image_search' => 'Gagal memproses gambar. Pastikan Python/OpenCV sudah terinstal dan data sudah di-index.']);
        }

        // 6. Ambil Produk dari DB (dengan mempertahankan urutan kemiripan)
        $products = collect();
        if (!empty($similarProductIds)) {
            $products = Sparepart::with(['brand', 'category', 'images'])
                       ->whereIn('id', $similarProductIds)
                       ->orderByRaw("FIELD(id, " . implode(',', $similarProductIds) . ")")
                       ->get();
        }
        
        // 7. Bersihkan file sementara
        Storage::delete($queryPath);
        if (file_exists($metadataJsonPath)) unlink($metadataJsonPath);
        
        // 8. Tampilkan Hasil
        $results = [
            'brands' => collect(), // Kosongkan, karena pencarian gambar hanya fokus ke sparepart
            'spareparts' => $products,
        ];

        return view('search', [
            'results' => $results,
            'query' => $queryDisplay,
            'is_image_search' => true 
        ]);
    }
}