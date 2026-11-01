<?php

namespace App\Jobs;

use App\Models\Sparepart;
use App\Services\ResnetServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Mengekstrak vektor fitur ResNet50 dari gambar utama sebuah sparepart dan
 * menyimpannya ke ChromaDB — DIJALANKAN DI BACKGROUND (queue), sehingga
 * request HTTP `store()`/`update()` tidak lagi menunggu proses berat ini.
 *
 * Alur:
 *  1. Tandai `is_indexed = false` saat mulai.
 *  2. Panggil Python service (ResNetServiceClient) untuk index gambar.
 *  3. Set `is_indexed = true` bila berhasil; log error bila gagal.
 */
class IndexSparepartVector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** Jeda antar percobaan (detik) bila service Python sedang belum siap. */
    public int $backoff = 15;

    public function __construct(
        public int $sparepartId,
        public string $imagePath
    ) {
    }

    public function handle(ResnetServiceClient $client): void
    {
        $sparepart = Sparepart::find($this->sparepartId);

        if (!$sparepart) {
            Log::warning("IndexSparepartVector: sparepart #{$this->sparepartId} tidak ditemukan, indexing dilewati.");
            return;
        }

        $absoluteImagePath = storage_path('app/public/' . $this->imagePath);

        if (!file_exists($absoluteImagePath)) {
            Log::error("IndexSparepartVector: file gambar tidak ditemukan untuk sparepart #{$this->sparepartId}: {$absoluteImagePath}");
            $sparepart->update(['is_indexed' => false]);
            return;
        }

        $result = $client->indexImage($absoluteImagePath, $this->sparepartId);

        if ($result['success']) {
            $sparepart->update([
                'is_indexed' => true,
                'vector_path' => $this->imagePath,
            ]);

            Log::info("IndexSparepartVector: SUCCESS untuk sparepart #{$this->sparepartId}.");
            return;
        }

        // Gagal: pastikan flag false dan lempar exception agar queue retry.
        $sparepart->update(['is_indexed' => false]);

        throw new \RuntimeException(
            "IndexSparepartVector gagal untuk sparepart #{$this->sparepartId}: {$result['message']}"
        );
    }

    /**
     * Dipanggil saat semua retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("IndexSparepartVector PERMANENT FAILURE untuk sparepart #{$this->sparepartId}: " . $exception->getMessage());

        Sparepart::whereKey($this->sparepartId)->update(['is_indexed' => false]);
    }
}
