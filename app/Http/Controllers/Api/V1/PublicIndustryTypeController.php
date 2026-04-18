<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\IndustryType;
use Illuminate\Http\JsonResponse;

class PublicIndustryTypeController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $rows = IndustryType::query()
            ->activeOrdered()
            ->get(['key', 'label', 'sort_order']);

        $data = $rows->map(fn (IndustryType $r) => [
            'key' => $r->key,
            'label' => $r->label,
            'sort_order' => (int) $r->sort_order,
        ])->values()->all();

        return $this->ok($data);
    }
}
