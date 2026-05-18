<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        Fortify::authenticateUsing(function (Request $request) {
            $login = $request->input('username');

            if (empty($login)) {
                return null;
            }

            $user = \App\Models\User::where('username', $login)->first();

            if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'username' => __('auth.failed'),
                ]);
            }

            // Pengecekan is_active hanya berlaku untuk group selain Admin
            if (! $user->hasRole('Admin') && ! $user->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'username' => 'Akun Anda tidak aktif. Silakan hubungi administrator untuk informasi lebih lanjut.',
                ]);
            }

            return $user;
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input('username') ?? '') . '|' . $request->ip());

            return Limit::perMinute(20)->by($throttleKey);
        });
    }
}
