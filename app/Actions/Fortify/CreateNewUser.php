<?php

namespace App\Actions\Fortify;

use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'icon_id' => Item::randomItemId(),
            ]), function (User $user) {
                $this->createTeam($user);
                $this->assignDefaultRole($user);
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }

    /**
     * The first registered user becomes the instance owner (SPEC §3.4);
     * everyone after them joins as a member.
     */
    protected function assignDefaultRole(User $user): void
    {
        foreach (['owner', 'admin', 'member'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $isFirstUser = ! Role::findByName('owner', 'web')->users()->exists();

        $user->assignRole($isFirstUser ? 'owner' : 'member');
    }
}
