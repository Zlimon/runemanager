<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    /** Posts per admin listing page. */
    private const PER_PAGE = 15;

    /**
     * Admin → News: every post — drafts and scheduled ones included, unlike
     * the public archive — newest activity first, searchable by title.
     * Editing, creating and deleting reuse the `news.*` routes; this page is
     * only a moderation-friendly index over them.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search')) ?: null;

        return Inertia::render('Admin/News/Index', [
            'posts' => NewsPostResource::collection(
                NewsPost::query()
                    ->with('user:id,name')
                    ->when($search !== null, fn ($query) => $query->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($search).'%']))
                    ->latestFirst()
                    ->paginate(self::PER_PAGE)
                    ->withQueryString(),
            ),
            'filters' => ['search' => $search],
        ]);
    }
}
