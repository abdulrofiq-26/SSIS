<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sparepart_images', function (Blueprint $table) {
            $table->id(); // <--- BARIS KRITIS YANG HILANG DAN SUDAH DITAMBAHKAN
            // Foreign key ke tabel spareparts
            $table->foreignId('sparepart_id')
                ->constrained('spareparts') // Pastikan menunjuk ke nama tabel Sparepart
                ->onDelete('cascade'); // Jika sparepart dihapus, gambar ikut terhapus
            $table->string('path'); // Path file gambar
            $table->integer('order')->default(0); // Untuk mengurutkan gambar (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sparepart_images');
    }
};
