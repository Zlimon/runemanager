<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyScreenshotsRequest;
use App\Http\Requests\UpdateScreenshotsRequest;
use App\Http\Resources\ScreenshotResource;
use App\Models\FeedEvent;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Screenshots\DeleteScreenshots;
use App\Services\Screenshots\UpdateScreenshotVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ScreenshotController extends Controller
{
    /** Screenshots per admin queue page. */
    private const PER_PAGE = 24;

    /**
     * Admin → Screenshots: every feed event with a screenshot across every
     * account, hidden ones included, newest first. Filterable by username,
     * hidden/visible and event type; an unmatched filter falls back to
     * everything, same as the owner-facing gallery.
     */
    public function index(Request $request): Response
    {
        $username = trim((string) $request->query('username')) ?: null;
        $type = in_array($request->query('type'), RecordFeedEvent::SCREENSHOT_TYPES, true) ? $request->query('type') : null;
        $visibility = in_array($request->query('visibility'), ['hidden', 'visible'], true) ? $request->query('visibility') : null;

        return Inertia::render('Admin/Screenshots/Index', [
            'screenshots' => ScreenshotResource::collectionWith(
                FeedEvent::query()
                    ->withScreenshot()
                    ->when($username !== null, fn (Builder $query) => $query->whereHas(
                        'account',
                        fn (Builder $query) => $query->searchUsername($username),
                    ))
                    ->when($type !== null, fn (Builder $query) => $query->ofType($type))
                    ->when($visibility === 'hidden', fn (Builder $query) => $query->hiddenScreenshot())
                    ->when($visibility === 'visible', fn (Builder $query) => $query->where('screenshot_hidden', false))
                    ->orderByDesc('occurred_at')
                    ->orderByDesc('id')
                    ->paginate(self::PER_PAGE)
                    ->withQueryString(),
            ),
            'types' => array_map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::ucfirst(str_replace('_', ' ', $type)),
            ], RecordFeedEvent::SCREENSHOT_TYPES),
            'filters' => ['username' => $username, 'type' => $type, 'visibility' => $visibility],
        ]);
    }

    /** Hide or show the selected screenshots, of any account. */
    public function update(UpdateScreenshotsRequest $request, UpdateScreenshotVisibility $visibility): RedirectResponse
    {
        $ids = $this->ids($request->validated('ids'));
        $hidden = $request->boolean('hidden');

        $count = $visibility->updateAny($ids, $hidden);

        return back()->banner(sprintf('%s %d %s.', $hidden ? 'Hid' : 'Showed', $count, Str::plural('screenshot', $count)));
    }

    /** Delete the selected screenshots, of any account; their feed events stay. */
    public function destroy(DestroyScreenshotsRequest $request, DeleteScreenshots $screenshots): RedirectResponse
    {
        $count = $screenshots->deleteAny($this->ids($request->validated('ids')));

        return back()->banner(sprintf('Deleted %d %s.', $count, Str::plural('screenshot', $count)));
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return list<int>
     */
    private function ids(array $ids): array
    {
        return array_values(array_unique(array_map('intval', $ids)));
    }
}
