<?php

namespace App\Console\Commands;

use App\Models\Sparepart;
use App\Services\ResnetServiceClient;
use Illuminate\Console\Command;

class DebugRenderedImageSearch extends Command
{
    protected $signature = 'debug:render-image-search';

    protected $description = 'Mensimulasikan hasil pencarian gambar (tanpa HTTP) untuk memeriksa urutan & skor.';

    public function handle(ResnetServiceClient $client): int
    {
        $sp = Sparepart::with('images')->whereHas('images')->first();
        $img = storage_path('app/public/' . $sp->images->first()->path);

        $result = $client->search($img, 8);
        $ids = array_map('intval', $result['ids']);
        $scores = $result['scores'] ?? [];

        $products = Sparepart::with(['brand', 'category', 'images'])
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();

        $scoreById = [];
        foreach ($ids as $i => $id) {
            $scoreById[$id] = $scores[$i] ?? null;
        }

        $this->info('Urutan hasil (paling mirip = paling kiri):');
        foreach ($products as $i => $p) {
            $score = $scoreById[$p->id] ?? null;
            $label = $i === 0 ? ' <== PALING MIRIP' : '';
            $this->line(sprintf(
                '  %d. #%d %-30s  %s%%%s',
                $i + 1,
                $p->id,
                $p->nama_barang,
                $score !== null ? number_format($score, 1) : 'n/a',
                $label
            ));
        }

        return self::SUCCESS;
    }
}
