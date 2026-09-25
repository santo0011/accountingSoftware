<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // After login / registration send each user type to its own area.
        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {
                return redirect()->intended(route('dashboard'));
            }
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {
            public function toResponse($request)
            {
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome! Please verify your email address to continue.');
            }
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn (Request $request) => view('auth.register', [
            'businessTypes' => \App\Models\Business::TYPES,
            'service' => $request->query('service'),
        ]));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        // Login with email OR mobile number in one field.
        Fortify::authenticateUsing(function (Request $request) {
            $login = trim((string) $request->input('login'));
            $mobile = preg_replace('/\D/', '', $login);
            $mobile = strlen($mobile) > 10 ? substr($mobile, -10) : $mobile;

            $user = filter_var($login, FILTER_VALIDATE_EMAIL)
                ? User::where('email', Str::lower($login))->first()
                : User::where('mobile', $mobile)->first();

            if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            if (! $user->isActive()) {
                throw ValidationException::withMessages(['login' => 'Your account is inactive. Please contact support.']);
            }

            $user->forceFill(['last_login_at' => now()])->saveQuietly();

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower((string) $request->input('login')).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
