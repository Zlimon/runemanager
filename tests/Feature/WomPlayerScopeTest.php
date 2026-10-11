<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\WomPlayer;

it('counts only linked, WOM-active profiles that changed within the window as active', function () {
    $active = WomPlayer::factory()->create();
    WomPlayer::factory()->inactive()->create();
    WomPlayer::factory()->notFound()->create();
    WomPlayer::factory()->create(['status' => 'flagged']);
    WomPlayer::factory()->create(['last_changed_at' => null]);

    expect(WomPlayer::active()->pluck('id')->all())->toBe([$active->id]);
});

it('follows the admin activity window', function () {
    $player = WomPlayer::factory()->create(['last_changed_at' => now()->subDays(45)]);

    expect(WomPlayer::active()->count())->toBe(0);

    SettingHelper::setSetting('wom_active_within_days', 60, 'int');

    expect(WomPlayer::active()->pluck('id')->all())->toBe([$player->id]);
});

it('separates linked rows and rows hidden from progress', function () {
    $linked = WomPlayer::factory()->create();
    $hidden = WomPlayer::factory()->create(['show_on_progress' => false]);
    WomPlayer::factory()->notFound()->create();

    expect(WomPlayer::linked()->pluck('id')->sort()->values()->all())->toBe([$linked->id, $hidden->id])
        ->and(WomPlayer::visibleOnProgress()->linked()->pluck('id')->all())->toBe([$linked->id]);
});

it('hangs off its account and goes with it', function () {
    $account = Account::factory()->create();
    $player = WomPlayer::factory()->for($account)->create();

    expect($account->womPlayer->is($player))->toBeTrue();

    $account->delete();

    expect(WomPlayer::count())->toBe(0);
});
