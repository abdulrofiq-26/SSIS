<?php

namespace App\Console\Commands;

use App\Jobs\IndexSparepartVector;
use App\Models\Sparepart;
use App\Services\ResnetServiceClient;
use Illuminate\Console\Command;

/**
 * REK 4: Re-index SELURUH sparepart yang punya gambar ke ChromaDB.
 *
 * Mengatasi kondisi saat ini di mana ChromaDB hanya berisi sebagian vektor
 * (mis. 1 dari 2 sparepart) karena indexing sebelumnya sering gagal.
 *
 * Pemakaian:
 *   php artisan spareparts:reindex            # dispatch ke queue (background)
 *   php artisan spareparts:reindex --sync     # jalankan langsung (butuh service hidup)
 */
class ReindexSpareparts extends Command
{
    protected $signature = 'spareparts:reindex {--sync : Jalankan langsung tanpa queue}';

    protected $description = 'Re-index seluruh gambar sparepart ke ChromaDB (ResNet50).';

    public function handle(ResnetServiceClient $client): int
    {
        $spareparts = Sparepart::with('images')->get();

        if ($spareparts->isEmpty()) {
            $this->warn('Tidak ada sparepart untuk di-index.');
            return self::SUCCESS;
        }

        // Peringatan bila service Python tidak hidup.
        if (!$client->isHealthy()) {
            $this->error('Python service (ResNet50) tidak terdeteksi di ' . config('services.resnet.url'));
            $this->line('Jalankan dulu: scripts\\start_resnet_service.bat');
            return self::FAILURE;
        }

        $dispatched = 0;
        $skipped = 0;

        foreach ($spareparts as $sparepart) {
            $mainImage = $sparepart->images->sortBy('order')->first();

            if (!$mainImage) {
                $sparepart->update(['is_indexed' => false]);
                $this->line("  - #{$sparepart->id} {$sparepart->nama_barang}: tidak ada gambar, dilewati.");
                $skipped++;
                continue;
            }

            if ($this->option('sync')) {
                // Jalankan sinkron (langsung) memakai job-nya.
                (new IndexSparepartVector($sparepart->id, $mainImage->path))->handle($client);
                $this->info("  - #{$sparepart->id} {$sparepart->nama_barang}: di-index (sync).");
            } else {
                $sparepart->update(['is_indexed' => false]);
                IndexSparepartVector::dispatch($sparepart->id, $mainImage->path);
                $this->info("  - #{$sparepart->id} {$sparepart->nama_barang}: masuk queue.");
            }

            $dispatched++;
        }

        $this->newLine();
        $this->info("Selesai. Diproses: {$dispatched}, dilewati: {$skipped}.");

        if (!$this->option('sync')) {
            $this->comment('Jalankan `php artisan queue:work` agar job di atas dieksekusi.');
        }

        return self::SUCCESS;
    }
}