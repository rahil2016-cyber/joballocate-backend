<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\ResumeHtmlDemoData;
use Illuminate\Http\JsonResponse;

class PublicResumeDemoController extends Controller
{
    use ApiResponses;

    /**
     * Rich demo résumé payloads for app template gallery (same data as preview-html demo_variant).
     */
    public function demoProfiles(): JsonResponse
    {
        return $this->ok([
            'variant_count' => ResumeHtmlDemoData::VARIANT_COUNT,
            'profiles' => ResumeHtmlDemoData::allProfiles(),
        ]);
    }
}
