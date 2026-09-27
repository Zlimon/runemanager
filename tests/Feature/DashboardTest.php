<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\FeedEvent;
use App\Models\User;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
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
            ->component('Dashboard/Index')
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

it('includes the owning user id on feed accounts so the owner sees pin/delete', function () {
    $account = Account::factory()->for($this->user)->create();
    FeedEvent::factory()->for($account)->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('feed', 1)
            ->where('feed.0.account.user_id', $this->user->id)
            ->where('feed.0.account.username', $account->username),
        );
});

it('defers the week\'s top WOM gainers only while Wise Old Man is on', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->missing('topGainers'));

    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $player = WomPlayer::factory()->create();
    foreach ([[8, 1_000], [0, 6_000]] as [$daysAgo, $exp]) {
        WomDailySnapshot::query()->create([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp, 'ehp' => 0.0, 'ehb' => 0.0,
        ]);
    }

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('topGainers')
            ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                ->has('topGainers', 1)
                ->where('topGainers.0.account_id', $player->account_id)
                ->where('topGainers.0.gained', 5_000),
            ),
        );
});
