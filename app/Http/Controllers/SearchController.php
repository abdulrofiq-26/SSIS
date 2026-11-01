<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Sparepart;
use App\Models\Category;
use App\Services\ResnetServiceClient;
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

        // 3. Jalankan pencarian lewat Python service (ResNet50 sudah ter-load di memori)
        /** @var ResnetServiceClient $client */
        $client = app(ResnetServiceClient::class);
        $result = $client->search($queryImagePath);

        // Hapus gambar query sementara setelah digunakan
        Storage::disk('local')->delete($queryPath);

        // 4. Proses Hasil
        if (!$result['success']) {
            Log::error("Python Image Search Failed: " . $result['message']);
            return redirect()->route('search.index')->withErrors([
                'image_search' => 'Gagal memproses gambar. Pastikan layanan pencarian gambar (Python service) sedang berjalan.'
            ]);
        }

        $similarProductIds = array_map('intval', $result['ids']);

        // Skor kemiripan (%) dari ChromaDB, sejajar indeks dengan $similarProductIds.
        // Sudah diurutkan dari yang paling mirip (tertinggi).
        $scoresRaw = $result['scores'] ?? [];
        $scores = array_map(function ($s) {
            return is_numeric($s) ? (float) $s : null;
        }, is_array($scoresRaw) ? $scoresRaw : []);

        $products = collect();
        if (!empty($similarProductIds)) {
            $products = Sparepart::with(['brand', 'category', 'images'])
                                ->whereIn('id', $similarProductIds)
                                // Urutkan berdasarkan urutan dari ChromaDB (paling mirip dulu)
                                ->orderByRaw("FIELD(id, " . implode(',', $similarProductIds) . ")")
                                ->get();

            // Tempelkan skor kemiripan ke tiap produk berdasarkan ID.
            $scoreById = [];
            foreach ($similarProductIds as $i => $id) {
                $scoreById[(int) $id] = $scores[$i] ?? null;
            }
            $products->each(function ($product) use ($scoreById) {
                $product->similarity_score = $scoreById[$product->id] ?? null;
            });
            // Pastikan urutan tetap sesuai ranking ChromaDB (paling mirip paling kiri).
            $products = $products->sortBy(function ($p) use ($similarProductIds) {
                return array_search($p->id, $similarProductIds, true);
            })->values();
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