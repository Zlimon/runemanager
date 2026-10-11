<?php

use App\Models\NewsPost;
use App\Models\User;
use App\Support\Roles;
use Inertia\Testing\AssertableInertia;

it('forbids guests from the admin news page', function () {
    $this->get(route('admin.news'))->assertRedirect(route('login'));
});

it('forbids a user without manage instance from the admin news page', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.news'))
        ->assertForbidden();
});

it('lists every post for a manage-instance user, drafts and scheduled ones included', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $published = NewsPost::factory()->for($author)->create(['title' => 'Published Post', 'published_at' => now()->subDay()]);
    $draft = NewsPost::factory()->for($author)->draft()->create(['title' => 'Draft Post']);
    $scheduled = NewsPost::factory()->for($author)->scheduled()->create(['title' => 'Scheduled Post']);

    $this->actingAs(adminUser())
        ->get(route('admin.news'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/News/Index')
            ->has('posts.data', 3)
            ->where('filters', ['search' => null]),
        );

    $titles = collect(
        $this->actingAs(adminUser())->get(route('admin.news'))->inertiaProps('posts')['data'],
    )->pluck('title');

    expect($titles)->toContain('Published Post', 'Draft Post', 'Scheduled Post');
});

it('searches posts by title', function () {
    $author = User::factory()->withPersonalTeam()->create();
    NewsPost::factory()->for($author)->create(['title' => 'Raid Update']);
    NewsPost::factory()->for($author)->create(['title' => 'Server Maintenance']);

    $this->actingAs(adminUser())
        ->get(route('admin.news', ['search' => 'raid']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Raid Update')
            ->where('filters.search', 'raid'));
});

it('lets an admin delete a post from the admin listing via the existing route', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $post = NewsPost::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->delete(route('news.destroy', $post))
        ->assertRedirect(route('news.index'));

    expect(NewsPost::find($post->id))->toBeNull();
});

it('returns to the admin news list when deleting from there', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $post = NewsPost::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->from(route('admin.news'))
        ->delete(route('news.destroy', $post))
        ->assertRedirect(route('admin.news'));

    expect(NewsPost::find($post->id))->toBeNull();
});

it('still returns to the public news index when deleting from the article page', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $post = NewsPost::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->from(route('news.show', $post))
        ->delete(route('news.destroy', $post))
        ->assertRedirect(route('news.index'));
});

it('keeps the admin news list\'s search and page when returning from a delete', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $post = NewsPost::factory()->for($author)->create();
    $filtered = route('admin.news', ['search' => 'raid', 'page' => 2]);

    $this->actingAs(adminUser())
        ->from($filtered)
        ->delete(route('news.destroy', $post))
        ->assertRedirect($filtered);
});
