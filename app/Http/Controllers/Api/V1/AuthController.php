<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Services\PlatformSettingService;
use App\Support\Identifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly OtpService $otpService,
        private readonly PlatformSettingService $settings
    ) {}

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'intent' => ['required', Rule::in(['register', 'login'])],
            'role' => ['required', Rule::in([UserRole::JobSeeker->value, UserRole::Company->value])],
        ]);

        $parts = Identifier::parse($validated['identifier']);

        if ($parts['email'] === null && $parts['phone'] === null) {
            return $this->fail('Enter a valid email or phone number.', [
                'identifier' => ['Invalid identifier.'],
            ]);
        }

        if ($validated['intent'] === 'login') {
            $existing = $this->findUserByIdentifier($parts);
            if (! $existing) {
                return $this->fail('No account found for this email or phone. Please create an account first.', null, 404);
            }
            if ($existing->role !== $validated['role']) {
                return $this->fail('This account uses a different login type. Try Job Seeker or Employer login accordingly.', null, 403);
            }
            if (! $existing->is_active) {
                return $this->fail('Account is disabled.', null, 403);
            }
        }

        if ($validated['intent'] === 'register') {
            if ($this->findUserByIdentifier($parts)) {
                return $this->fail('An account already exists for this email or phone. Please log in instead.', null, 409);
            }
        }

        $code = $this->otpService->send($validated['identifier']);

        $payload = [
            'expires_in_seconds' => (int) config('otp.ttl_seconds', 600),
        ];

        if (config('otp.expose_code_in_response') || config('app.debug')) {
            $payload['mock_otp'] = $code;
        }

        return $this->ok($payload, 'OTP generated. Replace with SMS/email in production.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
            'intent' => ['required', Rule::in(['register', 'login'])],
            'role' => ['required', Rule::in([UserRole::JobSeeker->value, UserRole::Company->value])],
            'name' => [Rule::requiredIf(fn () => $request->input('intent') === 'register'), 'nullable', 'string', 'max:120'],
            'company_name' => [Rule::requiredIf(fn () => $request->input('intent') === 'register' && $request->input('role') === UserRole::Company->value), 'nullable', 'string', 'max:160'],
            'gst_number' => ['nullable', 'string', 'max:32'],
            // Full India location fields (required on register; city is writeable/optional).
            'state' => [Rule::requiredIf(fn () => $request->input('intent') === 'register'), 'nullable', 'string', 'max:120'],
            'district' => [Rule::requiredIf(fn () => $request->input('intent') === 'register'), 'nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validated['role'] === UserRole::SuperAdmin->value) {
            return $this->fail('Super admin accounts cannot be created via public OTP.', null, 403);
        }

        $parts = Identifier::parse($validated['identifier']);

        if ($parts['email'] === null && $parts['phone'] === null) {
            return $this->fail('Enter a valid email or phone number.', [
                'identifier' => ['Invalid identifier.'],
            ]);
        }

        if (! $this->otpService->verify($validated['identifier'], $validated['code'])) {
            return $this->fail('Invalid or expired OTP.', [
                'code' => ['The code does not match.'],
            ]);
        }

        $user = $this->findUserByIdentifier($parts);

        if ($validated['intent'] === 'login') {
            if (! $user) {
                return $this->fail('No account found for this identifier.', null, 404);
            }
            if ($user->role !== $validated['role']) {
                return $this->fail('Role does not match this account.', null, 403);
            }
            if (! $user->is_active) {
                return $this->fail('Account is disabled.', null, 403);
            }

            return $this->issueTokenResponse($user, 'Login successful.');
        }

        // register
        if ($user) {
            return $this->fail('An account already exists for this identifier.', null, 409);
        }

        $email = Identifier::resolveLoginEmail($parts);

        if (User::where('email', $email)->exists()) {
            return $this->fail('Email already registered.', null, 409);
        }

        if ($parts['phone'] && User::where('phone', $parts['phone'])->exists()) {
            return $this->fail('Phone already registered.', null, 409);
        }

        $user = User::create([
            'name' => $validated['name'] ?? 'User',
            'email' => $email,
            'phone' => $parts['phone'],
            'password' => Hash::make(Str::random(64)),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        if ($validated['role'] === UserRole::Company->value) {
            $slug = $this->uniqueCompanySlug(Str::slug($validated['company_name']));
            $verified = $this->settings->autoVerifyCompanies()
                ? CompanyVerificationStatus::Verified
                : CompanyVerificationStatus::Unverified;

            $locParts = array_values(array_filter([
                $validated['city'] ?? null,
                $validated['district'] ?? null,
                $validated['state'] ?? null,
            ]));
            $location = implode(', ', $locParts);

            Company::create([
                'user_id' => $user->id,
                'name' => $validated['company_name'],
                'slug' => $slug,
                'gst_number' => $validated['gst_number'] ?? null,
                'verification_status' => $verified,
                'state' => $validated['state'] ?? null,
                'district' => $validated['district'] ?? null,
                'city' => $validated['city'] ?? null,
                // Keep legacy `location` string so existing UI doesn't break.
                'location' => $location !== '' ? $location : null,
            ]);
        }

        if ($validated['role'] === UserRole::JobSeeker->value) {
            JobSeekerProfile::create([
                'user_id' => $user->id,
                'state' => $validated['state'] ?? null,
                'district' => $validated['district'] ?? null,
                'city' => $validated['city'] ?? null,
            ]);
        }

        return $this->issueTokenResponse($user->fresh(), 'Registration successful.');
    }

    public function adminLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string', 'max:190'],
        ]);

        $username = trim($validated['username']);
        $user = User::query()
            ->where(function ($q) use ($username): void {
                $q->where('email', $username)
                    ->orWhere('name', $username);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->fail('Invalid username or password.', null, 401);
        }

        if ($user->role !== UserRole::SuperAdmin->value) {
            return $this->fail('Only super admin can login here.', null, 403);
        }

        if (! $user->is_active) {
            return $this->fail('Account is disabled.', null, 403);
        }

        return $this->issueTokenResponse($user, 'Admin login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->ok(null, 'Logged out.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->fail('Unauthorized.', null, 401);
        }

        if ($user->role !== UserRole::SuperAdmin->value) {
            return $this->fail('Only super admin can change password here.', null, 403);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:190'],
            'new_password' => ['required', 'string', 'min:8', 'max:190', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->fail('Current password is incorrect.', [
                'current_password' => ['Current password is incorrect.'],
            ], 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->ok(null, 'Password changed successfully.');
    }

    private function findUserByIdentifier(array $parts): ?User
    {
        if ($parts['email'] !== null) {
            return User::where('email', $parts['email'])->first();
        }

        if ($parts['phone'] !== null) {
            return User::where('phone', $parts['phone'])->first();
        }

        return null;
    }

    private function issueTokenResponse(User $user, string $message): JsonResponse
    {
        $token = $user->createToken('api')->plainTextToken;

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
        ], $message);
    }

    private function uniqueCompanySlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'company';
        $candidate = $slug;
        $i = 0;

        while (Company::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.(++$i);
        }

        return $candidate;
    }
}
