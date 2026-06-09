<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
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
        // The Owner holds every permission (SPEC §3.4). Management abilities are
        // checked via Spatie permissions (manage instance/members/…). Clan/group
        // elevation of other users is layered on later.
        Gate::before(fn (User $user) => $user->hasRole(Roles::OWNER) ? true : null);
    }
}
