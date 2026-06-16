<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract
            {
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user && $user->role === 'student') {
                        return redirect()->intended(route('home'));
                    }

                    return redirect()->intended(route('dashboard'));
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        // Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
        //     'email' => $request->email,
        //     'token' => $request->route('token'),
        //     'passwordRules' => Password::defaults()->toPasswordRulesString(),
        // ]));

        // Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
        //     'status' => $request->session()->get('status'),
        // ]));

    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }

    /**
     * Configure custom Fortify authentication logic.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $loginInput = $request->input('email');
            $password = $request->input('password');

            if (empty($loginInput) || empty($password)) {
                return null;
            }

            $user = null;

            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $loginInput)->first();
            } else {
                $user = User::where('nim', $loginInput)
                    ->where('role', 'student')
                    ->first();
            }

            if ($user && Hash::check($password, $user->password)) {
                return $user;
            }

            return null;
        });
    }
}
