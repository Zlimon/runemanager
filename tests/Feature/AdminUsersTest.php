<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Users\SuspendUser;
use App\Services\Users\UpdateUserRole;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

function plainUser(array $attributes = []): User
{
    Roles::sync();

    return tap(User::factory()->create($attributes))->assignRole(Roles::USER);
}

it('sends guests to the login page', function () {
    $this->get(route('admin.users'))->assertRedirect(route('login'));
});

it('forbids users without manage instance', function () {
    $member = plainUser();
    $target = plainUser();

    $this->actingAs($member)->get(route('admin.users'))->assertForbidden();
    $this->actingAs($member)->get(route('admin.users.show', $target))->assertForbidden();
    $this->actingAs($member)->post(route('admin.users.suspend', $target), ['reason' => 'x'])->assertForbidden();
    $this->actingAs($member)->delete(route('admin.users.destroy', $target))->assertForbidden();

    expect($target->fresh()->isSuspended())->toBeFalse();
});

it('lists users with counts, roles and the last token use', function () {
    // The list sorts by name; pin the admin's so it always sorts after Bob.
    $admin = tap(adminUser())->update(['name' => 'Zara Admin']);
    $member = plainUser(['name' => 'Bob Member']);
    Account::factory()->count(2)->create(['user_id' => $member->id]);
    $member->createToken('plugin');
    $member->tokens()->first()->forceFill(['last_used_at' => now()->subHour()])->save();
    $member->createToken('plugin');

    $this->actingAs($admin)->get(route('admin.users'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data', 2)
            ->where('users.data.0.name', 'Bob Member')
            ->where('users.data.0.accounts_count', 2)
            ->where('users.data.0.tokens_count', 2)
            ->where('users.data.0.roles', [Roles::USER])
            ->where('users.data.0.suspended', false)
            ->whereNot('users.data.0.last_token_used_at', null)
            ->has('users.meta.links'));
});

it('searches by name or email without regard to case', function () {
    $admin = adminUser();
    plainUser(['name' => 'Alice Zamorak', 'email' => 'alice@example.test']);
    plainUser(['name' => 'Carol', 'email' => 'CAROL.Saradomin@example.test']);

    $this->actingAs($admin)->get(route('admin.users', ['search' => 'zamorak']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Alice Zamorak'));

    $this->actingAs($admin)->get(route('admin.users', ['search' => 'saradomin']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Carol'));
});

it('filters by status', function () {
    $admin = adminUser();
    plainUser(['name' => 'Active One']);
    plainUser(['name' => 'Banned One', 'suspended_at' => now()]);

    $this->actingAs($admin)->get(route('admin.users', ['status' => 'suspended']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Banned One')
            ->where('users.data.0.suspended', true));

    $this->actingAs($admin)->get(route('admin.users', ['status' => 'active']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('users.data', 2));

    $this->actingAs($admin)->get(route('admin.users', ['status' => 'owners']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $admin->id));
});

it('lists a page of users in a fixed number of queries', function () {
    $admin = adminUser();

    $count = function () use ($admin): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();

        return count(DB::getQueryLog());
    };

    $seed = function (int $users): void {
        foreach (range(1, $users) as $i) {
            $user = plainUser();
            $user->createToken('plugin');
            Account::factory()->create(['user_id' => $user->id]);
        }
    };

    // Each first request after seeding re-warms the caches behind the shared
    // props (a role assignment flushes Spatie's permission cache).
    $seed(1);
    $count();
    $baseline = $count();

    $seed(5);
    $count();
    expect($count())->toBe($baseline);
});

it('shows a user with accounts, tokens and the suspension', function () {
    $admin = adminUser();
    $member = plainUser(['suspended_at' => now(), 'suspension_reason' => 'Botting', 'suspended_by_user_id' => $admin->id]);
    Account::factory()->create(['user_id' => $member->id, 'username' => 'Zezima']);
    $member->createToken('plugin');

    $this->actingAs($admin)->get(route('admin.users.show', $member))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Users/Show')
            ->where('user.suspension.reason', 'Botting')
            ->where('user.suspension.by', $admin->name)
            ->where('accounts.0.username', 'Zezima')
            ->has('tokens', 1)
            ->where('isSelf', false)
            ->where('isLastActiveOwner', false));
});

it('suspends a user, revoking their tokens and database sessions', function () {
    config(['session.driver' => 'database']);
    $admin = adminUser();
    $member = plainUser();
    $member->createToken('plugin');
    DB::table('sessions')->insert([
        ['id' => 'member-session', 'user_id' => $member->id, 'ip_address' => null, 'user_agent' => null, 'payload' => '', 'last_activity' => now()->timestamp],
        ['id' => 'admin-session', 'user_id' => $admin->id, 'ip_address' => null, 'user_agent' => null, 'payload' => '', 'last_activity' => now()->timestamp],
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.suspend', $member), ['reason' => 'Botting'])
        ->assertRedirect();

    $member->refresh();
    expect($member->isSuspended())->toBeTrue()
        ->and($member->suspension_reason)->toBe('Botting')
        ->and($member->suspended_by_user_id)->toBe($admin->id)
        ->and($member->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->where('id', 'member-session')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'admin-session')->exists())->toBeTrue();
});

it('requires a reason to suspend', function () {
    $admin = adminUser();
    $member = plainUser();

    $this->actingAs($admin)->post(route('admin.users.suspend', $member), ['reason' => ''])
        ->assertSessionHasErrors('reason');
    $this->actingAs($admin)->post(route('admin.users.suspend', $member), ['reason' => str_repeat('a', 501)])
        ->assertSessionHasErrors('reason');

    expect($member->fresh()->isSuspended())->toBeFalse();
});

it('lifts a suspension', function () {
    $admin = adminUser();
    $member = plainUser(['suspended_at' => now(), 'suspension_reason' => 'Botting', 'suspended_by_user_id' => $admin->id]);

    $this->actingAs($admin)->delete(route('admin.users.unsuspend', $member))->assertRedirect();

    $member->refresh();
    expect($member->isSuspended())->toBeFalse()
        ->and($member->suspension_reason)->toBeNull()
        ->and($member->suspended_by_user_id)->toBeNull();
});

it('refuses to act on the acting admin themselves', function () {
    $admin = adminUser();
    adminUser();

    $this->actingAs($admin)->post(route('admin.users.suspend', $admin), ['reason' => 'x'])->assertForbidden();
    $this->actingAs($admin)->put(route('admin.users.role', $admin), ['role' => Roles::USER])->assertForbidden();
    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();

    expect($admin->fresh()->isSuspended())->toBeFalse()
        ->and($admin->fresh()->hasRole(Roles::OWNER))->toBeTrue();
});

it('refuses to suspend, demote or delete the last active owner', function () {
    // The acting admin holds manage instance without being an Owner, so the
    // target is the only Owner.
    Roles::sync();
    $role = Role::findOrCreate('Instance admin', 'web');
    $role->givePermissionTo(Roles::MANAGE_INSTANCE);
    $admin = tap(User::factory()->create())->assignRole($role);
    $owner = adminUser();

    $this->actingAs($admin)->post(route('admin.users.suspend', $owner), ['reason' => 'x'])
        ->assertSessionHasErrors(['user' => 'The last active owner cannot be suspended.']);
    $this->actingAs($admin)->put(route('admin.users.role', $owner), ['role' => Roles::USER])
        ->assertSessionHasErrors(['user' => 'The last active owner cannot be demoted.']);
    $this->actingAs($admin)->delete(route('admin.users.destroy', $owner))
        ->assertSessionHasErrors('password');

    expect($owner->fresh())->not->toBeNull()
        ->and($owner->fresh()->isSuspended())->toBeFalse()
        ->and($owner->fresh()->hasRole(Roles::OWNER))->toBeTrue();
});

it('counts a suspended owner as no longer active', function () {
    $admin = adminUser();
    adminUser()->forceFill(['suspended_at' => now()])->save();

    expect($admin->isLastActiveOwner())->toBeTrue();
});

it('changes a user role', function () {
    $admin = adminUser();
    $member = plainUser();

    $this->actingAs($admin)->put(route('admin.users.role', $member), ['role' => Roles::OWNER])->assertRedirect();
    expect($member->fresh()->hasRole(Roles::OWNER))->toBeTrue()
        ->and($member->fresh()->hasRole(Roles::USER))->toBeFalse();

    $this->actingAs($admin)->put(route('admin.users.role', $member), ['role' => Roles::USER])->assertRedirect();
    expect($member->fresh()->hasRole(Roles::USER))->toBeTrue()
        ->and($member->fresh()->hasRole(Roles::OWNER))->toBeFalse();

    $this->actingAs($admin)->put(route('admin.users.role', $member), ['role' => 'Emperor'])
        ->assertSessionHasErrors('role');
});

it('revokes one token or all of them', function () {
    $admin = adminUser();
    $member = plainUser();
    $first = $member->createToken('one')->accessToken;
    $member->createToken('two');
    $member->createToken('three');
    $otherToken = $admin->createToken('mine')->accessToken;

    $this->actingAs($admin)->delete(route('admin.users.tokens.destroy', [$member, $first->id]))->assertRedirect();
    expect($member->tokens()->count())->toBe(2);

    // Another user's token cannot be revoked through this user's URL.
    $this->actingAs($admin)->delete(route('admin.users.tokens.destroy', [$member, $otherToken->id]))->assertNotFound();

    $this->actingAs($admin)->delete(route('admin.users.tokens.destroy', [$member]))->assertRedirect();
    expect($member->tokens()->count())->toBe(0)
        ->and($admin->tokens()->count())->toBe(1);
});

it('deletes a user and returns their accounts to the roster', function () {
    $admin = adminUser();
    $member = plainUser();
    $account = Account::factory()->create(['user_id' => $member->id]);
    $member->createToken('plugin');

    $this->actingAs($admin)->delete(route('admin.users.destroy', $member))
        ->assertRedirect(route('admin.users'));

    expect(User::find($member->id))->toBeNull()
        ->and($account->fresh()->user_id)->toBeNull()
        ->and(DB::table('personal_access_tokens')->where('tokenable_id', $member->id)->count())->toBe(0);
});

it('lets one of two owners go, but then guards the one who remains', function () {
    $admin = adminUser();
    $first = adminUser();
    $second = adminUser();

    // Two other owners are active, so the first can be suspended and the
    // service's guard then treats the second as the last one standing
    // besides the acting admin.
    app(SuspendUser::class)->run($first, $admin, 'x');
    app(UpdateUserRole::class)->run($second, Roles::USER);

    expect($first->fresh()->isSuspended())->toBeTrue()
        ->and($second->fresh()->hasRole(Roles::OWNER))->toBeFalse()
        ->and(fn () => app(SuspendUser::class)->run($admin, $first, 'x'))
        ->toThrow(ValidationException::class, 'The last active owner cannot be suspended.')
        ->and($admin->fresh()->isSuspended())->toBeFalse();
});

it('refuses the last active owner deleting their own account from the profile page', function () {
    $owner = adminUser();

    $this->actingAs($owner)
        ->delete(route('current-user.destroy'), ['password' => 'password'])
        ->assertSessionHasErrors(['password' => 'The last active owner cannot be deleted. Make another user an owner first.']);

    expect($owner->fresh())->not->toBeNull();
});

it('lets an owner delete their own account while another owner remains', function () {
    $owner = adminUser();
    adminUser();

    $this->actingAs($owner)
        ->delete(route('current-user.destroy'), ['password' => 'password'])
        ->assertSessionHasNoErrors();

    expect($owner->fresh())->toBeNull();
});

it('falls back to every user for an unknown status filter', function () {
    $admin = adminUser();
    plainUser();

    $this->actingAs($admin)->get(route('admin.users', ['status' => 'foo', 'search' => ['not', 'a', 'string']]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.status', 'all')
            ->where('filters.search', '')
            ->has('users.data', 2));
});
