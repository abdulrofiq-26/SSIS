<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;      
use App\Models\Category;   
use App\Models\SparepartImage; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SparepartController extends Controller
{
    /**
     * Menampilkan daftar sparepart.
     */
    public function index()
    {
        $spareparts = Sparepart::with(['brand', 'category', 'images'])->paginate(10); 
        $brands = Brand::orderBy('nama_brand')->get(); 
        // ⭐ KOREKSI 1: Menggunakan nama variabel $categories yang konsisten
        $categories = Category::orderBy('nama_kategori')->get(); 

        // ⭐ KOREKSI 2: Memasukkan $categories ke compact
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
            'gambar_tambahan' => ['required', 'array', 'min:1'], // Minimal harus ada 1 gambar
            'gambar_tambahan.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Ubah max:20048 ke 2048 (2MB) untuk standar
        ]);

        // 1. Buat Sparepart utama (tanpa gambar)
        // Hapus kunci gambar_tambahan dari validatedData agar tidak dimasukkan ke tabel Sparepart
        $dataToStore = array_diff_key($validatedData, array_flip(['gambar_tambahan']));
        $sparepart = Sparepart::create($dataToStore);

        // 2. Proses dan simpan gambar-gambar tambahan
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);
        }

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan!');
    }

    /**
     * Helper function untuk menyimpan file gambar.
     */
    protected function saveImages(array $files, int $sparepartId)
    {
        // Tentukan urutan berikutnya berdasarkan jumlah gambar yang sudah ada
        $lastOrder = SparepartImage::where('sparepart_id', $sparepartId)->max('order') ?? -1;
        $order = $lastOrder + 1;

        foreach ($files as $file) {
            // Menyimpan ke folder 'spareparts' di disk 'public'
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
        $sparepart->load('images'); // ⭐ Pastikan relasi images dimuat di sini
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
        
        $dataToUpdate = array_diff_key($validatedData, array_flip(['gambar_tambahan']));
        $sparepart->update($dataToUpdate);
        
        // 1. Proses dan simpan gambar-gambar tambahan BARU
        if ($request->hasFile('gambar_tambahan')) {
            $this->saveImages($request->file('gambar_tambahan'), $sparepart->id);
        }

        // ⭐ KOREKSI PENTING: Logika untuk menghapus gambar yang diminta
        if ($request->input('images_to_delete')) {
            // images_to_delete akan berisi string ID yang dipisahkan koma, 
            // misalnya "1,5,8". Kita pisahkan menjadi array.
            $this->deleteImages(explode(',', $request->input('images_to_delete')));
        }
        
        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil diperbarui!');
    }

    /**
     * Helper function untuk menghapus gambar berdasarkan ID.
     */
    protected function deleteImages(array $imageIds)
    {
        $images = SparepartImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            // Hapus file fisik dari storage
            Storage::disk('public')->delete($image->path);
            // Hapus record dari database
            $image->delete();
        }
    }
    
    /**
     * Menghapus data sparepart.
     */
    public function destroy(Sparepart $sparepart)
    {
        // Hapus file fisik gambar terkait
        $sparepart->images->each(function ($image) {
            Storage::disk('public')->delete($image->path);
        });

        // Hapus record Sparepart dan secara otomatis images-nya (karena onDelete('cascade'))
        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil dihapus!');
    }
}
