<?php

namespace App\Http\Resources;

use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A news post for the listings, the dashboard digest and the article page.
 *
 * The HTML body is only included by {@see self::full()}, so a listing of twenty
 * posts does not ship twenty whole articles to the browser. Callers must
 * eager-load `user`, which the author name reads.
 */
class NewsPostResource extends JsonResource
{
    private bool $withBody = false;

    /**
     * The whole article, body included — for the article page and the editor.
     */
    public static function full(NewsPost $post): self
    {
        $resource = new self($post);
        $resource->withBody = true;

        return $resource;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image_url' => $this->image_url,
            // Null marks a draft (or, with a future date, a scheduled post).
            // Only a news manager is ever sent one.
            'published_at' => $this->published_at?->toIso8601String(),
            'is_published' => $this->isPublished(),
            // Set when the post also raised an in-game announcement.
            'announcement_id' => $this->announcement_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
        ];

        if ($this->withBody) {
            $payload['body'] = $this->body;
        }

        return $payload;
    }
}
