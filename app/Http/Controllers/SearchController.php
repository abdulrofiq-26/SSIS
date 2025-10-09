<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Sparepart;
use App\Models\Category; // Pastikan model ini digunakan

class SearchController extends Controller
{
    /**
     * Menampilkan view untuk form pencarian.
     * Kita asumsikan ini adalah home page atau halaman utama search.
     */
    public function index()
    {
        return view('search'); // 'search' merujuk ke search.blade.php
    }

    /**
     * Memproses request pencarian dari form.
     */
    public function performSearch(Request $request)
    {
        // 1. Ambil keyword pencarian (query)
        $query = $request->input('search_query');

        // Pastikan query tidak kosong
        if (empty($query)) {
            // Jika kosong, kembalikan ke halaman search tanpa hasil
            return view('search', ['results' => [], 'query' => '']);
        }
        
        // Wildcard untuk pencarian LIKE
        $searchTerm = '%' . $query . '%';
        $brands = Brand::where('nama_brand', 'like', $searchTerm)
                        ->limit(5) // Batasi hasil brand
                        ->get();

        // 3. Pencarian data Sparepart
        // Eager Loading relasi 'brand' dan 'category'
        // agar kolom-kolom dari tabel tersebut juga tersedia tanpa N+1 query.
        $spareparts = Sparepart::with(['brand', 'category']) // Asumsi nama relasi di model Sparepart adalah 'brand' dan 'category'
                                ->where(function ($q) use ($searchTerm) {
                                    $q->where('kode_barang', 'like', $searchTerm)
                                    ->orWhere('nama_barang', 'like', $searchTerm)
                                    ->orWhere('deskripsi', 'like', $searchTerm) 
                                     // Bisa juga mencari berdasarkan brand_id atau category_id jika query-nya adalah ID
                                     // ->orWhere('brand_id', 'like', $searchTerm) 
                                     // ->orWhere('category_id', 'like', $searchTerm) 
                                    ;
                                })
                                ->limit(20) // Batasi hasil sparepart
                                ->get();

        // 4. Gabungkan hasil
        $results = [
            'brands' => $brands,
            'spareparts' => $spareparts,
        ];

        // 5. Kembalikan view dengan hasil pencarian
        return view('search', [
            'results' => $results,
            'query' => $query
        ]);
    }
}