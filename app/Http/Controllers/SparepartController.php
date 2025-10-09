<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;      // Asumsi model Brand sudah ada
use App\Models\Category;   // Asumsi model Category sudah ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\SparepartImage; // ⭐ Import Model baru
class SparepartController extends Controller
{
    /**
     * Menampilkan daftar sparepart.
     */
    public function index()
    {
        // Mengambil data sparepart dengan relasi Brand dan Category, serta pagination
        $spareparts = Sparepart::with(['brand', 'category'])->paginate(10); // Sesuaikan jumlah per halaman
        $brands = Brand::orderBy('nama_brand')->get(); // Diperlukan untuk modal tambah
        $categorys = Category::orderBy('nama_kategori')->get(); // Diperlukan untuk modal tambah

        return view('spareparts.index', compact('spareparts', 'brands',));
    }

    public function create()
    {
        // Ambil semua Brand dan Category untuk dropdown
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
            // ⭐ VALIDASI BARU untuk banyak gambar
            'gambar_tambahan' => ['nullable', 'array'], 
            'gambar_tambahan.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:20048'], 
        ]);

        // 1. Buat Sparepart utama (tanpa gambar)
        $sparepart = Sparepart::create($validatedData);

        // 2. Proses dan simpan gambar-gambar tambahan
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);
        }

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan!');
    }

    protected function saveImages(array $files, int $sparepartId)
    {
        $order = 0;
        foreach ($files as $file) {
            // ⭐ Menyimpan ke folder 'spareparts' di disk 'public'
            $path = $file->store('spareparts', 'public'); 

            SparepartImage::create([
                'sparepart_id' => $sparepartId,
                'path' => $path, // path akan berbentuk: 'spareparts/namafile.jpg'
                'order' => $order++,
            ]);
        }
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Sparepart $sparepart)
    {
        $brands = Brand::orderBy('nama_brand')->get();
        $categorys = Category::orderBy('nama_kategori')->get();

        return view('spareparts.edit', compact('sparepart', 'brands', 'categorys'));
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
            // ⭐ VALIDASI BARU untuk banyak gambar
            'gambar_tambahan' => ['nullable', 'array'], 
            'gambar_tambahan.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:20000'],
        ]);

        if ($request->hasFile('gambar_utama')) {
            // 1. Hapus gambar lama (Path yang tersimpan di DB sudah relatif ke disk 'public')
            if ($sparepart->gambar_utama && Storage::disk('public')->exists($sparepart->gambar_utama)) {
                Storage::disk('public')->delete($sparepart->gambar_utama); 
            }
            
            // 2. Simpan gambar baru
            // KOREKSI: Menggunakan disk 'public' secara eksplisit
            $path = $request->file('gambar_utama')->store('spareparts', 'public');
            $validatedData['gambar_utama'] = $path;
        }

        $sparepart->update($validatedData);

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil diperbarui!');
    }

    /**
     * Menghapus data sparepart.
     */
    public function destroy(Sparepart $sparepart)
    {
        // ⭐ PENGHAPUSAN: Relasi onDelete('cascade') di migrasi 
        $sparepart->images->each(function ($image) {
            Storage::disk('public')->delete($image->path);
        });

        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil dihapus!');
    }
}