<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ];

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        if (empty($request->user()->email)) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.email_empty_for_password_change')],
            ]);
        }

        $request->user()->update([
            'password' => $request->password,
            'password_changed_at' => $request->password === $request->user()->nim ? null : now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('passwords.updated')]);

        return back();
    }
}
