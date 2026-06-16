<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A quest snapshot — [name, status] pairs (status 0 = not started, 1 = in
 * progress, 2 = finished), matching the plugin push shape.
 *
 * @extends Factory<Quest>
 */
class QuestFactory extends Factory
{
    private const QUESTS = [
        "Cook's Assistant", 'Dragon Slayer I', 'Dragon Slayer II', 'Monkey Madness I',
        'Monkey Madness II', 'Recipe for Disaster', 'Desert Treasure I', 'Desert Treasure II',
        'Song of the Elves', 'Sins of the Father', 'A Night at the Theatre', 'The Fremennik Trials',
        'Lunar Diplomacy', 'Dream Mentor', 'Animal Magnetism', 'Legends Quest', 'Mournings End Part II',
        'Making History', 'Fairytale I', 'Fairytale II', "Witch's House", 'Tree Gnome Village',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quests = array_map(
            fn (string $name) => [$name, $this->faker->randomElement([0, 1, 2, 2, 2])],
            self::QUESTS,
        );

        return [
            'account_id' => Account::factory(),
            'quests' => $quests,
        ];
    }
}
