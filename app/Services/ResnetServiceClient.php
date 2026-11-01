<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien untuk menghubungi Python upstream service (Flask/FastAPI) yang
 * memuat model ResNet50 SEKALI di memori.
 *
 * Keuntungan dibanding memanggil `python script.py` per request:
 *  - Tidak ada cold-start TensorFlow + ResNet50 (~1-2 menit) di setiap request.
 *  - Setiap call hanya mengekstrak fitur 1 gambar (<1 detik).
 *
 * Service dijalankan terpisah lewat: scripts/resnet_service.py (lihat README).
 */
class ResnetServiceClient
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.resnet.url', 'http://127.0.0.1:5005'), '/');
    }

    /**
     * Cek apakah service hidup dan model sudah siap.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(3)->get($this->baseUrl . '/health');
            return $response->successful() && ($response->json('status') === 'ok');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Index satu gambar: ekstrak fitur ResNet50 lalu upsert ke ChromaDB.
     *
     * @return array{success: bool, message: string}
     */
    public function indexImage(string $absoluteImagePath, int $sparepartId): array
    {
        return $this->post('/index', [
            'image_path' => $absoluteImagePath,
            'sparepart_id' => (string) $sparepartId,
        ]);
    }

    /**
     * Cari sparepart paling mirip dengan gambar query.
     *
     * @return array{success: bool, ids: array<int,string>, scores: array<int,float>, message: string}
     */
    public function search(string $absoluteImagePath, int $nResults = 5): array
    {
        $result = $this->post('/search', [
            'image_path' => $absoluteImagePath,
            'n_results' => $nResults,
        ]);

        if ($result['success']) {
            $result['ids'] = is_array($result['ids'] ?? null) ? $result['ids'] : [];
            $result['scores'] = is_array($result['scores'] ?? null) ? $result['scores'] : [];
        } else {
            $result['ids'] = [];
            $result['scores'] = [];
        }

        return $result;
    }

    /**
     * Hapus vektor sebuah sparepart dari ChromaDB.
     */
    public function deleteVector(int $sparepartId): array
    {
        return $this->post('/delete', [
            'sparepart_id' => (string) $sparepartId,
        ]);
    }

    /**
     * Kirim POST JSON ke service. Selalu mengembalikan array dengan key `success`,
     * sehingga pemanggil tidak perlu menangani exception secara manual.
     */
    protected function post(string $path, array $payload): array
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(120)
                ->acceptJson()
                ->post($this->baseUrl . $path, $payload);

            if (!$response->successful()) {
                $message = 'Python service error (HTTP ' . $response->status() . '): ' . $response->body();
                Log::error('ResnetServiceClient ' . $path . ' failed: ' . $message);

                return ['success' => false, 'message' => $message];
            }

            return array_merge(
                ['success' => true, 'message' => 'OK'],
                (array) $response->json()
            );
        } catch (\Throwable $e) {
            $message = 'Tidak dapat menghubungi Python service: ' . $e->getMessage();
            Log::error('ResnetServiceClient ' . $path . ' exception: ' . $message);

            return ['success' => false, 'message' => $message];
        }
    }
}
