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
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\authentications\RegisterCover;

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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        //Register View
        Fortify::registerView(function () {
            return view('content.authentications.auth-register-cover'); 
        });
        //Login View
        Fortify::loginView(function () {
            return view('content.authentications.auth-login-cover'); 
        });
        //Forgot Password View
        Fortify::requestPasswordResetLinkView(function () {
            return view('content.authentications.auth-forgot-password-cover');
        });
        //Reset Password View
        Fortify::resetPasswordView(function (Request $request) {
            return view('content.authentications.auth-reset-password-cover', ['request'=>$request]);
        });
        //Two factor Chellenge View
        Fortify::twoFactorChallengeView(function () {
            return view('content.authentications.auth-two-factor-challenge');
        });
        //Two Factor Password Confirm View
        Fortify::confirmPasswordView(function () {
            return view('content.authentications.auth-confirm-password');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
