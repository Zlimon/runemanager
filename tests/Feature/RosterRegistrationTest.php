<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration is not enabled.');
    }

    Roles::sync();
});

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'New Member',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ], $overrides);
}

it('requires claiming a roster account in clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);

    $this->post('/register', registerPayload())
        ->assertSessionHasErrors('account_id');

    $this->assertGuest();
});

it('links the claimed account on registration as a plain User', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    // Pre-seed the owner so the new user isn't the bootstrap owner.
    User::factory()->withPersonalTeam()->create()->assignRole(Roles::OWNER);

    $account = Account::factory()->create([
        'username' => 'Claimee',
        'user_id' => null,
        'clan_rank' => 126,
    ]);

    $this->post('/register', registerPayload(['account_id' => $account->id]))
        ->assertRedirect();

    $this->assertAuthenticated();
    $user = User::where('email', 'new@example.com')->firstOrFail();
    expect($account->fresh()->user_id)->toBe($user->id);
    // Clan-rank → role elevation is deferred; the new user is a plain User.
    expect($user->hasRole(Roles::USER))->toBeTrue();
});

it('rejects claiming an already-claimed account', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);
    $owner = User::factory()->withPersonalTeam()->create();
    $taken = Account::factory()->for($owner)->create(['username' => 'Taken']);

    $this->post('/register', registerPayload(['account_id' => $taken->id]))
        ->assertSessionHasErrors('account_id');
});

it('ignores account_id in casual mode and registers freely', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->post('/register', registerPayload())
        ->assertRedirect();

    $this->assertAuthenticated();
});

it('surfaces the unclaimed roster accounts on the register page in clan mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    Account::factory()->create(['username' => 'Pickme', 'user_id' => null]);
    Account::factory()->create(['username' => 'Claimed', 'user_id' => User::factory()->withPersonalTeam()]);

    $this->get('/register')
        ->assertInertia(fn ($page) => $page
            ->component('Auth/Register')
            ->where('rosterRequired', true)
            ->has('rosterAccounts', 1)
            ->where('rosterAccounts.0.username', 'Pickme'),
        );
});
