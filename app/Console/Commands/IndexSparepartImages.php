<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sparepart;
use App\Services\ResnetServiceClient;
use Illuminate\Support\Facades\Log;

class IndexSparepartImages extends Command
{
    protected $signature = 'image:index-parts';
    protected $description = 'Extracts image features for spareparts and saves them for image search.';

    public function handle(ResnetServiceClient $client): int
    {
        $this->info("Starting image feature indexing...");

        // REK 2 & 3: Pakai Python service persisten (bukan `python script.py` sinkron).
        if (!$client->isHealthy()) {
            $this->error('Python service (ResNet50) tidak terdeteksi di ' . config('services.resnet.url'));
            $this->line('Jalankan dulu: scripts\\start_resnet_service.bat');
            return Command::FAILURE;
        }

        // Asumsi model Sparepart memiliki relasi 'images' yang mengembalikan path gambar
        $spareparts = Sparepart::with('images')
                               ->where('is_indexed', false)
                               ->get();

        $total = $spareparts->count();
        if ($total === 0) {
            $this->info("All spare parts are already indexed.");
            return Command::SUCCESS;
        }

        $indexed = 0;

        $this->withProgressBar($spareparts, function ($sparepart) use ($client, &$indexed) {
            // Cek apakah ada gambar yang terkait
            $mainImage = $sparepart->images->sortBy('order')->first();
            if (!$mainImage) {
                $this->warn("Sparepart ID {$sparepart->id} skipped (No image found).");
                return;
            }

            // Path gambar di sistem file (storage/app/public/...)
            $imagePath = storage_path('app/public/' . $mainImage->path);

            $result = $client->indexImage($imagePath, $sparepart->id);

            if ($result['success']) {
                $sparepart->update(['is_indexed' => true]);
                $indexed++;
            } else {
                Log::error("Indexing failed for ID {$sparepart->id}. Error: " . $result['message']);
                $this->error("Failed to index ID {$sparepart->id}: " . $result['message']);
            }
        });

        $this->newLine();
        $this->info("Indexing finished. Indexed {$indexed} of {$total} parts.");
        
        return Command::SUCCESS;
    }
}