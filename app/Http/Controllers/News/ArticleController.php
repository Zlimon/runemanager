<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use App\Support\Roles;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Reading news. Public in every mode, like the announcements and calendar
 * pages — a prospective member should be able to read a clan's news before
 * joining it.
 */
class ArticleController extends Controller
{
    /**
     * The news archive, newest first. Paginated because this grows without
     * bound, unlike the announcements list which expires its own entries.
     *
     * Drafts are included for news managers only, so they can find an unfinished
     * post again without a separate admin screen.
     */
    public function index(Request $request): Response
    {
        $canManage = $request->user()?->can(Roles::MANAGE_NEWS) === true;

        return Inertia::render('News/Index', [
            // Passed unresolved, like the Accounts index: a paginated resource
            // collection carries the `links` and `meta` the pager reads.
            'posts' => NewsPostResource::collection(
                NewsPost::query()
                    ->with('user:id,name')
                    ->when($canManage, fn ($query) => $query->latestFirst(), fn ($query) => $query->published())
                    ->paginate(9)
                    ->withQueryString(),
            ),
            'can_manage' => $canManage,
        ]);
    }

    /**
     * One article, resolved by slug so the URL stays readable when it is shared
     * in-game or on Discord.
     */
    public function show(Request $request, NewsPost $newsPost): Response
    {
        // A draft is only readable by the people who can edit it. 404 rather
        // than 403: an unpublished article should not admit it exists.
        abort_unless(
            $newsPost->isPublished() || $request->user()?->can(Roles::MANAGE_NEWS) === true,
            HttpResponse::HTTP_NOT_FOUND,
        );

        $newsPost->load('user:id,name');

        return Inertia::render('News/Show', [
            'post' => NewsPostResource::full($newsPost)->resolve(),
            'can_manage' => $request->user()?->can(Roles::MANAGE_NEWS) === true,
            'recent' => NewsPostResource::collection(
                NewsPost::query()
                    ->with('user:id,name')
                    ->published()
                    ->whereKeyNot($newsPost->id)
                    ->limit(4)
                    ->get(),
            )->resolve(),
        ]);
    }
}
