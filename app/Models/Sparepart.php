<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'deskripsi',
        'brand_id',
        'category_id',
        'lokasi_penyimpanan',
        'jumlah_stok',
        'jumlah_satuan',
        // HAPUS 'gambar_utama' dari sini
    ];
    
    // Relasi dengan Brand dan Category (Tetap ada)
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ⭐ RELASI BARU: Sparepart memiliki banyak gambar tambahan
    public function images(): HasMany
    {
        return $this->hasMany(SparepartImage::class)->orderBy('order');
    }

    // 💡 Helper untuk mendapatkan gambar utama (Gambar pertama dari koleksi images)
    public function getGambarUtamaAttribute()
    {
        return $this->images->first();
    }
}