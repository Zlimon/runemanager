<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\FeedEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SPEC §8 live-feed events. Defaults to a level-up; use the type states for the
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
                'items' => [['id' => 11286, 'quantity' => 1]], // Draconic visage placeholder
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
}
