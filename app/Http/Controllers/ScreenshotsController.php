<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyScreenshotsRequest;
use App\Http\Requests\UpdateScreenshotsRequest;
use App\Http\Resources\ScreenshotResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Screenshots\DeleteScreenshots;
use App\Services\Screenshots\UpdateScreenshotVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ScreenshotsController extends Controller
{
    /** Screenshots per gallery page. */
    private const PER_PAGE = 24;

    /**
     * Settings → Screenshots: every screenshot of the user's own accounts,
     * newest first, optionally narrowed to one account (`?account=`) or one
     * event type (`?type=`). An unknown filter value shows everything.
     */
    public function index(Request $request): Response
    {
        $accounts = $request->user()->account()->orderBy('username')->get(['id', 'username']);

        $account = filter_var($request->query('account'), FILTER_VALIDATE_INT) ?: null;
        $account = $accounts->contains('id', $account) ? $account : null;
        $type = in_array($request->query('type'), RecordFeedEvent::SCREENSHOT_TYPES, true) ? $request->query('type') : null;

        return Inertia::render('Screenshots/Index', [
            'screenshots' => ScreenshotResource::collectionWith(
                FeedEvent::query()
                    ->ownedBy($request->user())
                    ->withScreenshot()
                    ->when($account !== null, fn (Builder $query) => $query->where('account_id', $account))
                    ->when($type !== null, fn (Builder $query) => $query->ofType($type))
                    ->orderByDesc('occurred_at')
                    ->orderByDesc('id')
                    ->paginate(self::PER_PAGE)
                    ->withQueryString(),
            ),
            'accounts' => $accounts->map(fn (Account $account): array => [
                'id' => $account->id,
                'username' => $account->username,
            ])->all(),
            'types' => array_map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::ucfirst(str_replace('_', ' ', $type)),
            ], RecordFeedEvent::SCREENSHOT_TYPES),
            'filters' => ['account' => $account, 'type' => $type],
        ]);
    }

    /** Hide or show the selected screenshots; ids of other people's events are skipped. */
    public function update(UpdateScreenshotsRequest $request, UpdateScreenshotVisibility $visibility): RedirectResponse
    {
        $ids = $this->ownIds($request, $request->validated('ids'));
        $hidden = $request->boolean('hidden');

        $count = $visibility->update($request->user(), $ids, $hidden);

        return back()->banner(sprintf('%s %d %s.', $hidden ? 'Hid' : 'Showed', $count, Str::plural('screenshot', $count)));
    }

    /** Delete the selected screenshots (their feed events stay); ids of other people's events are skipped. */
    public function destroy(DestroyScreenshotsRequest $request, DeleteScreenshots $screenshots): RedirectResponse
    {
        $count = $screenshots->delete($request->user(), $this->ownIds($request, $request->validated('ids')));

        return back()->banner(sprintf('Deleted %d %s.', $count, Str::plural('screenshot', $count)));
    }

    /**
     * The ids to act on, or a 403 when none of them is an event of the
     * user's own accounts.
     *
     * @param  array<int, mixed>  $ids
     * @return list<int>
     */
    private function ownIds(Request $request, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        abort_unless(
            FeedEvent::query()->whereIn('id', $ids)->ownedBy($request->user())->exists(),
            HttpResponse::HTTP_FORBIDDEN,
        );

        return $ids;
    }
}
