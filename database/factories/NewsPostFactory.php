<?php

namespace Database\Factories;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title($this->faker->words(rand(3, 6), true));

        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'announcement_id' => null,
            'title' => $title,
            // Not NewsPost::uniqueSlug(): the factory may be building a batch
            // that has not been inserted yet, so a database lookup cannot see
            // the slugs its siblings are about to take.
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'excerpt' => $this->faker->sentence(),
            'body' => $this->article(),
            'image_path' => null,
            'published_at' => $this->faker->dateTimeBetween('-6 months'),
        ];
    }

    /**
     * An unpublished post, visible only to the news managers.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => ['published_at' => null]);
    }

    /**
     * Published at a future date, so it stays hidden until then.
     */
    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'published_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Body markup limited to what the editor's toolbar produces and the `news`
     * purifier profile allows, so demo content exercises the real article
     * styling rather than a wall of one paragraph.
     */
    private function article(): string
    {
        $paragraphs = collect($this->faker->paragraphs(rand(2, 4)))
            ->map(fn (string $text): string => "<p>{$text}</p>")
            ->implode('');

        $bullets = collect($this->faker->words(3))
            ->map(fn (string $word): string => '<li>'.Str::ucfirst($word).'</li>')
            ->implode('');

        return $paragraphs
            .'<h2>'.Str::title($this->faker->words(3, true)).'</h2>'
            ."<ul>{$bullets}</ul>"
            .'<blockquote><p>'.$this->faker->sentence().'</p></blockquote>';
    }
}
