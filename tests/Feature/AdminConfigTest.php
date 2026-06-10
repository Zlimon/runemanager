<?php

use App\Helpers\SettingHelper;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('saves the instance description via settings', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CASUAL,
            'instance_description' => 'The finest clan in Gielinor.',
        ])
        ->assertRedirect();

    expect(Instance::description())->toBe('The finest clan in Gielinor.');
});

it('saves feed + sync config and reflects it through Instance readers', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.config.update'), [
            'hiscore_refresh_minutes' => 180,
            'feed_level_up_thresholds' => '70, 80, 99, 80, 1, 120',
            'feed_loot_min_value' => 250000,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Instance::hiscoreRefreshMinutes())->toBe(180);
    expect(Instance::hiscoreRefreshCron())->toBe('0 */3 * * *');
    // Sanitised on read: unique, in-range (2–99), sorted.
    expect(Instance::feedLevelUpThresholds())->toBe([70, 80, 99]);
    expect(Instance::feedLootMinValue())->toBe(250000);
});

it('validates the config payload', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.config.update'), [
            'hiscore_refresh_minutes' => 7,
            'feed_level_up_thresholds' => 'abc',
            'feed_loot_min_value' => -5,
        ])
        ->assertJsonValidationErrors(['hiscore_refresh_minutes', 'feed_level_up_thresholds', 'feed_loot_min_value']);
});

it('maps refresh minutes to a cron cadence', function () {
    expect(Instance::hiscoreRefreshCron())->toBe('0 * * * *'); // default 60
});

it('uploads and removes branding assets', function () {
    Storage::fake('public');

    $this->actingAs(adminUser())
        ->post(route('admin.branding.update'), [
            'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
        ])
        ->assertRedirect();

    $path = SettingHelper::getSetting('logo_path');
    expect($path)->not->toBeEmpty();
    Storage::disk('public')->assertExists($path);
    expect(Instance::logoUrl())->not->toBeNull();

    // Removing it clears the setting and deletes the file.
    $this->actingAs(adminUser())
        ->post(route('admin.branding.update'), ['remove_logo' => true])
        ->assertRedirect();

    expect(SettingHelper::getSetting('logo_path'))->toBeEmpty();
    Storage::disk('public')->assertMissing($path);
});

it('rejects a non-image logo upload', function () {
    Storage::fake('public');

    $this->actingAs(adminUser())
        ->post(route('admin.branding.update'), [
            'logo' => UploadedFile::fake()->create('virus.pdf', 10),
        ])
        ->assertSessionHasErrors('logo');
});

it('forbids non-admins from the config + branding endpoints', function () {
    $user = adminUser(Roles::USER);

    $this->actingAs($user)
        ->put(route('admin.config.update'), [
            'hiscore_refresh_minutes' => 60,
            'feed_loot_min_value' => 0,
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('admin.branding.update'), [])
        ->assertForbidden();
});
