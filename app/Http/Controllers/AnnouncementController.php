<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Jobs\DeliverWebhook;
use App\Models\Announcement;
use App\Services\Webhooks\WebhookPayload;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AnnouncementController extends Controller
{
    /**
     * Viewing is public (available in all modes). Lists active
     * (non-expired) announcements, newest first.
     */
    public function index(): Response
    {
        return Inertia::render('Announcements/Index', [
            'announcements' => AnnouncementResource::collection(
                Announcement::with('user:id,name')->active()->get(),
            )->resolve(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $announcement = $request->user()->announcements()->create($request->validated());

        // Forward to the instance's configured webhook (e.g. Discord).
        if ($url = Instance::webhookUrl()) {
            DeliverWebhook::dispatch($url, WebhookPayload::forAnnouncement($announcement), 'announcement');
        }

        return redirect()->route('announcements.index');
    }

    /**
     * Delete an announcement. `manage announcements` covers any announcement,
     * not only your own — the same shape as {@see FeedController::destroy()},
     * where the permission also overrides authorship. Authorship alone grants
     * nothing here: the route is permission-gated, so a member can never reach
     * this method to delete even a post of their own.
     */
    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(
            $request->user()?->can(Roles::MANAGE_ANNOUNCEMENTS) === true,
            HttpResponse::HTTP_FORBIDDEN,
        );

        $announcement->delete();

        return redirect()->route('announcements.index');
    }
}
