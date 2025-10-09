<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;      // Asumsi model Brand sudah ada
use App\Models\Category;   // Asumsi model Category sudah ada
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
        // Mengambil data sparepart dengan relasi Brand dan Category, serta pagination
        $spareparts = Sparepart::with(['brand', 'category'])->paginate(10); // Sesuaikan jumlah per halaman
        $brands = Brand::orderBy('nama_brand')->get(); // Diperlukan untuk modal tambah
        $categories = Category::orderBy('nama_category')->get(); // Diperlukan untuk modal tambah

        return view('spareparts.index', compact('spareparts', 'brands', 'categories'));
    }

    /**
     * Menyimpan data sparepart baru.
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
            'gambar_utama' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('gambar_utama')) {
            $path = $request->file('gambar_utama')->store('public/spareparts');
            $validatedData['gambar_utama'] = str_replace('public/', '', $path);
        }

        Sparepart::create($validatedData);

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Sparepart $sparepart)
    {
        $brands = Brand::orderBy('nama_brand')->get();
        $categories = Category::orderBy('nama_category')->get();

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
            'gambar_utama' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('gambar_utama')) {
            // Hapus gambar lama jika ada
            if ($sparepart->gambar_utama) {
                Storage::delete('public/' . $sparepart->gambar_utama);
            }
            
            $path = $request->file('gambar_utama')->store('public/spareparts');
            $validatedData['gambar_utama'] = str_replace('public/', '', $path);
        }

        $sparepart->update($validatedData);

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil diperbarui!');
    }

    /**
     * Menghapus data sparepart.
     */
    public function destroy(Sparepart $sparepart)
    {
        // Hapus gambar terkait
        if ($sparepart->gambar_utama) {
            Storage::delete('public/' . $sparepart->gambar_utama);
        }

        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('success', 'Sparepart berhasil dihapus!');
    }
}