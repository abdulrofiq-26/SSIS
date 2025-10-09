<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sparepart;

class SparepartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareparts = [
            [
                'kode_barang' => '114-000022',
                'nama_barang' => 'GREASE, SPEC =  MOBILUX EP 0',
                'deskripsi' => 'Untuk Perdam gesekan pada bearing, joint, dll',
                'brand_id' => 1,
                'category_id' => 1,
                'lokasi_penyimpanan' => '1414',
                'jumlah_stok' => 1260000,
                'jumlah_satuan' => 'Kg',
                'gambar_utama' => 'gris.Jpg',
            ],
            [
                'kode_barang' => '114-000023',
                'nama_barang' => 'GREASE, SPEC =  MOBILUX EP 2',
                'deskripsi' => 'Untuk Perdam gesekan pada bearing, joint, dll',
                'brand_id' => 2,
                'category_id' => 2,
                'lokasi_penyimpanan' => '1414',
                'jumlah_stok' => 3053186,
                'jumlah_satuan' => 'Kg',
                'gambar_utama' => 'gris.Jpg',
            ],
        ];
        Sparepart::insert($spareparts);
    }
}
