<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PublicLocationController extends Controller
{
    use ApiResponses;

    private const REMOTE_JSON_URL = 'https://raw.githubusercontent.com/sab99r/Indian-States-And-Districts/master/states-and-districts.json';
    private const CACHE_PATH = 'location/states-and-districts.json';
    private const CACHE_TTL_SECONDS = 60 * 60 * 24; // 24h

    /**
     * Cached dataset loader.
     * We avoid shipping huge district lists inside the repo; the first request downloads it.
     */
    private function loadDataset(): array
    {
        $disk = Storage::disk('local');
        $path = self::CACHE_PATH;

        if ($disk->exists($path)) {
            $fullPath = storage_path('app/'.$path);
            $mtime = is_file($fullPath) ? filemtime($fullPath) : null;
            if ($mtime !== null && (time() - $mtime) <= self::CACHE_TTL_SECONDS) {
                $raw = $disk->get($path);
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        $resp = Http::timeout(15)->get(self::REMOTE_JSON_URL);
        if (! $resp->successful()) {
            throw new \RuntimeException('Failed to download location dataset.');
        }

        $raw = $resp->body();
        $disk->put($path, $raw);

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid location dataset JSON.');
        }

        return $decoded;
    }

    public function states(Request $request): JsonResponse
    {
        try {
            $data = $this->loadDataset();
            $states = collect($data['states'] ?? [])
                ->map(fn ($s) => (string) ($s['state'] ?? ''))
                ->filter(fn ($v) => trim($v) !== '')
                ->values()
                ->all();

            return $this->ok(['states' => $states]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to load states.', null, 500);
        }
    }

    public function districts(Request $request): JsonResponse
    {
        $state = trim((string) $request->query('state'));
        if ($state === '') {
            return $this->ok(['districts' => []]);
        }

        try {
            $data = $this->loadDataset();
            $needle = mb_strtolower($state);

            $districts = [];
            foreach (($data['states'] ?? []) as $s) {
                $sName = trim((string) ($s['state'] ?? ''));
                if ($sName === '') continue;
                if (mb_strtolower($sName) === $needle) {
                    $districts = array_values(array_filter(array_map(
                        fn ($d) => is_string($d) ? trim($d) : '',
                        $s['districts'] ?? []
                    ), fn ($d) => $d !== ''));
                    break;
                }
            }

            return $this->ok(['districts' => $districts]);
        } catch (\Throwable $e) {
            return $this->fail('Failed to load districts.', null, 500);
        }
    }
}

