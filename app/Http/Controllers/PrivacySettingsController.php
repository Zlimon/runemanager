<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountPrivacyRequest;
use App\Models\Account;
use App\Models\WomPlayer;
use App\Services\Accounts\UpdateAccountPrivacy;
use App\Services\WiseOldMan\RequestWomSync;
use App\Support\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PrivacySettingsController extends Controller
{
    /**
     * Settings → Privacy: each of the user's accounts with every switch that
     * decides what others see of it (events on the map, new screenshots, and
     * while the integration is on, Wise Old Man), with counts of what the
     * switches cover.
     */
    public function index(Request $request): Response
    {
        $womEnabled = Instance::womEnabled();

        return Inertia::render('Privacy/Index', [
            'wom_enabled' => $womEnabled,
            'accounts' => $request->user()->account()
                ->with('womPlayer')
                ->withCount([
                    'feedEvents as positioned_event_count' => fn (Builder $query) => $query->positioned(),
                    'feedEvents as screenshot_count' => fn (Builder $query) => $query->withScreenshot(),
                    'feedEvents as hidden_screenshot_count' => fn (Builder $query) => $query->withScreenshot()->where('screenshot_hidden', true),
                ])
                ->orderBy('username')
                ->get()
                ->map(fn (Account $account): array => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'account_type' => $account->account_type?->value,
                    'show_events_on_map' => $account->show_events_on_map,
                    'hide_new_screenshots' => $account->hide_new_screenshots,
                    'positioned_event_count' => (int) $account->positioned_event_count,
                    'screenshot_count' => (int) $account->screenshot_count,
                    'hidden_screenshot_count' => (int) $account->hidden_screenshot_count,
                    'wom' => $womEnabled ? self::womChoices($account->womPlayer) : null,
                ])
                ->all(),
        ]);
    }

    public function update(UpdateAccountPrivacyRequest $request, Account $account, UpdateAccountPrivacy $privacy): RedirectResponse
    {
        $privacy->update($account, $request->validated());

        return Instance::womEnabled() && $request->boolean('tracking_opt_in')
            ? back()->banner('Queued — Wise Old Man will update shortly.')
            : back();
    }

    /** The owner asks for a Wise Old Man refresh, once a day per account. */
    public function syncWom(Request $request, Account $account, RequestWomSync $sync): RedirectResponse
    {
        abort_unless(Instance::womEnabled(), 404);
        abort_unless($request->user()->id === $account->user_id, HttpResponse::HTTP_FORBIDDEN);

        return $sync->request($account)
            ? back()->banner('Queued — Wise Old Man will update shortly.')
            : back()->dangerBanner('This account was already synced in the last day. Please try again later.');
    }

    /**
     * An account's Wise Old Man link and choices; an account never looked up
     * has the defaults a new row gets.
     *
     * @return array{status: string, show_on_progress: bool, tracking_opt_in: bool, synced_at: ?string, sync_available_at: ?string}
     */
    private static function womChoices(?WomPlayer $player): array
    {
        return [
            'status' => match (true) {
                $player?->isLinked() === true => 'linked',
                $player?->not_found_at !== null => 'not_found',
                default => 'unchecked',
            },
            'show_on_progress' => $player?->show_on_progress ?? true,
            'tracking_opt_in' => $player?->tracking_opt_in ?? false,
            'synced_at' => $player?->synced_at?->toIso8601String(),
            'sync_available_at' => RequestWomSync::availableAt($player)?->toIso8601String(),
        ];
    }
}
