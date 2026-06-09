<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Instance;
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
        // The instance owner can do everything (SPEC §3.4).
        Gate::before(fn (User $user) => $user->hasRole('owner') ? true : null);

        // Admin abilities (manage announcements, calendar, instance config, …).
        // In GROUP mode every member is an admin (SPEC §2.2); otherwise it's the
        // owner/admin roles (CLAN ranks sync into these later).
        Gate::define('admin', fn (User $user) => Instance::isGroup() || $user->hasRole(['owner', 'admin']));
    }
}
