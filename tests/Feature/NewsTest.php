<?php

use App\Models\Announcement;
use App\Models\NewsPost;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/** The smallest valid article payload, for the tests that vary one field. */
function articlePayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Bossing night',
        'excerpt' => 'We are running Nex on Friday.',
        'body' => '<p>Bring an ancient godsword.</p>',
        'published' => true,
    ], $overrides);
}

it('renders the news index publicly with only published posts, newest first', function () {
    $author = User::factory()->withPersonalTeam()->create();
    NewsPost::factory()->for($author)->create(['title' => 'Older', 'published_at' => now()->subWeek()]);
    NewsPost::factory()->for($author)->create(['title' => 'Newer', 'published_at' => now()->subDay()]);
    NewsPost::factory()->draft()->for($author)->create(['title' => 'Unfinished']);
    NewsPost::factory()->scheduled()->for($author)->create(['title' => 'Later']);

    // No actingAs — reading news is public.
    $this->get(route('news.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('News/Index')
            ->where('can_manage', false)
            ->has('posts.data', 2)
            ->where('posts.data.0.title', 'Newer')
            ->where('posts.data.1.title', 'Older'),
        );
});

it('shows drafts and scheduled posts to a news manager', function () {
    $manager = adminUser();
    NewsPost::factory()->for($manager)->create(['title' => 'Live']);
    NewsPost::factory()->draft()->for($manager)->create(['title' => 'Unfinished']);
    NewsPost::factory()->scheduled()->for($manager)->create(['title' => 'Later']);

    $this->actingAs($manager)
        ->get(route('news.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_manage', true)
            ->has('posts.data', 3),
        );
});

it('renders a published article by slug, body included', function () {
    $post = NewsPost::factory()
        ->for(User::factory()->withPersonalTeam())
        ->create(['title' => 'Bossing night', 'body' => '<p>Bring brews.</p>']);

    $this->get(route('news.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('News/Show')
            ->where('post.title', 'Bossing night')
            ->where('post.body', '<p>Bring brews.</p>')
            ->where('post.is_published', true),
        );
});

it('leaves the excerpt but not the body in a listing, so listings stay small', function () {
    NewsPost::factory()->for(User::factory()->withPersonalTeam())->create();

    $this->get(route('news.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data.0.excerpt')
            ->missing('posts.data.0.body'),
        );
});

it('hides a draft article from the public behind a 404, not a 403', function () {
    $post = NewsPost::factory()->draft()->for(User::factory()->withPersonalTeam())->create();

    $this->get(route('news.show', $post))->assertNotFound();
});

it('hides a scheduled article until its publish date arrives', function () {
    $post = NewsPost::factory()->scheduled()->for(User::factory()->withPersonalTeam())->create();

    $this->get(route('news.show', $post))->assertNotFound();
});

it('lets a news manager read a draft article', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->draft()->for($manager)->create();

    $this->actingAs($manager)
        ->get(route('news.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('post.is_published', false));
});

it('lets a news manager publish an article', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload())
        ->assertRedirect(route('news.show', 'bossing-night'));

    $post = NewsPost::firstOrFail();
    expect($post->title)->toBe('Bossing night');
    expect($post->slug)->toBe('bossing-night');
    expect($post->user_id)->toBe($manager->id);
    expect($post->isPublished())->toBeTrue();
    // Not asked for, so no in-game alert.
    expect($post->announcement_id)->toBeNull();
});

it('saves an unpublished article as a draft', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload(['published' => false]))
        ->assertRedirect();

    expect(NewsPost::firstOrFail()->published_at)->toBeNull();
});

it('treats the "0" an unticked checkbox sends as not published', function () {
    $manager = adminUser();

    // Inertia serialises booleans into a multipart body as "1"/"0", so an
    // unticked Publish box arrives as the string "0" and must not publish the
    // post or fire the in-game alert.
    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload(['published' => '0', 'announce' => '1']))
        ->assertRedirect();

    $post = NewsPost::firstOrFail();
    expect($post->published_at)->toBeNull();
    expect($post->announcement_id)->toBeNull();
    expect(Announcement::count())->toBe(0);
});

it('strips script and event handlers out of the article body before storing it', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload([
            'body' => '<p onclick="steal()">Hello</p>'
                .'<script>alert(1)</script>'
                .'<img src="x" onerror="alert(2)">'
                .'<a href="javascript:alert(3)">click</a>'
                .'<iframe src="https://evil.test"></iframe>',
        ]))
        ->assertRedirect();

    $body = NewsPost::firstOrFail()->body;

    expect($body)->not->toContain('script');
    expect($body)->not->toContain('onclick');
    expect($body)->not->toContain('onerror');
    expect($body)->not->toContain('javascript:');
    expect($body)->not->toContain('iframe');
    // The safe content survives.
    expect($body)->toContain('Hello');
});

it('keeps the formatting the editor is allowed to produce', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload([
            'body' => '<h2>Plan</h2><p><strong>Bring</strong> <em>brews</em></p>'
                .'<ul><li>Godsword</li></ul><blockquote><p>Be on time.</p></blockquote>'
                .'<a href="https://osrs.example/wiki">wiki</a>',
        ]))
        ->assertRedirect();

    $body = NewsPost::firstOrFail()->body;

    expect($body)->toContain('<h2>');
    expect($body)->toContain('<strong>');
    expect($body)->toContain('<em>');
    expect($body)->toContain('<li>');
    expect($body)->toContain('<blockquote>');
    expect($body)->toContain('https://osrs.example/wiki');
});

it('raises a linked in-game announcement when asked', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload(['announce' => true]))
        ->assertRedirect();

    $post = NewsPost::firstOrFail();
    $announcement = Announcement::firstOrFail();

    expect($post->announcement_id)->toBe($announcement->id);
    expect($announcement->title)->toBe('Bossing night');
    expect($announcement->body)->toBe('We are running Nex on Friday.');
    expect($announcement->user_id)->toBe($manager->id);
});

it('does not announce a draft, which would point players at a 404', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->post(route('news.store'), articlePayload(['published' => false, 'announce' => true]))
        ->assertRedirect();

    expect(NewsPost::firstOrFail()->announcement_id)->toBeNull();
    expect(Announcement::count())->toBe(0);
});

it('announces a draft only once it is published', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->draft()->for($manager)->create();

    $this->actingAs($manager)
        ->post(route('news.update', $post), articlePayload(['announce' => true]))
        ->assertRedirect();

    expect(Announcement::count())->toBe(1);
    expect($post->fresh()->announcement_id)->not->toBeNull();
});

it('withdraws the announcement when the author unticks the alert', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->for($manager)->create();

    $this->actingAs($manager)->post(route('news.update', $post), articlePayload(['announce' => true]));
    expect(Announcement::count())->toBe(1);

    $this->actingAs($manager)->post(route('news.update', $post->fresh()), articlePayload(['announce' => false]));

    expect(Announcement::count())->toBe(0);
    expect($post->fresh()->announcement_id)->toBeNull();
});

it('refreshes the existing announcement rather than raising a second one', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->for($manager)->create();

    $this->actingAs($manager)->post(route('news.update', $post), articlePayload(['announce' => true]));
    $original = Announcement::firstOrFail();

    // Accounts that already acknowledged the first announcement must not be
    // shown a fresh one just because the article was edited.
    $this->actingAs($manager)->post(route('news.update', $post->fresh()), articlePayload([
        'title' => 'Bossing night (moved)',
        'announce' => true,
    ]));

    expect(Announcement::count())->toBe(1);
    expect(Announcement::firstOrFail()->id)->toBe($original->id);
    expect(Announcement::firstOrFail()->title)->toBe('Bossing night (moved)');
});

it('gives two posts sharing a title distinct slugs', function () {
    $manager = adminUser();

    $this->actingAs($manager)->post(route('news.store'), articlePayload(['title' => 'Weekly recap']));
    $this->actingAs($manager)->post(route('news.store'), articlePayload(['title' => 'Weekly recap']));

    expect(NewsPost::pluck('slug')->all())->toBe(['weekly-recap', 'weekly-recap-2']);
});

it('keeps the slug when a published article is edited without a title change', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->for($manager)->create(['title' => 'Bossing night', 'slug' => 'bossing-night']);

    // The URL has already been shared in-game, so an edit must not move it.
    $this->actingAs($manager)
        ->post(route('news.update', $post), articlePayload(['excerpt' => 'Now on Saturday.']))
        ->assertRedirect(route('news.show', 'bossing-night'));

    expect($post->fresh()->slug)->toBe('bossing-night');
});

it('stores and clears the optional header image', function () {
    Storage::fake('public');
    $manager = adminUser();

    $this->actingAs($manager)->post(route('news.store'), articlePayload([
        'image' => UploadedFile::fake()->image('header.png'),
    ]));

    $post = NewsPost::firstOrFail();
    expect($post->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);
    $stored = $post->image_path;

    $this->actingAs($manager)->post(route('news.update', $post), articlePayload(['remove_image' => true]));

    expect($post->fresh()->image_path)->toBeNull();
    // The replaced file must not be left behind on disk.
    Storage::disk('public')->assertMissing($stored);
});

it('falls back to the bundled default image when a post has none', function () {
    $post = NewsPost::factory()->for(User::factory()->withPersonalTeam())->create(['image_path' => null]);

    expect($post->image_url)->toBe(NewsPost::DEFAULT_IMAGE);
});

it('deletes a post and the image it owned', function () {
    Storage::fake('public');
    $manager = adminUser();

    $this->actingAs($manager)->post(route('news.store'), articlePayload([
        'image' => UploadedFile::fake()->image('header.png'),
    ]));
    $post = NewsPost::firstOrFail();
    $stored = $post->image_path;

    $this->actingAs($manager)
        ->delete(route('news.destroy', $post))
        ->assertRedirect(route('news.index'));

    expect(NewsPost::count())->toBe(0);
    Storage::disk('public')->assertMissing($stored);
});

it('keeps the article when its author is deleted', function () {
    $author = adminUser();
    $post = NewsPost::factory()->for($author)->create();

    $author->delete();

    expect($post->fresh())->not->toBeNull();
    expect($post->fresh()->user_id)->toBeNull();
});

it('validates the article payload', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->postJson(route('news.store'), ['title' => '', 'excerpt' => '', 'body' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'excerpt', 'body']);
});

it('rejects guests from writing news', function () {
    $this->post(route('news.store'), articlePayload())->assertRedirect(route('login'));
});

it('rejects a member without the manage news permission', function () {
    $member = adminUser(Roles::USER);

    $this->actingAs($member)->get(route('news.create'))->assertForbidden();
    $this->actingAs($member)->post(route('news.store'), articlePayload())->assertForbidden();
});

it('rejects a member from uploading an inline image', function () {
    $member = adminUser(Roles::USER);

    $this->actingAs($member)
        ->post(route('news.images.store'), ['image' => UploadedFile::fake()->image('inline.png')])
        ->assertForbidden();
});

it('returns the stored url for an inline editor image', function () {
    Storage::fake('public');
    $manager = adminUser();

    $response = $this->actingAs($manager)
        ->postJson(route('news.images.store'), ['image' => UploadedFile::fake()->image('inline.png')])
        ->assertOk();

    expect($response->json('data.url'))->toContain('/storage/news/');
});

it('rejects a non-image upload', function () {
    $manager = adminUser();

    $this->actingAs($manager)
        ->postJson(route('news.images.store'), ['image' => UploadedFile::fake()->create('payload.php', 10)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('shows the latest published news on the dashboard', function () {
    $user = adminUser();
    NewsPost::factory(4)->for($user)->create();
    NewsPost::factory()->draft()->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('news', 3));
});

it('renders the editor page for a new and an existing article', function () {
    $manager = adminUser();
    $post = NewsPost::factory()->for($manager)->create();

    $this->actingAs($manager)
        ->get(route('news.create'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('News/Editor')->where('post', null));

    $this->actingAs($manager)
        ->get(route('news.edit', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('News/Editor')
            ->where('post.id', $post->id)
            ->has('post.body'),
        );
});
