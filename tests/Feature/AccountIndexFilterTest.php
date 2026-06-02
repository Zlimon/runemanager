<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create();
});

it('renders the index page with the Inertia component and shared shape', function () {
    Account::factory()->for($this->user)->create([
        'account_type' => 'normal',
        'username' => 'Alpha',
    ]);

    $this->actingAs($this->user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Index')
            ->has('accountTypes')
            ->has('accounts.data')
            ->has('filters', fn ($filters) => $filters
                ->where('username', '')
                ->where('account_types', [])
                ->where('per_page', 16),
            ),
        );
});

it('filters accounts by username substring', function () {
    Account::factory()->for($this->user)->create(['username' => 'Foo', 'account_type' => 'normal']);
    Account::factory()->for($this->user)->create(['username' => 'Bar', 'account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['username' => 'Foo']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Index')
            ->has('accounts.data', 1)
            ->where('accounts.data.0.username', 'Foo'),
        );
});

it('filters accounts by account_types[]', function () {
    Account::factory()->for($this->user)->create(['username' => 'IronOne', 'account_type' => 'ironman']);
    Account::factory()->for($this->user)->create(['username' => 'NormOne', 'account_type' => 'normal']);

    $this->actingAs($this->user)
        ->get(route('accounts.index', ['account_types' => ['ironman']]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('accounts.data', 1)
            ->where('accounts.data.0.username', 'IronOne'),
        );
});

it('rejects an account_type not in the enum', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.index', ['account_types' => ['not-a-real-type']]))
        ->assertSessionHasErrors(['account_types.0']);
});

it('show renders the Inertia component with the `account` prop', function () {
    $account = Account::factory()->for($this->user)->create(['username' => 'Solo']);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->has('account')
            ->where('account.username', 'Solo'),
        );
});
