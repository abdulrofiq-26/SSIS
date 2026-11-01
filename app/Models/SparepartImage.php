<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparepartImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sparepart_id',
        'path',
        'order',
    ];

    // Relasi: Satu gambar dimiliki oleh satu Sparepart
    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    // Helper untuk mendapatkan URL gambar
    public function getUrlAttribute(): string
    {
        return route('storage.media', ltrim($this->path, '/'));
    }
}