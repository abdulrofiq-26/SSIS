<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data Brand, urutkan berdasarkan nama, dan tambahkan pagination
        $brands = Brand::orderBy('nama_brand', 'asc')->paginate(10); 
        
        // Mengirimkan data brand dan juga data brand kosong untuk modal edit (best practice)
        return view('brands.index', [
            'brands' => $brands,
            'brand' => new Brand(), // Objek Brand kosong untuk inisialisasi form modal
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Menyimpan brand baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data
        $validatedData = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands,nama_brand',
            'logo_brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB
        ], [
            'nama_brand.unique' => 'Nama brand ini sudah ada.',
            'logo_brand.image' => 'File harus berupa gambar.',
        ]);

        // 2. Proses Upload Gambar (Logo)
        if ($request->hasFile('logo_brand')) {
            // Simpan gambar di folder 'public/logos'
            // Gunakan Storage::disk('public') untuk menyimpan di public/storage
            $path = $request->file('logo_brand')->store('logos', 'public');
            $validatedData['logo_brand'] = $path;
        }

        // 3. Simpan ke Database
        Brand::create($validatedData);

        return redirect()->route('brands.index')->with('success', 'Brand baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        // Biasanya untuk detail, tapi kita abaikan karena data ditampilkan di index
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        // Mengirimkan data brand yang spesifik ke view edit.
        return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        // Hapus blok try-catch yang membawa failed_update_brand_id
        
        // 1. Validasi Data
        $validatedData = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands,nama_brand,' . $brand->id,
            'logo_brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
        ]);
        
        // 2. Proses Upload Gambar/Logo Baru
        if ($request->hasFile('logo_brand')) {
            // Hapus logo lama jika ada
            if ($brand->logo_brand && Storage::disk('public')->exists($brand->logo_brand)) {
                Storage::disk('public')->delete($brand->logo_brand);
            }
            
            // Simpan logo baru
            $path = $request->file('logo_brand')->store('logos', 'public');
            $validatedData['logo_brand'] = $path;
        }

        // 3. Update ke Database
        $brand->update($validatedData);

        // Redirect ke index setelah sukses.
        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Hapus logo dari storage jika ada
        if ($brand->logo_brand && Storage::disk('public')->exists($brand->logo_brand)) {
            Storage::disk('public')->delete($brand->logo_brand);
        }
        
        // Hapus record dari database
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand berhasil dihapus.');
    }
}
