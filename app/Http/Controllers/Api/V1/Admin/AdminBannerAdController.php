<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminBannerAdController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'active', 'paused'])],
            'audience' => ['nullable', Rule::in(['all', 'job_seeker', 'employer'])],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = BannerAd::query()
            ->with('creator:id,name,email')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if (! empty($validated['status'] ?? null)) {
            $q->where('status', $validated['status']);
        }
        if (! empty($validated['audience'] ?? null)) {
            $q->where('audience', $validated['audience']);
        }
        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('title', 'like', $term)
                    ->orWhere('content', 'like', $term)
                    ->orWhere('target_url', 'like', $term);
            });
        }

        $rows = $q->paginate((int) ($validated['per_page'] ?? 20));
        $data = collect($rows->items())->map(fn (BannerAd $b) => $this->toPayload($b))->values()->all();

        return $this->ok(
            $data,
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeOptimizedImage($request->file('image'));
        }

        $startsAt = $validated['starts_at'] ?? null;
        $status = ($validated['publish_now'] ?? false) ? 'active' : 'draft';
        if (($validated['publish_now'] ?? false) && ! $startsAt) {
            $startsAt = now()->toDateTimeString();
        }

        $row = BannerAd::query()->create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'below_line' => $validated['below_line'] ?? null,
            'target_url' => $validated['target_url'] ?? null,
            'background_color' => $validated['background_color'] ?? null,
            'image_path' => $imagePath,
            'status' => $status,
            'audience' => $validated['audience'] ?? 'all',
            'starts_at' => $startsAt,
            'expires_at' => $validated['expires_at'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'created_by' => $request->user()?->id,
        ]);

        return $this->ok($this->toPayload($row->fresh()), 'Banner created.', null, 201);
    }

    public function update(Request $request, int $bannerId): JsonResponse
    {
        $row = BannerAd::query()->find($bannerId);
        if (! $row) {
            return $this->fail('Banner not found.', null, 404);
        }

        $validated = $this->validatePayload($request, true);

        if ($request->hasFile('image')) {
            if ($row->image_path) {
                Storage::disk('public')->delete($row->image_path);
            }
            $row->image_path = $this->storeOptimizedImage($request->file('image'));
        }

        foreach (['title', 'content', 'below_line', 'target_url', 'background_color', 'audience', 'starts_at', 'expires_at', 'sort_order'] as $f) {
            if (array_key_exists($f, $validated)) {
                $row->{$f} = $validated[$f];
            }
        }

        if (array_key_exists('publish_now', $validated)) {
            $row->status = $validated['publish_now'] ? 'active' : $row->status;
            if ($validated['publish_now'] && ! $row->starts_at) {
                $row->starts_at = now();
            }
        }

        $row->save();

        return $this->ok($this->toPayload($row->fresh()), 'Banner updated.');
    }

    public function start(int $bannerId): JsonResponse
    {
        $row = BannerAd::query()->find($bannerId);
        if (! $row) {
            return $this->fail('Banner not found.', null, 404);
        }

        if (! $row->starts_at) {
            $row->starts_at = now();
        }
        $row->status = 'active';
        $row->save();

        return $this->ok($this->toPayload($row->fresh()), 'Banner started.');
    }

    public function stop(int $bannerId): JsonResponse
    {
        $row = BannerAd::query()->find($bannerId);
        if (! $row) {
            return $this->fail('Banner not found.', null, 404);
        }
        $row->status = 'paused';
        $row->save();

        return $this->ok($this->toPayload($row->fresh()), 'Banner stopped.');
    }

    public function destroy(int $bannerId): JsonResponse
    {
        $row = BannerAd::query()->find($bannerId);
        if (! $row) {
            return $this->fail('Banner not found.', null, 404);
        }
        if ($row->image_path) {
            Storage::disk('public')->delete($row->image_path);
        }
        $row->delete();

        return $this->ok(null, 'Banner deleted.');
    }

    private function validatePayload(Request $request, bool $partial = false): array
    {
        $titleRule = $partial ? ['sometimes', 'string', 'max:160'] : ['required', 'string', 'max:160'];
        return $request->validate([
            'title' => $titleRule,
            'content' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'below_line' => ['sometimes', 'nullable', 'string', 'max:500'],
            'target_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'background_color' => ['sometimes', 'nullable', 'regex:/^#?[0-9a-fA-F]{3,8}$/'],
            'audience' => ['sometimes', 'nullable', Rule::in(['all', 'job_seeker', 'employer'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'publish_now' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'nullable', 'image', 'max:5120'],
        ]);
    }

    private function storeOptimizedImage(UploadedFile $file): string
    {
        $raw = file_get_contents($file->getRealPath());
        if ($raw === false) {
            throw new \RuntimeException('Could not read image file.');
        }

        $src = @imagecreatefromstring($raw);
        if (! $src) {
            return $file->store('banner-ads', 'public');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $maxW = 1600;
        $nw = $w;
        $nh = $h;
        if ($w > $maxW) {
            $ratio = $maxW / $w;
            $nw = (int) round($w * $ratio);
            $nh = (int) round($h * $ratio);
        }

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ext = strtolower((string) $file->getClientOriginalExtension());
        $name = 'banner-ads/'.Str::uuid()->toString();

        ob_start();
        if (in_array($ext, ['png', 'gif'], true)) {
            imagepng($dst, null, 7);
            $bytes = (string) ob_get_clean();
            $path = $name.'.png';
        } else {
            imagejpeg($dst, null, 82);
            $bytes = (string) ob_get_clean();
            $path = $name.'.jpg';
        }

        imagedestroy($src);
        imagedestroy($dst);
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    private function toPayload(BannerAd $b): array
    {
        return [
            'id' => $b->id,
            'title' => $b->title,
            'content' => $b->content,
            'below_line' => $b->below_line,
            'target_url' => $b->target_url,
            'background_color' => $b->background_color,
            'image_path' => $b->image_path,
            'image_url' => $b->publicImageUrl(),
            'status' => $b->status,
            'audience' => $b->audience ?? 'all',
            'starts_at' => $b->starts_at?->toIso8601String(),
            'expires_at' => $b->expires_at?->toIso8601String(),
            'sort_order' => $b->sort_order,
            'created_by' => $b->created_by,
            'created_at' => $b->created_at?->toIso8601String(),
            'updated_at' => $b->updated_at?->toIso8601String(),
        ];
    }
}

