<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\FeedEvent;
use App\Services\Screenshots\ScreenshotStorage;
use Database\Seeders\Support\ItemPool;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    /**
     * Where a loot or reward source is fought or opened: [x, y, plane, location].
     * Coordinates are OSRS world tiles, as the plugin reports them.
     */
    private const SOURCE_POSITIONS = [
        'Zulrah' => [2268, 3070, 0, 'Zul-Andra'],
        'Vorkath' => [2272, 4054, 0, 'Ungael'],
        'Chambers of Xeric' => [1233, 3565, 0, 'Mount Quidamortem'],
        'Theatre of Blood' => [3676, 3219, 0, 'Ver Sinhaza'],
        'Cerberus' => [1310, 1250, 0, "Cerberus' Lair"],
        'Kraken' => [2280, 10022, 0, 'Kraken Cove'],
        'Barrows' => [3565, 3289, 0, 'Barrows'],
        'Clue scroll' => [3164, 3487, 0, 'Grand Exchange'],
    ];

    /** Where a demo quest ends. */
    private const QUEST_POSITIONS = [
        'Dragon Slayer II' => [2458, 2851, 0, "Myths' Guild"],
        'Monkey Madness II' => [2764, 2775, 0, 'Ape Atoll'],
        'Song of the Elves' => [2330, 3172, 0, 'Lletya'],
        'Sins of the Father' => [3624, 3364, 0, 'Darkmeyer'],
    ];

    /**
     * Everyday spots for events that are not tied to one place (level-ups,
     * combat tasks, pets, deaths). Lumbridge Castle's bank is upstairs, so the
     * map's plane handling has something to show.
     */
    private const COMMON_POSITIONS = [
        [3222, 3218, 0, 'Lumbridge'],
        [3208, 3220, 2, 'Lumbridge Castle'],
        [3165, 3487, 0, 'Grand Exchange'],
        [3213, 3424, 0, 'Varrock'],
        [2965, 3380, 0, 'Falador'],
        [2662, 3305, 0, 'East Ardougne'],
        [2837, 3434, 0, 'Catherby'],
        [1639, 3673, 0, 'Kourend Castle'],
        [3093, 3690, 0, 'Wilderness'],
    ];

    /** Draconic visage — stands in for a drop when the item catalogue is empty. */
    private const PLACEHOLDER_ITEM_ID = 11286;

    /** Bundled image copied into screenshot storage to stand in for a plugin screenshot. */
    public const SCREENSHOT_SOURCE = 'images/newspost_default.png';

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
     * Where it happened, as the plugin attaches it: at the drop's boss or
     * chest, at the quest's end, or at an everyday spot for the rest.
     */
    public function positioned(): static
    {
        return $this->afterMaking(function (FeedEvent $event): void {
            $payload = is_array($event->payload) ? $event->payload : [];

            $event->payload = [...$payload, 'position' => self::positionFor((string) $event->type, $payload, $this->faker)];
        });
    }

    /**
     * A realistic position for an event of this type and payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array{x: int, y: int, plane: int, location: string}
     */
    public static function positionFor(string $type, array $payload, Generator $faker): array
    {
        $key = match ($type) {
            FeedEvent::TYPE_QUEST_COMPLETE => $payload['quest'] ?? null,
            FeedEvent::TYPE_LOOT_DROP, FeedEvent::TYPE_REWARD => $payload['source'] ?? null,
            default => null,
        };

        [$x, $y, $plane, $location] = self::QUEST_POSITIONS[$key] ?? self::SOURCE_POSITIONS[$key]
            ?? $faker->randomElement(self::COMMON_POSITIONS);

        return ['x' => $x, 'y' => $y, 'plane' => $plane, 'location' => $location];
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
     * A screenshot of the moment, as the plugin would have uploaded one: its
     * own copy of the bundled image on the private disk, so deleting one
     * event's screenshot never takes another's.
     */
    public function withScreenshot(): static
    {
        return $this->state(fn () => ['screenshot_path' => self::demoScreenshotPath()]);
    }

    /**
     * A fresh copy of the bundled image in screenshot storage, or null when the
     * image is missing (a path pointing at nothing would render as a broken
     * image in the feed and the gallery).
     */
    private static function demoScreenshotPath(): ?string
    {
        $source = public_path(self::SCREENSHOT_SOURCE);

        return is_file($source)
            ? app(ScreenshotStorage::class)->put((string) file_get_contents($source), 'png')
            : null;
    }
}
