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
                    ->with('success', 'Welcome! Your account is ready.');
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

        // Customers sign in with their mobile number; admin / staff / professionals with their email.
        Fortify::authenticateUsing(function (Request $request) {
            $login = trim((string) $request->input('login'));
            $byEmail = str_contains($login, '@');

            if ($byEmail) {
                $user = User::where('email', mb_strtolower($login))->first();
            } else {
                $mobile = preg_replace('/\D/', '', $login);
                $mobile = strlen($mobile) > 10 ? substr($mobile, -10) : $mobile;
                $user = strlen($mobile) === 10 ? User::where('mobile', $mobile)->first() : null;
            }

            if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            // Checked only after the password matches, so this never reveals which accounts exist.
            if ($byEmail && $user->isCustomer()) {
                throw ValidationException::withMessages(['login' => 'Customers sign in with their mobile number.']);
            }
            if (! $byEmail && ! $user->isCustomer()) {
                throw ValidationException::withMessages(['login' => 'Staff and admin sign in with their email address.']);
            }

            if (! $user->isActive()) {
                throw ValidationException::withMessages(['login' => 'Your account is inactive. Please contact support.']);
            }

            $user->forceFill(['last_login_at' => now()])->saveQuietly();

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            // Same key however the email / number is typed (case, spaces, +91, leading 0)
            $login = trim((string) $request->input('login'));
            $key = str_contains($login, '@') ? mb_strtolower($login) : substr(preg_replace('/\D/', '', $login), -10);
            $throttleKey = $key.'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
