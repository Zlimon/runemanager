<?php

use App\Models\Account;
use App\Models\CollectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MongoDB\Driver\Exception\BulkWriteException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // RefreshDatabase only resets the SQL DB; the Mongo collection persists across tests.
    CollectionLog::query()->delete();
});

function makeAccountForLog(string $username = 'Zlimon'): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Test',
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

it('round-trips a collection log document with nested slots', function () {
    $account = makeAccountForLog();

    CollectionLog::create([
        'account_id' => $account->id,
        'slots' => [
            [
                'tab' => 'Bosses',
                'source' => 'Abyssal Sire',
                'item_id' => 13262,
                'name' => 'Abyssal orphan',
                'first_obtained_at' => '2026-06-01T12:00:00Z',
                'last_obtained_at' => '2026-06-01T12:00:00Z',
                'quantity' => 1,
            ],
        ],
    ]);

    $fetched = CollectionLog::where('account_id', $account->id)->first();

    expect($fetched)->not->toBeNull();
    expect($fetched->slots)->toHaveCount(1);
    expect($fetched->slots[0]['source'])->toBe('Abyssal Sire');
    expect($fetched->slots[0]['item_id'])->toBe(13262);
    expect($fetched->slots[0]['name'])->toBe('Abyssal orphan');
    expect($fetched->slots[0]['quantity'])->toBe(1);
});

it('enforces one document per account via the unique index', function () {
    $account = makeAccountForLog();

    CollectionLog::create(['account_id' => $account->id, 'slots' => []]);

    expect(fn () => CollectionLog::create(['account_id' => $account->id, 'slots' => []]))
        ->toThrow(BulkWriteException::class);
});

it('Account::collectionLog accessor returns null when no doc exists', function () {
    $account = makeAccountForLog();

    expect($account->collectionLog)->toBeNull();
});

it('Account::collectionLog accessor returns the doc when it exists', function () {
    $account = makeAccountForLog();

    CollectionLog::create([
        'account_id' => $account->id,
        'slots' => [
            ['tab' => 'Bosses', 'source' => 'Vorkath', 'item_id' => 22971, 'name' => 'Vorki', 'quantity' => 1],
        ],
    ]);

    expect($account->collectionLog)
        ->toBeInstanceOf(CollectionLog::class)
        ->and($account->collectionLog->slots[0]['name'])->toBe('Vorki');
});
