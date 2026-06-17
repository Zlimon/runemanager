<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedEventResource;
use App\Models\FeedEvent;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class FeedController extends Controller
{
    /**
     * SPEC §8 — reverse-chronological event stream for the instance. Seeds the
     * page; new events arrive live on the public `feed` broadcast channel
     * (FeedEventCreated), so there's no polling.
     */
    public function index(): Response
    {
        $pageSize = (int) config('runemanager.feed.page_size', 50);

        return Inertia::render('Feed/Index', [
            'events' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()->with('account:id,user_id,username,account_type')->recent($pageSize)->get(),
            )->resolve(),
        ]);
    }

    /**
     * Delete a feed entry. Allowed for an instance admin or the owner of the
     * account the entry belongs to. Removes its screenshot too.
     */
    public function destroy(Request $request, FeedEvent $feedEvent): RedirectResponse
    {
        $user = $request->user();
        $canDelete = $user !== null
            && ($user->can(Roles::MANAGE_INSTANCE) || $user->id === $feedEvent->account?->user_id);

        abort_unless($canDelete, HttpResponse::HTTP_FORBIDDEN);

        if ($feedEvent->screenshot_path) {
            Storage::disk('public')->delete($feedEvent->screenshot_path);
        }

        $feedEvent->delete();

        return back();
    }

    /**
     * Pin/unpin a feed entry to the account's achievement gallery. Only the
     * owner of the account curates their own gallery.
     */
    public function togglePin(Request $request, FeedEvent $feedEvent): RedirectResponse
    {
        abort_unless(
            $request->user()?->id === $feedEvent->account?->user_id,
            HttpResponse::HTTP_FORBIDDEN,
        );

        $feedEvent->update(['pinned_at' => $feedEvent->pinned_at ? null : now()]);

        return back();
    }
}
