<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load(['company', 'jobSeekerProfile']);

        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'slug' => $user->company->slug,
                'verification_status' => $user->company->verification_status instanceof \BackedEnum
                    ? $user->company->verification_status->value
                    : $user->company->verification_status,
            ] : null,
            'job_seeker_profile' => $user->jobSeekerProfile,
        ]);
    }
}
