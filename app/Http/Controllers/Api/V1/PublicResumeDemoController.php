<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\JobSeeker\ResumeHtmlPreviewController;
use App\Support\ResumeHtmlDemoData;
use App\Support\ResumeHtmlViewComposer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

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

    /**
     * All template HTML previews for one demo profile — gallery thumbnails (one request).
     */
    public function demoPreviewHtmlBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'demo_variant' => ['nullable', 'integer', Rule::in([0, 1, 2, 3])],
        ]);
        $variant = (int) ($validated['demo_variant'] ?? 0);
        $resume = ResumeHtmlDemoData::viewProfile($variant);
        $viewData = ResumeHtmlViewComposer::data($resume);
        $previews = [];
        foreach (ResumeHtmlPreviewController::TEMPLATE_KEYS as $key) {
            $viewName = 'resume.html.'.$key;
            if (View::exists($viewName)) {
                $previews[$key] = view($viewName, $viewData)->render();
            }
        }

        return $this->ok([
            'demo_variant' => $variant,
            'previews' => $previews,
        ]);
    }
}
