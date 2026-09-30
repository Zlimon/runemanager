<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Models\Account;
use App\Models\User;
use App\Services\Users\CredentialCheck;
use App\Support\Instance;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // The credential check (timed the same for unknown emails, see
        // CredentialCheck), plus a refusal for a suspended user. The suspension
        // is only revealed once the password is right, so it tells nobody else
        // whether an email is registered. Fortify runs this callback for the
        // two-factor redirect as well, so 2FA users are covered too.
        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = app(CredentialCheck::class)->user(
                $request->input(Fortify::username()),
                $request->input('password'),
            );

            if ($user === null) {
                return null;
            }

            if ($user->isSuspended()) {
                throw ValidationException::withMessages([
                    Fortify::username() => [EnsureUserIsNotSuspended::MESSAGE],
                ]);
            }

            return $user;
        });

        // In clan/group mode registration is gated on claiming a
        // pre-created roster account, so surface the unclaimed ones to the form.
        Fortify::registerView(function () {
            $rosterRequired = Instance::requiresRosterClaim();

            return Inertia::render('Auth/Register', [
                'rosterRequired' => $rosterRequired,
                'rosterAccounts' => $rosterRequired
                    ? Account::query()->whereNull('user_id')->orderBy('username')->get(['id', 'username'])
                        ->map(fn (Account $account): array => ['id' => $account->id, 'username' => $account->username])
                        ->all()
                    : [],
            ]);
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
