<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedEventResource;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Services\Accounts\AccountProfile;
use App\Services\Accounts\RecentEvents;
use App\Services\WiseOldMan\WomAccountView;
use App\Support\Instance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Adventurer's Log: the account's landing tab.
 */
class LogController extends Controller
{
    public function show(Request $request, Account $account, AccountProfile $profile, RecentEvents $recentEvents, WomAccountView $womView): Response
    {
        $account->load('equipment', 'hiscore')->append(['skills', 'bosses', 'clues']);

        return Inertia::render('Accounts/Show', [
            ...$profile->header($account, $request->user()),

            // Kept off AccountResource: that also serves the account lists, where
            // a count query per account would be one query per row.
            'instanceRank' => $account->instanceRank(),

            'avatar' => fn () => $account->avatarPayload(),

            'quests' => fn () => $account->quests,

            // Achievement Diary completion ({area: {tier: bool}}).
            'diaries' => fn () => $account->diary?->diaries ?? [],

            // Combat Achievement points + per-tier status, or
            // null until the plugin has pushed a snapshot.
            'combatAchievements' => fn () => $account->combatAchievement
                ? ['points' => $account->combatAchievement->points, 'tiers' => $account->combatAchievement->tiers]
                : null,

            // Owner-pinned events shown as the achievement gallery.
            'pinnedFeed' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()
                    ->where('account_id', $account->id)->pinned()->get(),
            )->resolve(),

            // First page of milestones merged with Wise Old Man records and
            // achievements; later pages come from accounts.events.
            'recentEvents' => fn () => RecentEventResource::page($recentEvents->page($account, viewer: $request->user()), $request),

            'freshness' => fn () => $profile->freshness($account, ['hiscores', 'quests', 'diaries', 'combat_achievements', 'equipment', 'avatar']),

            ...(Instance::womEnabled()
                ? ['wom' => fn () => $womView->summary($account->loadMissing('womPlayer'), $request->user())]
                : []),
        ]);
    }
}
