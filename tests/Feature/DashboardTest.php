<?php

use App\Models\Account;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
});

it('redirects guests to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('renders the dashboard with the digest props', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('stats', fn ($stats) => $stats
                ->where('accounts', 0)
                ->where('members', 0)
                ->where('online', 0),
            )
            ->has('announcements')
            ->has('upcoming')
            ->has('topAccounts')
            ->has('feed'),
        );
});

it('counts accounts, distinct members and online accounts', function () {
    $other = User::factory()->withPersonalTeam()->create();

    // Two accounts on one user, one on another → 3 accounts, 2 members.
    Account::factory()->count(2)->for($this->user)->create(['last_seen_at' => now()]);
    Account::factory()->for($other)->create(['last_seen_at' => now()->subHour()]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.accounts', 3)
            ->where('stats.members', 2)
            ->where('stats.online', 2),
        );
});

it('ranks top accounts by total level desc and skips unsynced accounts', function () {
    Account::factory()->for($this->user)->create(['username' => 'High', 'level' => 1500, 'xp' => 100]);
    Account::factory()->for($this->user)->create(['username' => 'Low', 'level' => 800, 'xp' => 50]);
    Account::factory()->for($this->user)->create(['username' => 'Unsynced', 'level' => 0]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('topAccounts', 2)
            ->where('topAccounts.0.username', 'High')
            ->where('topAccounts.1.username', 'Low'),
        );
});

it('surfaces active announcements and upcoming events', function () {
    Announcement::factory()->for($this->user)->create(['title' => 'Welcome', 'expires_at' => null]);
    Announcement::factory()->for($this->user)->create(['title' => 'Expired', 'expires_at' => now()->subDay()]);

    CalendarEvent::factory()->for($this->user)->create(['title' => 'Raid Night', 'starts_at' => now()->addDay()]);
    CalendarEvent::factory()->for($this->user)->create(['title' => 'Old Event', 'starts_at' => now()->subDay()]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('announcements', 1)
            ->where('announcements.0.title', 'Welcome')
            ->has('upcoming', 1)
            ->where('upcoming.0.title', 'Raid Night'),
        );
});
