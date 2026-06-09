<?php

use App\Events\AccountDataUpdated;
use App\Models\Account;
use App\Models\AccountDiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function diaryHeaders(string $hash = 'hash-diary', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function diaryAccount(): Account
{
    $user = User::factory()->withPersonalTeam()->create();

    return Account::factory()->for($user)->create([
        'username' => 'Zlimon',
        'account_hash' => 'hash-diary',
    ]);
}

it('stores and normalises diary completion and broadcasts the update', function () {
    Event::fake([AccountDataUpdated::class]);
    $account = diaryAccount();
    Sanctum::actingAs($account->user);

    $this->putJson('/api/plugin/diaries', [
        'diaries' => [
            'Varrock' => ['Easy' => true, 'Medium' => true, 'Hard' => false, 'Elite' => false],
            'Karamja' => ['Easy' => true],
            'Bogus' => ['Easy' => true], // unknown area — dropped on normalise
        ],
    ], diaryHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.completed', 3);

    $diary = AccountDiary::where('account_id', $account->id)->firstOrFail();
    expect($diary->diaries['Varrock']['Easy'])->toBeTrue();
    expect($diary->diaries['Varrock']['Hard'])->toBeFalse();
    expect($diary->diaries['Karamja']['Easy'])->toBeTrue();
    // Canonical set only — unknown areas are dropped, missing tiers default false.
    expect($diary->diaries)->not->toHaveKey('Bogus');
    expect($diary->diaries['Wilderness']['Elite'])->toBeFalse();

    Event::assertDispatched(AccountDataUpdated::class,
        fn (AccountDataUpdated $e) => $e->type === 'diaries' && $e->account->is($account));
});

it('upserts on repeat pushes', function () {
    $account = diaryAccount();
    Sanctum::actingAs($account->user);

    $this->putJson('/api/plugin/diaries', ['diaries' => ['Varrock' => ['Easy' => true]]], diaryHeaders())->assertSuccessful();
    $this->putJson('/api/plugin/diaries', ['diaries' => ['Varrock' => ['Easy' => false, 'Medium' => true]]], diaryHeaders())->assertSuccessful();

    expect(AccountDiary::where('account_id', $account->id)->count())->toBe(1);
    $diary = AccountDiary::where('account_id', $account->id)->firstOrFail();
    expect($diary->diaries['Varrock']['Easy'])->toBeFalse();
    expect($diary->diaries['Varrock']['Medium'])->toBeTrue();
});

it('requires authentication', function () {
    $this->putJson('/api/plugin/diaries', ['diaries' => []], ['Accept' => 'application/json'])
        ->assertUnauthorized();
});

it('exposes diary completion on the account show page', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Diarist']);
    AccountDiary::create([
        'account_id' => $account->id,
        'diaries' => ['Lumbridge' => ['Easy' => true]],
    ]);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('diaries.Lumbridge.Easy', true),
        );
});

it('ranks the diaries leaderboard by tiers completed', function () {
    $most = Account::factory()->for(User::factory()->withPersonalTeam())->create(['username' => 'Maxed']);
    $few = Account::factory()->for(User::factory()->withPersonalTeam())->create(['username' => 'Starter']);

    AccountDiary::create(['account_id' => $most->id, 'diaries' => [
        'Varrock' => ['Easy' => true, 'Medium' => true, 'Hard' => true],
        'Karamja' => ['Elite' => true],
    ]]);
    AccountDiary::create(['account_id' => $few->id, 'diaries' => [
        'Lumbridge' => ['Easy' => true],
    ]]);

    $this->actingAs($most->user)
        ->get(route('hiscores.diaries.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/Diaries/Show')
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Maxed')
            ->where('hiscores.0.completed', 4)
            ->where('hiscores.1.account.username', 'Starter')
            ->where('hiscores.1.completed', 1),
        );
});
