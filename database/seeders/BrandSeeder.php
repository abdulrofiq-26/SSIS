<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // ... (Data array Anda yang sudah didefinisikan)
            [
                'nama_brand' => 'Mitshubisi',
                'logo_brand' => 'logos/Mitshubisi.png'
            ],
            [
                'nama_brand' => 'Denso',
                'logo_brand' => 'logos/honda.png'
            ],
        ];
        Brand::insert($brands);
    }
}
