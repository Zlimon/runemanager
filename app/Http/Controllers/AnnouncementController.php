<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AnnouncementController extends Controller
{
    /**
     * SPEC §9 — viewing is public (available in all modes). Lists active
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
        $request->user()->announcements()->create($request->validated());

        return redirect()->route('announcements.index');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        abort_if(
            $announcement->user_id !== request()->user()?->id,
            HttpResponse::HTTP_FORBIDDEN,
        );

        $announcement->delete();

        return redirect()->route('announcements.index');
    }
}
