<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Custom Login Redirection based on User Role
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request): Response
                {
                    $user = $request->user();

                    if (! $user) {
                        return redirect()->route('login');
                    }

                    $role = strtolower(trim($user->role ?? ''));

                    // 1. Administrator -> Admin Dashboard
                    if (in_array($role, ['administrator', 'admin'])) {
                        return redirect()->to('/admin');
                    }

                    // 2. Staff/Employee -> Staff Dashboard
                    if (in_array($role, ['employee', 'staff'])) {
                        return redirect()->to('/dashboard/staff');
                    }

                    // 3. Student / Default -> Student Dashboard
                    return redirect()->to('/dashboard');
                }
            };
        });
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

        // Custom Authentication Handler (Email or ID Number)
        Fortify::authenticateUsing(function (Request $request) {
            $loginInput = $request->input(Fortify::username()) ?? $request->input('email');

            if (! $loginInput || ! $request->password) {
                return null;
            }

            // Safely query email or id_number if the column exists
            $user = User::where('email', $loginInput)
                ->when(Schema::hasColumn('users', 'id_number'), function ($query) use ($loginInput) {
                    $query->orWhere('id_number', $loginInput);
                })
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // Rate Limiters
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}