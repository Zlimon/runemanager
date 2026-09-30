<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsPostRequest;
use App\Http\Requests\UpdateNewsPostRequest;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use App\Services\News\SaveNewsPost;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Writing news. Every route here is gated by `can:manage news`, so the methods
 * carry no authorisation of their own.
 *
 * Create and edit render the same page — the editor only differs by whether it
 * starts with a post in it, the same way the announcements dialog does.
 */
class EditorController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('News/Editor', [
            'post' => null,
        ]);
    }

    public function edit(NewsPost $newsPost): Response
    {
        $newsPost->load('user:id,name');

        return Inertia::render('News/Editor', [
            'post' => NewsPostResource::full($newsPost)->resolve(),
        ]);
    }

    public function store(StoreNewsPostRequest $request, SaveNewsPost $saveNewsPost): RedirectResponse
    {
        $post = $saveNewsPost->create(
            $request->user(),
            $request->articleAttributes(),
            $request->file('image'),
        );

        return redirect()
            ->route('news.show', $post)
            ->with('status', $post->isPublished() ? 'Article published.' : 'Draft saved.');
    }

    public function update(UpdateNewsPostRequest $request, NewsPost $newsPost, SaveNewsPost $saveNewsPost): RedirectResponse
    {
        $post = $saveNewsPost->update(
            $newsPost,
            $request->articleAttributes(),
            $request->file('image'),
        );

        return redirect()
            ->route('news.show', $post)
            ->with('status', $post->isPublished() ? 'Article updated.' : 'Draft saved.');
    }

    public function destroy(NewsPost $newsPost, SaveNewsPost $saveNewsPost): RedirectResponse
    {
        $saveNewsPost->delete($newsPost);

        // Deleting from Admin → News returns there, search and page kept;
        // anywhere else (the article page, the public archive) still lands
        // on news.index. Admin → News is the only caller with a query
        // string worth preserving, so this checks it by name rather than
        // using back(), which would drop it.
        $previous = url()->previous();
        $redirect = str($previous)->startsWith(route('admin.news'))
            ? redirect($previous)
            : redirect()->route('news.index');

        return $redirect->with('status', 'Article deleted.');
    }
}
