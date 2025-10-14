<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SparepartsTemplateExport implements FromArray, WithHeadings
{
    /**
    * Mendefinisikan baris data yang akan diekspor (di sini, hanya satu baris kosong).
    */
    public function array(): array
    {
        // Baris ini kosong, hanya sebagai contoh template
        return [
            ['KB001', 'Baut Mesin', 'Toyota', 'Fast Moving', 50, 'Rack A-01', 'pcs'],
        ];
    }

    /**
    * Mendefinisikan header kolom (ini harus cocok dengan logika Import Class Anda).
    */
    public function headings(): array
    {
        return [
            'kode_barang',
            'nama_barang',
            'nama_brand',       // ⭐ PENTING: Menggunakan NAMA Brand, bukan ID
            'nama_kategori',    // ⭐ PENTING: Menggunakan NAMA Kategori, bukan ID
            'jumlah_stok',
            'lokasi_penyimpanan',
            'jumlah_satuan',
            'deskripsi'
        ];
    }
}