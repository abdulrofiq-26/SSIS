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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->text('deskripsi')->nullable();
            // Kolom Foreign Key
            $table->foreignId('brand_id')
                ->constrained('brands')
                ->onDelete('restrict'); // Mencegah brand dihapus jika masih punya sparepart
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('restrict'); // Mencegah kategori dihapus jika masih punya sparepart
            $table->string('lokasi_penyimpanan')->nullable();
            $table->integer('jumlah_stok')->nullable();
            $table->string('jumlah_satuan')->nullable();
            $table->string('gambar_utama')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
