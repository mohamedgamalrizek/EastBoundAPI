<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // This app ships its own auth flow (AuthController / RegisterController /
        // PasswordController with token verification), so Fortify's routes are
        // unused — and they registered duplicate names (`login`, `register`,
        // `password.reset`, `password.update`), which made `route:cache` throw
        // "Another route has already been assigned name". Its actions, views
        // and rate limiters below stay in place.
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify actions
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        /*
         * Fortify views.
         *
         * This application ships its own registration and password-reset flow
         * (Auth\RegisterController and Auth\PasswordController, views under
         * resources/views/auth/register and .../password). Fortify still
         * registers its own routes — /forgot-password, /reset-password,
         * /two-factor-challenge — and these closures used to render blade
         * files that do not exist, so hitting them returned a 500.
         *
         * Each one now hands off to the flow the app actually uses; only
         * auth.login exists as a real Fortify view.
         */
        Fortify::loginView(fn () => view('auth.login'));

        Fortify::registerView(fn () => redirect()->route('registerForm'));

        Fortify::requestPasswordResetLinkView(fn () => redirect()->route('password.forgotForm'));

        Fortify::resetPasswordView(fn ($request) => redirect()->route('password.forgotForm'));

        Fortify::verifyEmailView(fn () => redirect()->route('loginForm'));

        Fortify::twoFactorChallengeView(fn () => redirect()->route('loginForm'));

        // Rate limiters
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
