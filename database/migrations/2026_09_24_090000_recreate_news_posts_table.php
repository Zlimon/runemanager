<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the news scaffolding left over from the first version of the site.
 * The old `news_posts` was never reachable: no controller, no route and no page
 * ever referenced it, so its columns describe a page that no longer exists —
 * `title` capped at 50 characters, a 100-character `shortstory`, and two
 * foreign keys (`news_category_id`, `image_id`) into tables that exist only to
 * serve it.
 *
 * `news_categories` and `images` go with it. `news_categories` held three fixed
 * rows nothing read, and `images` is old-style upload bookkeeping — a row per
 * file recording name, extension, type and size — which the application stopped
 * doing when branding moved to storing a path directly on the record (see
 * Admin\BrandingController). The new table follows that pattern with
 * `image_path`.
 *
 * The drop order matters: `news_posts` owns the foreign keys, so it has to go
 * before the two tables it points at, or Postgres refuses with "cannot drop
 * table because other objects depend on it". SQLite does not enforce this, so
 * the test suite stays green either way and will not catch a reordering.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('news_posts');
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('images');

        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();

            // A post outlives its author, the same rule accounts and the old
            // news_posts table were given in the 2026_09_22 migrations.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Set when the post raised an in-game announcement. Clearing the
            // announcement must not take the article with it.
            $table->foreignId('announcement_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title', 160);
            $table->string('slug')->unique();
            $table->string('excerpt', 300);

            // Sanitised HTML from the article editor, never raw editor output.
            $table->longText('body');

            // Relative path on the public disk; null falls back to the bundled
            // default image.
            $table->string('image_path')->nullable();

            // Null means draft — only the news managers see it.
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index('published_at');
        });
    }

    /**
     * Recreates the three tables as their original migrations defined them,
     * minus the seeded `news_categories` rows and the default image row, which
     * the drop above destroys for good.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_posts');

        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category')->unique();
        });

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('image_file_name');
            $table->string('image_file_extension');
            $table->string('image_file_type');
            $table->integer('image_file_size');
            $table->timestamps();
        });

        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('news_category_id')->constrained()->default(1);
            $table->foreignId('image_id')->constrained()->default(1);
            $table->string('title', 50);
            $table->string('shortstory', 100);
            $table->longText('longstory');
            $table->timestamps();
        });
    }
};
