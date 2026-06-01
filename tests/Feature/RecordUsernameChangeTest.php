<?php

use App\Models\Account;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Services\Accounts\RecordUsernameChange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAccountFor(string $username): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Test User',
        'email' => $username.'@test.local',
        'password' => bcrypt('password'),
        'icon_id' => 0,
    ]);

    return Account::query()->forceCreate([
        'user_id' => $user->id,
        'account_type' => 'normal',
        'username' => $username,
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);
}

it('is a no-op when the username is unchanged', function () {
    $account = makeAccountFor('Zlimon');

    $result = app(RecordUsernameChange::class)->record($account, 'Zlimon');

    expect($result)->toBeNull();
    expect($account->fresh()->username)->toBe('Zlimon');
    expect(UsernameHistory::query()->count())->toBe(0);
});

it('logs history and updates the account on change', function () {
    $account = makeAccountFor('OldName');

    $history = app(RecordUsernameChange::class)->record($account, 'NewName');

    expect($history)->not->toBeNull();
    expect($history->old_username)->toBe('OldName');
    expect($history->new_username)->toBe('NewName');
    expect($history->detected_at)->not->toBeNull();
    expect($account->fresh()->username)->toBe('NewName');
    expect(UsernameHistory::query()->count())->toBe(1);
});

it('records each rename as a separate row', function () {
    $account = makeAccountFor('First');
    $service = app(RecordUsernameChange::class);

    $service->record($account, 'Second');
    $service->record($account->fresh(), 'Third');
    $service->record($account->fresh(), 'Fourth');

    expect($account->fresh()->username)->toBe('Fourth');
    expect(UsernameHistory::query()->count())->toBe(3);

    $entries = UsernameHistory::query()->orderBy('id')->get();
    expect($entries->pluck('old_username')->all())->toBe(['First', 'Second', 'Third']);
    expect($entries->pluck('new_username')->all())->toBe(['Second', 'Third', 'Fourth']);
});

it('exposes history through Account::usernameHistory ordered most-recent-first', function () {
    $account = makeAccountFor('Alpha');
    $service = app(RecordUsernameChange::class);

    $service->record($account, 'Bravo');
    $service->record($account->fresh(), 'Charlie');

    $history = $account->fresh()->usernameHistory;

    expect($history)->toHaveCount(2);
    expect($history[0]->new_username)->toBe('Charlie');
    expect($history[1]->new_username)->toBe('Bravo');
});
