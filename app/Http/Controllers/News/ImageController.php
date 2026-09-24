<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsImageRequest;
use App\Models\NewsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Inline images for the article editor. Plain JSON rather than an Inertia
 * visit: the editor drops the returned URL straight into the document the
 * author is typing in, and a visit would re-render the page and lose it.
 *
 * The image is stored as soon as it is picked, before the article is saved, so
 * an abandoned draft can leave a file behind. That is the same trade the feed's
 * screenshots make, and is preferable to holding the bytes in the browser and
 * posting a body full of base64.
 */
class ImageController extends Controller
{
    public function store(StoreNewsImageRequest $request): JsonResponse
    {
        $path = $request->file('image')->store(NewsPost::IMAGE_DIRECTORY, 'public');

        return response()->json([
            'data' => ['url' => Storage::disk('public')->url($path)],
        ]);
    }
}
