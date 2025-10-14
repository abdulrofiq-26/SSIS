<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrandsTemplateExport implements FromArray, WithHeadings
{
    /**
    * Baris data contoh di template.
    */
    public function array(): array
    {
        return [
            ['Mitshubisi', 'logos/mitsubisi.png'],
            ['Denso', null], // Contoh brand tanpa path logo
        ];
    }

    /**
    * Mendefinisikan header kolom.
    */
    public function headings(): array
    {
        return [
            'nama_brand',
            'logo_brand',
        ];
    }
}