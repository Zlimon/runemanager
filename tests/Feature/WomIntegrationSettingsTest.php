<?php

use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('defaults to disabled, a 30-day window and a members-only progress page', function () {
    expect(Instance::womEnabled())->toBeFalse()
        ->and(Instance::womActiveWithinDays())->toBe(30)
        ->and(Instance::womProgressPublic())->toBeFalse();
});

it('falls back to the defaults when the settings table is unavailable', function () {
    Schema::drop('settings');

    expect(Instance::womEnabled())->toBeFalse()
        ->and(Instance::womActiveWithinDays())->toBe(30)
        ->and(Instance::womProgressPublic())->toBeFalse();
});

it('shows the Wise Old Man settings on the integrations page', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.integrations'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Settings/Integrations/Index')
            ->where('settings.wom_enabled', false)
            ->where('settings.wom_active_within_days', 30)
            ->where('settings.wom_progress_public', false)
            ->where('wom_enabled', false)
            ->where('wom_progress_public', false));
});

it('saves the Wise Old Man settings and shares them with every page', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->put(route('admin.integrations.update'), [
            'webhook_url' => '',
            'wom_enabled' => true,
            'wom_active_within_days' => 14,
            'wom_progress_public' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Instance::womEnabled())->toBeTrue()
        ->and(Instance::womActiveWithinDays())->toBe(14)
        ->and(Instance::womProgressPublic())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.integrations'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('wom_enabled', true)
            ->where('wom_progress_public', true));
});

it('rejects an activity window outside 1 to 365 days', function (int $days) {
    $this->actingAs(adminUser())
        ->putJson(route('admin.integrations.update'), ['wom_active_within_days' => $days])
        ->assertStatus(422)
        ->assertJsonValidationErrors('wom_active_within_days');
})->with([0, 366]);

it('keeps the integrations page to instance managers', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->put(route('admin.integrations.update'), ['wom_enabled' => true])
        ->assertForbidden();

    expect(Instance::womEnabled())->toBeFalse();
});
