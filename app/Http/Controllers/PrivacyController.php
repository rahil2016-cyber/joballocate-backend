<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivacyController extends Controller
{
    public function show(): View
    {
        return view('privacy');
    }

    public function submitDeletionRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{10,15}$/'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Enter a valid mobile number with 10 to 15 digits.',
        ]);

        $email = strtolower(trim($validated['email']));
        $phone = trim($validated['phone']);

        $user = User::query()
            ->where('email', $email)
            ->where('phone', $phone)
            ->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'No account was found with this registered email and mobile number.',
                ]);
        }

        $alreadyPending = AccountDeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()->with('status', 'A deletion request is already pending for this account. We will process it within 24 hours.');
        }

        AccountDeletionRequest::create([
            'user_id' => $user->id,
            'name' => $validated['name'] ?? $user->name,
            'email' => $email,
            'phone' => $phone,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return back()->with('status', 'Your account deletion request has been submitted. We will process it within 24 hours.');
    }
}
