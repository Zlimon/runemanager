<?php

namespace App\Services\News;

use App\Jobs\DeliverWebhook;
use App\Models\Announcement;
use App\Models\NewsPost;
use App\Models\User;
use App\Services\Webhooks\WebhookPayload;
use App\Support\Instance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Creates and updates news posts. Everything a saved article needs beyond the
 * validated fields lives here, because the editor page, a future artisan
 * command and the tests all need the same rules: the body must be sanitised
 * before it is ever stored, the slug must stay unique, a replaced header image
 * must not leak a file on disk, and the optional in-game announcement has to
 * follow the post's published state.
 */
class SaveNewsPost
{
    public function __construct(private readonly SanitizeArticleHtml $sanitizer) {}

    /**
     * @param  array<string, mixed>  $attributes  Validated fields from the request.
     */
    public function create(User $author, array $attributes, ?UploadedFile $image = null): NewsPost
    {
        return DB::transaction(function () use ($author, $attributes, $image): NewsPost {
            $post = NewsPost::query()->create([
                'user_id' => $author->id,
                'title' => $attributes['title'],
                'slug' => NewsPost::uniqueSlug($attributes['title']),
                'excerpt' => $attributes['excerpt'],
                'body' => $this->sanitizer->clean($attributes['body']),
                'image_path' => $image?->store(NewsPost::IMAGE_DIRECTORY, 'public'),
                'published_at' => $this->publishedAt($attributes),
            ]);

            $this->syncAnnouncement($post, $author, (bool) ($attributes['announce'] ?? false));

            return $post;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes  Validated fields from the request.
     */
    public function update(NewsPost $post, array $attributes, ?UploadedFile $image = null): NewsPost
    {
        return DB::transaction(function () use ($post, $attributes, $image): NewsPost {
            // Re-slug only on a real title change, so a published article keeps
            // the URL that was shared in-game and on Discord.
            $slug = $attributes['title'] === $post->title
                ? $post->slug
                : NewsPost::uniqueSlug($attributes['title'], $post->id);

            $post->fill([
                'title' => $attributes['title'],
                'slug' => $slug,
                'excerpt' => $attributes['excerpt'],
                'body' => $this->sanitizer->clean($attributes['body']),
                'published_at' => $this->publishedAt($attributes),
            ]);

            if ($image !== null) {
                $this->replaceImage($post, $image->store(NewsPost::IMAGE_DIRECTORY, 'public'));
            } elseif (($attributes['remove_image'] ?? false) === true) {
                $this->replaceImage($post, null);
            }

            $post->save();

            $author = $post->user ?? User::query()->whereKey($post->user_id)->first();
            $this->syncAnnouncement($post, $author, (bool) ($attributes['announce'] ?? false));

            return $post;
        });
    }

    /**
     * Removes the post and the header image it owns. The linked announcement is
     * left standing: players may already have seen it in-game, and the
     * migration's `nullOnDelete` means nothing dangles.
     */
    public function delete(NewsPost $post): void
    {
        $this->deleteImage($post->image_path);

        $post->delete();
    }

    /**
     * A draft has no `published_at`. Publishing without an explicit date means
     * "now", and an explicit date in the future schedules the post.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function publishedAt(array $attributes): ?string
    {
        if (($attributes['published'] ?? false) !== true) {
            return null;
        }

        return $attributes['published_at'] ?? now()->toDateTimeString();
    }

    /**
     * Keeps the in-game announcement in step with the post.
     *
     * An announcement is only raised for a post players can actually read, so a
     * draft or scheduled post never sends one — the alert would point at a page
     * that 404s. Unticking the box withdraws it. An existing announcement has
     * its wording refreshed rather than being replaced, because the accounts
     * that already acknowledged it would otherwise be shown it a second time.
     */
    private function syncAnnouncement(NewsPost $post, ?User $author, bool $announce): void
    {
        $wanted = $announce && $post->isPublished();

        if (! $wanted) {
            if ($post->announcement_id !== null) {
                $post->announcement?->delete();
                $post->announcement_id = null;
                $post->saveQuietly();
            }

            return;
        }

        $attributes = [
            'title' => $post->title,
            'body' => $post->excerpt,
        ];

        if ($post->announcement_id !== null) {
            $post->announcement?->update($attributes);

            return;
        }

        $announcement = Announcement::query()->create($attributes + ['user_id' => $author?->id]);

        $post->announcement_id = $announcement->id;
        $post->saveQuietly();

        // Same forwarding the announcements page does, so a news post reaches
        // Discord as well as the in-game alert.
        if ($url = Instance::webhookUrl()) {
            DeliverWebhook::dispatch($url, WebhookPayload::forAnnouncement($announcement), 'announcement');
        }
    }

    private function replaceImage(NewsPost $post, ?string $path): void
    {
        $this->deleteImage($post->image_path);

        $post->image_path = $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
