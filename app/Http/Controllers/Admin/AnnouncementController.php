<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Admin → Announcements: every announcement, expired ones included
     * (unlike the public page's `active()` scope), with how many accounts
     * have acknowledged each in-game. Creating and deleting reuse the
     * `announcements.*` routes; this page is only a moderation-friendly index
     * over them.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => AnnouncementResource::collection(
                Announcement::query()
                    ->with('user:id,name')
                    ->withCount('acknowledgedBy')
                    ->latest()
                    ->get(),
            )->resolve(),
        ]);
    }
}
