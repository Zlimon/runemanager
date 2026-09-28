<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use App\Services\Screenshots\DeleteScreenshots;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        protected DeletesTeams $deletesTeams,
        protected DeleteScreenshots $deleteScreenshots,
    ) {}

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            // An OSRS account outlives the user who claimed it and goes back to
            // the roster as unclaimed. The foreign key nulls this too, but doing
            // it here keeps the behaviour the same on a connection where foreign
            // keys are not enforced — a left-behind id would hide the account
            // from the "unclaimed accounts" list and strand it. The event-map
            // opt-in, the screenshot default and the screenshots were this
            // user's, so they go with them (a mass update skips Account's hooks
            // that do this on a claim).
            $accountIds = $user->account()->pluck('id')->all();
            $user->account()->update(['user_id' => null, 'show_events_on_map' => false, 'hide_new_screenshots' => false]);
            $this->deleteScreenshots->forAccounts($accountIds);
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function (Team $team) {
            $this->deletesTeams->delete($team);
        });
    }
}
