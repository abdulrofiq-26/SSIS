<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BrandsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Masukkan data baru. logo_brand bersifat opsional/nullable
        return new Brand([
            'nama_brand' => $row['nama_brand'], 
            'logo_brand' => $row['logo_brand'] ?? null,
        ]);
    }
    
    /**
     * Aturan validasi untuk data Excel.
     */
    public function rules(): array
    {
        return [
            // Pastikan nama brand wajib diisi dan unik di database
            'nama_brand' => 'required|string|unique:brands,nama_brand', 
            'logo_brand' => 'nullable|string',
        ];
    }
}