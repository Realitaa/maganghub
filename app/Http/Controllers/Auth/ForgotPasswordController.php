<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $loginInput = $request->input('email');

        // Find user by email or NIM
        $user = null;
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)->first();
        } else {
            $user = User::where('nim', $loginInput)
                ->where('role', 'student')
                ->first();
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.user_or_nim')],
            ]);
        }

        // If user doesn't have an email
        if (empty($user->email)) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.email_empty')],
            ]);
        }

        // Send reset link using the user's email
        try {
            $status = Password::broker(config('fortify.passwords'))->sendResetLink(
                ['email' => $user->email]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send reset link email: ' . $e->getMessage(), ['email' => $user->email]);
            throw ValidationException::withMessages([
                'email' => [__('passwords.smtp_error')],
            ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            $message = __('passwords.sent');
            if ($loginInput !== $user->email) {
                // If they input NIM, tell them which email it was sent to (masked for privacy)
                $maskedEmail = $this->maskEmail($user->email);
                $message = __('passwords.masked_sent', ['email' => $maskedEmail]);
            }
            
            return back()->with('status', $message);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Mask email for privacy (e.g., john.doe@example.com -> j***e@example.com)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);
        
        if ($len <= 2) {
            return $name . '@' . $domain;
        }
        
        return $name[0] . str_repeat('*', $len - 2) . $name[$len - 1] . '@' . $domain;
    }
}
