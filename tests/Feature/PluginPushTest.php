<?php

use App\Models\Account;
use App\Models\Bank;
use App\Models\Equipment;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // RefreshDatabase only resets SQL; the Mongo collections persist between tests.
    Inventory::query()->delete();
    Bank::query()->delete();
    Quest::query()->delete();
    LootingBag::query()->delete();
    Loot::query()->delete();
});

function freshUser(string $email = 'plugin@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Plugin User',
        'email' => $email,
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function pluginHeaders(string $hash, string $username): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function pushInventoryAs(User $user, string $hash, string $username): TestResponse
{
    Sanctum::actingAs($user);

    return test()->putJson('/api/plugin/inventory', [
        'inventory' => [
            [4151, 1],
            [556, 1000],
        ],
    ], pluginHeaders($hash, $username));
}

it('rejects pushes without an auth token', function () {
    $this->putJson('/api/plugin/inventory', ['inventory' => []])->assertUnauthorized();
});

it('rejects pushes that are missing the account headers', function () {
    Sanctum::actingAs(freshUser());

    $this->withHeaders(['Accept' => 'application/json'])
        ->putJson('/api/plugin/inventory', ['inventory' => []])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['X-Account-Hash', 'X-Account-Username']);
});

it('auto-provisions an Account on first push from a new hash', function () {
    $user = freshUser();

    expect(Account::count())->toBe(0);

    pushInventoryAs($user, 'hash-aaa', 'Zlimon')->assertSuccessful();

    expect(Account::count())->toBe(1);
    $account = Account::first();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_hash)->toBe('hash-aaa');
    expect($account->username)->toBe('Zlimon');
});

it('reuses the existing Account on subsequent pushes with the same hash', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-bbb', 'Zlimon')->assertSuccessful();
    pushInventoryAs($user, 'hash-bbb', 'Zlimon')->assertSuccessful();

    expect(Account::count())->toBe(1);
});

it('records a UsernameHistory row when the supplied username drifts', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-ccc', 'OldName')->assertSuccessful();
    pushInventoryAs($user, 'hash-ccc', 'NewName')->assertSuccessful();

    $account = Account::where('account_hash', 'hash-ccc')->first();
    expect($account->username)->toBe('NewName');

    $history = UsernameHistory::query()->get();
    expect($history)->toHaveCount(1);
    expect($history->first()->old_username)->toBe('OldName');
    expect($history->first()->new_username)->toBe('NewName');
});

it('adopts an existing seeded row (hash placeholder) when the username matches the same user', function () {
    $user = freshUser('seed@test.local');

    // Simulate the seeder having created the account with a placeholder hash
    // before the real plugin connects with the actual Java-long hash.
    $seeded = Account::query()->forceCreate([
        'user_id' => $user->id,
        'account_hash' => 'seed-placeholder-xxx',
        'account_type' => 'normal',
        'username' => 'Habski',
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);

    pushInventoryAs($user, '-3217384136573234808', 'Habski')->assertSuccessful();

    expect(Account::count())->toBe(1);
    $adopted = Account::find($seeded->id);
    expect($adopted->account_hash)->toBe('-3217384136573234808');
    expect($adopted->user_id)->toBe($user->id);
});

it('forbids adopting a username already owned by a different user', function () {
    $owner = freshUser('owner@test.local');
    $intruder = freshUser('intruder@test.local');

    Account::query()->forceCreate([
        'user_id' => $owner->id,
        'account_hash' => 'seed-placeholder-yyy',
        'account_type' => 'normal',
        'username' => 'Habski',
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);

    pushInventoryAs($intruder, 'real-plugin-hash', 'Habski')->assertForbidden();

    expect(Account::count())->toBe(1);
    expect(Account::first()->user_id)->toBe($owner->id);
});

it('rejects with 403 when the hash is linked to a different user', function () {
    $userA = freshUser('a@test.local');
    $userB = freshUser('b@test.local');

    pushInventoryAs($userA, 'hash-ddd', 'Zlimon')->assertSuccessful();

    pushInventoryAs($userB, 'hash-ddd', 'Impostor')->assertForbidden();

    expect(Account::count())->toBe(1);
    expect(UsernameHistory::query()->count())->toBe(0);
});

it('upserts the Mongo inventory document on push', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-eee', 'Zlimon')->assertSuccessful();

    $account = Account::where('account_hash', 'hash-eee')->first();
    $inv = Inventory::where('account_id', $account->id)->first();

    expect($inv)->not->toBeNull();
    expect($inv->inventory)->toBe([[4151, 1], [556, 1000]]);
});

it('upserts the Mongo bank document on PUT /api/plugin/bank', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    $payload = [
        'bank' => [
            [[4151, 1], [556, 5000]],  // tab 0
            [[1163, 1]],               // tab 1
        ],
    ];

    $this->putJson('/api/plugin/bank', $payload, pluginHeaders('hash-bank', 'Zlimon'))
        ->assertSuccessful();

    $account = Account::where('account_hash', 'hash-bank')->first();
    $bank = Bank::where('account_id', $account->id)->first();

    expect($bank->bank)->toBe([[[4151, 1], [556, 5000]], [[1163, 1]]]);
});

it('writes equipment slots on PUT /api/plugin/equipment', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    // Plugin shape: sparse array indexed by KitType slot, each [itemId, qty].
    // -1 in slot 0 = empty. Slots 6, 8, 11 are gaps in KitType and we don't read them.
    $payload = [
        'equipment' => [
            0 => [11832, 1],   // head: bandos chestplate (just illustrative)
            1 => [21295, 1],   // cape
            2 => [-1, 0],      // amulet empty
            3 => [4151, 1],    // weapon: abyssal whip
            4 => [12437, 1],   // body
            5 => [1201, 1],    // shield
            7 => [4087, 1],    // legs
            9 => [7462, 1],    // hands
            10 => [11840, 1],  // feet
            12 => [6735, 1],   // ring
            13 => [882, 1000], // ammo
        ],
    ];

    $this->putJson('/api/plugin/equipment', $payload, pluginHeaders('hash-eq', 'Zlimon'))
        ->assertSuccessful();

    $account = Account::where('account_hash', 'hash-eq')->first();
    $eq = Equipment::where('account_id', $account->id)->first();

    expect($eq->weapon)->toBe(4151);
    expect($eq->neck)->toBeNull();
    expect($eq->ammo)->toBe(882);
});

it('upserts the Mongo quest doc on PUT /api/plugin/quests', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    $payload = [
        'quests' => [
            ['Cooks Assistant', 2],
            ['Dragon Slayer II', 1],
        ],
    ];

    $this->putJson('/api/plugin/quests', $payload, pluginHeaders('hash-q', 'Zlimon'))
        ->assertSuccessful();

    $account = Account::where('account_hash', 'hash-q')->first();
    $q = Quest::where('account_id', $account->id)->first();

    expect($q->quests)->toBe([['Cooks Assistant', 2], ['Dragon Slayer II', 1]]);
});

it('upserts the Mongo looting bag doc on PUT /api/plugin/looting-bag', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    $payload = [
        'looting_bag' => [
            [995, 250000],
            [560, 100],
        ],
    ];

    $this->putJson('/api/plugin/looting-bag', $payload, pluginHeaders('hash-lb', 'Zlimon'))
        ->assertSuccessful();

    $account = Account::where('account_hash', 'hash-lb')->first();
    $bag = LootingBag::where('account_id', $account->id)->first();

    expect($bag->looting_bag)->toBe([[995, 250000], [560, 100]]);
});

it('appends loot entries on POST /api/plugin/loot', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    $payload = [
        'loot' => [
            [
                'source' => 'Abyssal demon',
                'items' => [
                    ['id' => 4151, 'quantity' => 1],
                    ['id' => 995, 'quantity' => 10000],
                ],
                'total_value' => 2_500_000,
                'killed_at' => '2026-06-02T17:30:00Z',
            ],
            [
                'source' => 'Vorkath',
                'items' => [['id' => 21907, 'quantity' => 1]],
                'killed_at' => '2026-06-02T17:35:00Z',
            ],
        ],
    ];

    $this->postJson('/api/plugin/loot', $payload, pluginHeaders('hash-loot', 'Zlimon'))
        ->assertCreated()
        ->assertJsonPath('data.inserted', 2);

    $account = Account::where('account_hash', 'hash-loot')->first();
    $entries = Loot::where('account_id', $account->id)->orderBy('killed_at')->get();

    expect($entries)->toHaveCount(2);
    expect($entries[0]->source)->toBe('Abyssal demon');
    // Mongo BSON doesn't preserve assoc-array key order; use loose equality (==)
    // so the assertion isn't sensitive to id/quantity coming back in either order.
    expect($entries[0]->items)->toEqual([
        ['id' => 4151, 'quantity' => 1],
        ['id' => 995, 'quantity' => 10000],
    ]);
    expect($entries[0]->total_value)->toBe(2_500_000);
    expect($entries[1]->source)->toBe('Vorkath');
    expect($entries[1]->total_value)->toBe(0);
});

it('does NOT replace prior loot — successive pushes accumulate', function () {
    $user = freshUser();
    Sanctum::actingAs($user);

    $headers = pluginHeaders('hash-acc', 'Zlimon');

    $this->postJson('/api/plugin/loot', [
        'loot' => [[
            'source' => 'Abyssal demon',
            'items' => [['id' => 4151, 'quantity' => 1]],
            'killed_at' => '2026-06-02T17:00:00Z',
        ]],
    ], $headers)->assertCreated();

    $this->postJson('/api/plugin/loot', [
        'loot' => [[
            'source' => 'Zulrah',
            'items' => [['id' => 12934, 'quantity' => 1]],
            'killed_at' => '2026-06-02T17:05:00Z',
        ]],
    ], $headers)->assertCreated();

    $account = Account::where('account_hash', 'hash-acc')->first();
    expect(Loot::where('account_id', $account->id)->count())->toBe(2);
});

it('rejects loot pushes that are missing required fields', function () {
    Sanctum::actingAs(freshUser());

    $this->postJson('/api/plugin/loot', [
        'loot' => [['source' => 'No items here', 'killed_at' => '2026-06-02T17:00:00Z']],
    ], pluginHeaders('hash-x', 'Zlimon'))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['loot.0.items']);
});
