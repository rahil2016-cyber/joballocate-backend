<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\ResumeDraft;
use App\Support\ResumeHtmlDemoData;
use App\Support\ResumeHtmlViewComposer;
use App\Support\ResumeViewData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class ResumeHtmlPreviewController extends Controller
{
    use ApiResponses;

    public const TEMPLATE_KEYS = [
        't13_academic_clean',
        't1_teal_sidebar',
        't2_minimal',
        't3_bold_navy',
        't4_classic_serif',
        't5_modern_split',
        't6_navy_two_column',
        't7_geometric_modern',
        't8_typewriter_retro',
        't9_vintage_folio',
        't10_creative_sunset',
        't11_mono_swiss',
        't12_royal_gold',
    ];

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_key' => ['required', 'string', 'max:64', Rule::in(self::TEMPLATE_KEYS)],
            'content' => ['nullable', 'array'],
            'resume_draft_id' => ['nullable', 'integer', 'exists:resume_drafts,id'],
            'demo_variant' => ['nullable', 'integer', Rule::in([0, 1, 2, 3])],
        ]);

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        $demoVariant = $validated['demo_variant'] ?? null;
        if ($demoVariant !== null) {
            $resume = ResumeHtmlDemoData::viewProfile((int) $demoVariant);
        } else {
            $envelope = $validated['content'] ?? null;
            if ($envelope === null && ! empty($validated['resume_draft_id'])) {
                $draft = ResumeDraft::query()
                    ->where('id', $validated['resume_draft_id'])
                    ->where('user_id', $user->id)
                    ->first();
                if (! $draft) {
                    return $this->fail('Resume draft not found.', null, 404);
                }
                $envelope = is_array($draft->content) ? $draft->content : null;
            }

            if ($envelope === null && $profile?->resume_document !== null) {
                $envelope = is_array($profile->resume_document) ? $profile->resume_document : null;
            }

            if ($envelope === null) {
                $envelope = ['schema' => 'resume_model_v1', 'version' => 1, 'data' => []];
            }

            $resume = ResumeViewData::fromEnvelope($envelope, $user, $profile);
        }
        $key = $validated['template_key'];
        $viewName = 'resume.html.'.$key;

        if (! View::exists($viewName)) {
            return $this->fail('Template not found.', null, 404);
        }

        $html = view($viewName, ResumeHtmlViewComposer::data($resume))->render();

        return $this->ok([
            'html' => $html,
            'template_key' => $key,
        ]);
    }
}
