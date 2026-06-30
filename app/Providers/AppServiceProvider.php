<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        // Behind a TLS-terminating reverse proxy, PHP sees plain HTTP, so the URL
        // generator would emit http:// asset/links and the browser blocks them as
        // mixed content on an HTTPS page. Force https whenever the configured app
        // URL is https (production), leaving local http:// development untouched.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // The Owner holds every permission (SPEC §3.4). Management abilities are
        // checked via Spatie permissions (manage instance/members/…). Clan/group
        // elevation of other users is layered on later.
        Gate::before(fn (User $user) => $user->hasRole(Roles::OWNER) ? true : null);
    }
}
