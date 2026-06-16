<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('renders the public landing page for guests with the digest props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Welcome')
            ->where('canLogin', true)
            ->where('canRegister', true)
            ->where('anonymized', false)
            ->has('stats')
            ->has('upcoming')
            ->has('topAccounts')
            ->missing('announcements')
            ->missing('feed'),
        );
});

it('exposes public counts, top accounts and upcoming events to guests', function () {
    $user = User::factory()->withPersonalTeam()->create();

    Account::factory()->for($user)->create(['username' => 'High', 'level' => 1500, 'last_seen_at' => now()]);
    Account::factory()->for($user)->create(['username' => 'Unsynced', 'level' => 0, 'last_seen_at' => now()->subDay()]);

    CalendarEvent::factory()->for($user)->create(['title' => 'Raid Night', 'starts_at' => now()->addDay()]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.accounts', 2)
            ->where('stats.members', 1)
            ->where('stats.online', 1)
            ->has('topAccounts', 1)
            ->where('topAccounts.0.username', 'High')
            ->has('upcoming', 1)
            ->where('upcoming.0.title', 'Raid Night'),
        );
});

it('masks usernames on the public page when anonymisation is enabled', function () {
    SettingHelper::setSetting('public_anonymize_accounts', true, 'bool');

    $user = User::factory()->withPersonalTeam()->create();
    Account::factory()->for($user)->create(['username' => 'Zezima', 'level' => 1500]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('anonymized', true)
            ->where('topAccounts.0.username', 'Z•••••'),
        );
});
