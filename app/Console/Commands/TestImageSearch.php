<?php

namespace App\Console\Commands;

use App\Models\Sparepart;
use App\Services\ResnetServiceClient;
use Illuminate\Console\Command;

class TestImageSearch extends Command
{
    protected $signature = 'debug:test-image-search';

    protected $description = 'Uji end-to-end jalur Laravel -> Python service -> MySQL (untuk verifikasi).';

    public function handle(ResnetServiceClient $client): int
    {
        $this->info('1) Health check Python service...');
        $healthy = $client->isHealthy();
        $this->line('   Healthy: ' . ($healthy ? 'YES' : 'NO'));

        if (!$healthy) {
            $this->error('Python service tidak hidup. Jalankan scripts\\start_resnet_service.bat');
            return self::FAILURE;
        }

        // Ambil gambar pertama dari sparepart #1 sebagai query.
        $sparepart = Sparepart::with('images')->find(1);
        if (!$sparepart || !$sparepart->images->first()) {
            $this->error('Tidak ada gambar untuk dijadikan query.');
            return self::FAILURE;
        }

        $imagePath = storage_path('app/public/' . $sparepart->images->first()->path);
        $this->info('2) Query image: ' . $imagePath);

        $result = $client->search($imagePath, 5);
        $this->line('   Search success: ' . ($result['success'] ? 'YES' : 'NO'));
        $this->line('   Raw IDs: ' . implode(', ', $result['ids']));

        if (!$result['success'] || empty($result['ids'])) {
            $this->error('Pencarian gagal / kosong: ' . $result['message']);
            return self::FAILURE;
        }

        $ids = array_map('intval', $result['ids']);
        $products = Sparepart::with(['brand', 'category', 'images'])
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();

        $this->info('3) Sparepart ditemukan dari MySQL: ' . $products->count());
        foreach ($products as $index => $p) {
            $this->line('   ' . ($index + 1) . '. #' . $p->id . ' — ' . $p->nama_barang);
        }

        $this->newLine();
        $this->info('END-TO-END BERHASIL: pipeline pencarian gambar bekerja.');

        return self::SUCCESS;
    }
}
