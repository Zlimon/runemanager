<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user. In clan/group mode the user must claim a
     * pre-created (unclaimed) roster account (SPEC §5.2); in casual mode that
     * field is ignored.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rosterRequired = Instance::requiresRosterClaim();

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'account_id' => $rosterRequired
                ? ['required', 'integer', Rule::exists('accounts', 'id')->whereNull('user_id')]
                : ['nullable'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'account_id.required' => 'Select the account you own to register.',
            'account_id.exists' => 'That account is unavailable — it may already be claimed.',
        ])->validate();

        return DB::transaction(function () use ($input, $rosterRequired) {
            $user = tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'icon_id' => Item::randomItemId(),
            ]), function (User $user) {
                $this->createTeam($user);
                $this->assignDefaultRole($user);
            });

            if ($rosterRequired) {
                $this->claimAccount($user, (int) $input['account_id']);
            }

            return $user;
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
     * The first registered user becomes the instance Owner (SPEC §3.4);
     * everyone after them joins as a plain User.
     */
    protected function assignDefaultRole(User $user): void
    {
        Roles::sync();

        $isFirstUser = ! Role::findByName(Roles::OWNER, 'web')->users()->exists();

        $user->assignRole($isFirstUser ? Roles::OWNER : Roles::USER);
    }

    /**
     * Link the selected roster account to the new user. Locked + re-checked so
     * two simultaneous registrations can't both claim the same account.
     */
    protected function claimAccount(User $user, int $accountId): void
    {
        $account = Account::query()
            ->whereKey($accountId)
            ->whereNull('user_id')
            ->lockForUpdate()
            ->firstOrFail();

        $account->user_id = $user->id;
        $account->save();
    }
}
