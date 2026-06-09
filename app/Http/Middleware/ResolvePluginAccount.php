<?php

namespace App\Http\Middleware;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Services\Accounts\GroupIronmanValidator;
use App\Services\Accounts\RecordUsernameChange;
use App\Support\Instance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the OSRS account behind a plugin-initiated request.
 *
 * Reads two headers:
 *   - X-Account-Hash:     RuneLite's Client.getAccountHash() — stable identifier per OSRS account
 *   - X-Account-Username: the current in-game username
 *
 * Behaviour:
 *   - 401 if no authenticated user (sanctum must run first)
 *   - 422 if either header is missing
 *   - 403 if the hash is owned by a different user
 *   - Auto-provisions an Account for the authenticated user on first sighting of a hash
 *   - Records a UsernameHistory row if the supplied username differs from the stored one
 *
 * The resolved Account is attached to the request via $request->attributes->set('account', $account)
 * and is accessible from controllers as $request->attributes->get('account').
 */
class ResolvePluginAccount
{
    public function __construct(
        protected RecordUsernameChange $recorder,
        protected GroupIronmanValidator $groupIronman,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hash = $request->header('X-Account-Hash');
        $username = $request->header('X-Account-Username');

        if (empty($hash) || empty($username)) {
            return response()->json([
                'message' => 'Missing required headers.',
                'errors' => [
                    'X-Account-Hash' => $hash ? null : ['The X-Account-Hash header is required.'],
                    'X-Account-Username' => $username ? null : ['The X-Account-Username header is required.'],
                ],
            ], 422);
        }

        $account = Account::where('account_hash', $hash)->first();

        if ($account && $account->user_id !== $user->id) {
            return response()->json([
                'message' => 'This account is linked to a different user.',
            ], 403);
        }

        if (! $account) {
            // No hash match — fall through to a username lookup so the plugin can
            // adopt an existing row. This covers a pre-created roster account
            // (user_id null, SPEC §5.2) that this login now claims, plus a
            // placeholder-hash row already owned by this user. Usernames are
            // globally unique, so we find at most one row.
            $byUsername = Account::where('username', $username)->first();

            if ($byUsername && $byUsername->user_id !== null && $byUsername->user_id !== $user->id) {
                // Owned by someone else — don't let the caller hijack it.
                return response()->json([
                    'message' => 'This account is linked to a different user.',
                ], 403);
            }

            // GROUP mode is a Group Ironman group: validate the account type
            // (via TempleOSRS) before first linking/creating it. Steady-state
            // pushes hit the hash-match path above and skip this check.
            $claimingUnclaimed = $byUsername && $byUsername->user_id === null;
            if (Instance::isGroup() && ($claimingUnclaimed || ! $byUsername)
                && ! $this->groupIronman->isGroupIronman($username)) {
                return response()->json([
                    'message' => 'Only Group Ironman accounts can join this instance.',
                ], 403);
            }

            if ($byUsername) {
                // Claim it: logging into the game as this character proves
                // ownership, so link the (unclaimed) row and stamp the hash.
                $byUsername->user_id = $user->id;
                $byUsername->account_hash = $hash;
                $byUsername->save();
                $account = $byUsername;
            } else {
                $account = new Account;
                $account->user_id = $user->id;
                $account->account_hash = $hash;
                $account->account_type = Instance::isGroup()
                    ? AccountTypesEnum::GROUP_IRONMAN
                    : AccountTypesEnum::NORMAL;
                $account->username = $username;
                $account->rank = 0;
                $account->level = 0;
                $account->xp = 0;
                $account->save();
            }
        } elseif ($account->username !== $username) {
            $this->recorder->record($account, $username);
            $account->refresh();
        }

        $request->attributes->set('account', $account);

        return $next($request);
    }
}
