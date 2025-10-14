<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sparepart;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class IndexSparepartImages extends Command
{
    protected $signature = 'image:index-parts';
    protected $description = 'Extracts image features for spareparts and saves them for image search.';

    public function handle()
    {
        $this->info("Starting image feature indexing...");

        // Asumsi model Sparepart memiliki relasi 'images' yang mengembalikan path gambar
        $spareparts = Sparepart::with('images')
                               ->where('is_indexed', false)
                               ->get();

        $total = $spareparts->count();
        if ($total === 0) {
            $this->info("All spare parts are already indexed.");
            return Command::SUCCESS;
        }

        $this->withProgressBar($spareparts, function ($sparepart) {
            // Cek apakah ada gambar yang terkait
            $mainImage = $sparepart->images->first(); 
            if (!$mainImage) {
                // Jika tidak ada gambar, skip
                $this->warn("Sparepart ID {$sparepart->id} skipped (No image found).");
                return;
            }

            // Path gambar di sistem file (storage/app/public/...)
            $imagePath = storage_path('app/public/' . $mainImage->path);
            
            // Path Vektor untuk disimpan di storage/app/public/vectors/
            $vectorPathRelative = 'vectors/' . $sparepart->id . '.pkl';
            $vectorPathAbsolute = storage_path('app/public/' . $vectorPathRelative);

            // Perintah Python untuk mengekstrak vektor
            $command = [
                'python3',
                base_path('scripts/extract_vector.py'),
                $imagePath,
                $vectorPathAbsolute
            ];

            $process = new Process($command);
            $process->setTimeout(120); // Beri waktu lebih untuk Python
            $process->run();

            if ($process->isSuccessful()) {
                // Update database
                $sparepart->update([
                    'vector_path' => $vectorPathRelative,
                    'is_indexed' => true
                ]);
            } else {
                Log::error("Indexing failed for ID {$sparepart->id}. Error: " . $process->getErrorOutput());
                $this->error("Failed to index ID {$sparepart->id}");
            }
        });

        $this->newLine();
        $this->info("Indexing finished. Indexed {$total} new parts.");
        
        return Command::SUCCESS;
    }
}