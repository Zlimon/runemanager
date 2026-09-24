<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * An article written by a news manager: title, short excerpt for the listings,
 * and a sanitised HTML body from the article editor. `published_at` being null
 * means the post is still a draft, visible only to the managers.
 *
 * A post may carry an optional header image and may be linked to an
 * Announcement, which is what puts an alert in front of players in-game.
 */
#[Fillable([
    'user_id',
    'announcement_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'image_path',
    'published_at',
])]
class NewsPost extends Model
{
    use HasFactory;

    /**
     * Shown when a post has no image of its own. Lives in `public/images`
     * rather than a resource pack: a news header is site content, not an
     * in-game texture, so no pack has an equivalent sprite.
     */
    public const DEFAULT_IMAGE = '/images/newspost_default.png';

    /**
     * Where header images are stored on the public disk.
     */
    public const IMAGE_DIRECTORY = 'news';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Posts are addressed by slug everywhere — page URLs, the editor's form
     * targets and the links shared in-game — so route binding uses it too and
     * Ziggy generates readable URLs from a post object without the callers
     * naming the key.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The in-game announcement this post raised, if the author asked for one.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Published posts, newest first. A future `published_at` is a scheduled
     * post and stays hidden until then.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    /**
     * Every post, newest activity first — drafts included. Only the authoring
     * surface uses this; the draft ordering falls back to creation time
     * because a draft has no `published_at` to sort on.
     */
    #[Scope]
    protected function latestFirst(Builder $query): void
    {
        $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * The header image URL, falling back to the bundled default.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : self::DEFAULT_IMAGE,
        );
    }

    /**
     * A URL-safe slug for the title, suffixed if that slug is already taken so
     * two posts sharing a title (say, a recurring "Weekly recap") cannot
     * collide on the table's unique index.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;

        $exists = fn (string $candidate): bool => static::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();

        for ($suffix = 2; $exists($slug); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
