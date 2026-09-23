<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\FeedEvent;
use Database\Seeders\Support\ItemPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * Live-feed events. Defaults to a level-up; use the type states for the
 * others. Item-bearing loot drops are best built with real item ids — see
 * DemoContentSeeder, which hydrates loot from the live feed it generates.
 *
 * @extends Factory<FeedEvent>
 */
class FeedEventFactory extends Factory
{
    private const SKILLS = ['attack', 'slayer', 'farming', 'runecraft', 'agility', 'fishing', 'mining'];

    private const SOURCES = ['Zulrah', 'Vorkath', 'Chambers of Xeric', 'Theatre of Blood', 'Cerberus', 'Kraken'];

    private const QUESTS = ['Dragon Slayer II', 'Monkey Madness II', 'Song of the Elves', 'Sins of the Father'];

    private const CA_TASKS = ["Ghommal's Hilt 6", 'Peach Conjurer', 'Whack-a-Mole', 'A Frozen Foe'];

    private const CA_TIERS = ['easy', 'medium', 'hard', 'elite', 'master', 'grandmaster'];

    /** Draconic visage — stands in for a drop when the item catalogue is empty. */
    private const PLACEHOLDER_ITEM_ID = 11286;

    /** Bundled image copied onto the public disk to stand in for a plugin screenshot. */
    private const SCREENSHOT_SOURCE = 'images/newspost_default.png';

    /** Where that copy lives, alongside the screenshots the plugin uploads. */
    private const SCREENSHOT_PATH = 'feed-screenshots/demo.png';

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = $this->faker->numberBetween(50, 99);

        return [
            'account_id' => Account::factory(),
            'type' => FeedEvent::TYPE_LEVEL_UP,
            'payload' => [
                'skill' => $this->faker->randomElement(self::SKILLS),
                'level' => $level,
                'milestone' => $level,
            ],
            'occurred_at' => now()->subMinutes($this->faker->numberBetween(0, 20_000)),
        ];
    }

    public function lootDrop(): static
    {
        return $this->state(fn () => [
            'type' => FeedEvent::TYPE_LOOT_DROP,
            'payload' => [
                'source' => $this->faker->randomElement(self::SOURCES),
                'items' => [[
                    'id' => ItemPool::id($this->faker) ?: self::PLACEHOLDER_ITEM_ID,
                    'quantity' => $this->faker->numberBetween(1, 5),
                ]],
                'total_value' => $this->faker->numberBetween(100_000, 5_000_000),
            ],
        ]);
    }

    public function questComplete(): static
    {
        return $this->state(fn () => [
            'type' => FeedEvent::TYPE_QUEST_COMPLETE,
            'payload' => ['quest' => $this->faker->randomElement(self::QUESTS)],
        ]);
    }

    public function combatAchievement(): static
    {
        return $this->state(fn () => [
            'type' => FeedEvent::TYPE_COMBAT_ACHIEVEMENT,
            'payload' => [
                'task' => $this->faker->randomElement(self::CA_TASKS),
                'tier' => $this->faker->randomElement(self::CA_TIERS),
            ],
        ]);
    }

    public function collectionLog(): static
    {
        return $this->state(fn () => [
            'type' => FeedEvent::TYPE_COLLECTION_LOG,
            'payload' => ['item' => $this->faker->randomElement(['Twisted bow', 'Tumeken\'s shadow', 'Pet snakeling', 'Dragon warhammer'])],
        ]);
    }

    public function pet(): static
    {
        return $this->state(fn () => ['type' => FeedEvent::TYPE_PET, 'payload' => []]);
    }

    public function death(): static
    {
        return $this->state(fn () => ['type' => FeedEvent::TYPE_DEATH, 'payload' => []]);
    }

    public function reward(): static
    {
        return $this->state(fn () => [
            'type' => FeedEvent::TYPE_REWARD,
            'payload' => ['source' => $this->faker->randomElement(['Barrows', 'Chambers of Xeric', 'Clue scroll'])],
        ]);
    }

    /**
     * Kept by its owner in the account's achievement gallery. The gallery is
     * built around the screenshot, so pin something that has one.
     */
    public function pinned(): static
    {
        return $this->state(fn () => [
            'pinned_at' => now()->subDays($this->faker->numberBetween(0, 30)),
        ]);
    }

    /**
     * A screenshot of the moment, as the plugin would have uploaded one.
     */
    public function withScreenshot(): static
    {
        return $this->state(fn () => ['screenshot_path' => self::demoScreenshotPath()]);
    }

    /**
     * The demo screenshot's path on the public disk, copying the bundled image
     * there on first use.
     *
     * Returns null when the bundled image is missing: FeedEventResource turns a
     * stored path into a URL without checking the file, so a path pointing at
     * nothing renders as a broken image in the feed and the gallery.
     */
    private static function demoScreenshotPath(): ?string
    {
        $disk = Storage::disk('public');

        if ($disk->exists(self::SCREENSHOT_PATH)) {
            return self::SCREENSHOT_PATH;
        }

        $source = public_path(self::SCREENSHOT_SOURCE);

        if (! is_file($source)) {
            return null;
        }

        return $disk->put(self::SCREENSHOT_PATH, (string) file_get_contents($source))
            ? self::SCREENSHOT_PATH
            : null;
    }
}
