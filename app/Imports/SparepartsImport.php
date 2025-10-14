<?php

namespace App\Imports;

use App\Models\Sparepart;
use App\Models\Brand;      // ⭐ TAMBAHKAN INI ⭐
use App\Models\Category;   // ⭐ TAMBAHKAN INI ⭐
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable; // Optional, tapi bagus untuk digunakan

class SparepartsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        // 1. Mencari ID Brand berdasarkan Nama Brand
        $brand = Brand::where('nama_brand', $row['nama_brand'])->first();
        
        // 2. Mencari ID Category berdasarkan Nama Category
        $category = Category::where('nama_kategori', $row['nama_kategori'])->first();
        
        // Cek validasi data yang sudah dipastikan ada oleh rules()
        if (!$brand || !$category) {
            // Ini seharusnya dicegah oleh rules(), tapi ini sebagai fallback
            return null; 
        }

        return new Sparepart([
            // Kolom tetap
            'kode_barang' => $row['kode_barang'], 
            'nama_barang' => $row['nama_barang'],
            'deskripsi' => $row['deskripsi'] ?? null,
            
            // ⭐ MENGGUNAKAN ID YANG DITEMUKAN DARI NAMA ⭐
            'brand_id' => $brand->id, 
            'category_id' => $category->id, 
            
            // Kolom lainnya
            'lokasi_penyimpanan' => $row['lokasi_penyimpanan'] ?? null,
            'jumlah_stok' => $row['jumlah_stok'] ?? 0,
            'jumlah_satuan' => $row['jumlah_satuan'] ?? null,
        ]);
    }
    
    /**
     * Menentukan aturan validasi untuk setiap baris Excel.
     * Pastikan nama brand dan kategori yang dimasukkan pengguna ada.
     */
    public function rules(): array
    {
        return [
            // ⭐ Excel harus memiliki header 'nama_brand' dan 'nama_kategori' ⭐
            
            'kode_barang' => 'required|string|unique:spareparts,kode_barang',
            'nama_barang' => 'required|string',
            
            // Validasi: Pastikan nama brand ada di tabel brands pada kolom nama_brand
            'nama_brand' => 'required|string|exists:brands,nama_brand', 
            
            // Validasi: Pastikan nama kategori ada di tabel categories pada kolom nama_kategori
            'nama_kategori' => 'required|string|exists:categories,nama_kategori',
            
            'jumlah_stok' => 'nullable|integer|min:0',
            // Kolom lain (deskripsi, dll) tidak perlu diubah.
        ];
    }
}