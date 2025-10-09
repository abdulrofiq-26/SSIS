<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    
    // Opsional, pastikan nama tabel sesuai
    protected $table = 'brands'; 

    protected $fillable = [
        'nama_brand',
        'logo_brand', 
    ];

    public function spareparts()
    {
        // Asumsi Brand memiliki banyak Sparepart
        return $this->hasMany(Sparepart::class);
    }
}
