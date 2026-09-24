<?php

namespace Database\Factories;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Name stems for generated usernames. All are 7 characters or shorter so
     * that stem + space + a four-digit sequence still fits OSRS's 12-character
     * limit, and all are plain letters so `AccountUsernameRule` accepts them.
     *
     * @var list<string>
     */
    private const USERNAME_STEMS = [
        'Zamorak', 'Guthix', 'Vorkath', 'Zulrah', 'Kraken', 'Barrows', 'Dragon',
        'Rune', 'Adamant', 'Mithril', 'Slayer', 'Prayer', 'Ranger', 'Wizard',
        'Warrior', 'Paladin', 'Knight', 'Archer', 'Hunter', 'Farmer', 'Miner',
        'Fisher', 'Crafter', 'Thiever', 'Scaper', 'Ironman', 'Maxed', 'Looter',
        'Grinder', 'Bossman', 'Pilgrim', 'Nomad', 'Templar', 'Cleric', 'Druid',
    ];

    /**
     * Places a player plausibly is, with the activity that happens there and
     * the real OSRS surface coordinates of the spot, so the Live Map and the
     * profile minimap put people somewhere that exists. `icon` is a skill slug
     * (`packIcon('skill', …)`) or null where no skill fits the activity.
     *
     * @var list<array{activity: string, icon: ?string, location: string, x: int, y: int}>
     */
    private const SPOTS = [
        ['activity' => 'Banking', 'icon' => null, 'location' => 'Grand Exchange', 'x' => 3165, 'y' => 3489],
        ['activity' => 'Runecraft', 'icon' => 'runecraft', 'location' => 'Varrock', 'x' => 3185, 'y' => 3436],
        ['activity' => 'Cooking', 'icon' => 'cooking', 'location' => 'Lumbridge', 'x' => 3222, 'y' => 3218],
        ['activity' => 'Mining', 'icon' => 'mining', 'location' => 'Falador', 'x' => 3013, 'y' => 3355],
        ['activity' => 'Slayer', 'icon' => 'slayer', 'location' => 'Edgeville', 'x' => 3094, 'y' => 3491],
        ['activity' => 'Fishing', 'icon' => 'fishing', 'location' => 'Catherby', 'x' => 2809, 'y' => 3441],
        ['activity' => 'Agility', 'icon' => 'agility', 'location' => "Seers' Village", 'x' => 2725, 'y' => 3493],
        ['activity' => 'Thieving', 'icon' => 'thieving', 'location' => 'Ardougne Market', 'x' => 2662, 'y' => 3305],
        ['activity' => 'Woodcutting', 'icon' => 'woodcutting', 'location' => 'Woodcutting Guild', 'x' => 1591, 'y' => 3487],
        ['activity' => 'Firemaking', 'icon' => 'firemaking', 'location' => 'Wintertodt', 'x' => 1630, 'y' => 3962],
        ['activity' => 'Fighting Zulrah', 'icon' => 'attack', 'location' => 'Zul-Andra', 'x' => 2199, 'y' => 3056],
    ];

    /**
     * Member world numbers to spread accounts over.
     *
     * @var list<int>
     */
    private const WORLDS = [302, 330, 353, 416, 450, 469, 525];

    /**
     * Next username sequence number, seeded from the rows already in the
     * database the first time a username is generated. Faker's own `unique()`
     * cannot be used here: it dedupes before the value is truncated to the
     * column width, so two long names can still collide afterwards.
     */
    private static ?int $usernameSequence = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_map(fn ($case) => $case->value, AccountTypesEnum::cases());

        return [
            'user_id' => User::factory(),
            // accounts.account_hash has a unique constraint; a short random
            // marker keeps factory rows from colliding when many are created.
            'account_hash' => 'fac-'.Str::lower(Str::random(20)),
            'account_type' => $this->faker->randomElement($types),
            'username' => $this->generateUsername(),
            'rank' => $this->faker->numberBetween(1, 2_000_000),
            'level' => $this->faker->numberBetween(32, 2277),
            'xp' => $this->faker->numberBetween(0, 200_000_000),
            // Presence is derived from last_seen_at: a recent stamp reads as
            // online, an old one as offline.
            'last_seen_at' => $this->faker->boolean()
                ? now()->subSeconds($this->faker->numberBetween(0, 120))
                : now()->subHours($this->faker->numberBetween(1, 48)),
        ];
    }

    /**
     * Presence for an account whose plugin is still pinging. `last_seen_at`
     * alone: online status is derived from it, and nothing else about a
     * heartbeat is stored.
     */
    public function online(): static
    {
        return $this->state(fn () => [
            'last_seen_at' => now()->subSeconds($this->faker->numberBetween(0, 60)),
        ]);
    }

    /**
     * The plugin's status push — what the player is doing, where, and on which
     * world — without position sharing. Drives the activity line on account
     * cards and the profile header.
     *
     * {@see self::onMap()} sets these too, from the spot it places the player
     * at, so the two states are alternatives rather than a pair to chain.
     */
    public function busy(): static
    {
        return $this->state(fn () => $this->activityAt($this->faker->randomElement(self::SPOTS)));
    }

    /**
     * A player sharing their position: on the Live Map and the profile minimap,
     * doing whatever the spot is for. Visibility expires with
     * `position_updated_at` (see `runemanager.map.visible_within_minutes`), so
     * the stamp is deliberately fresh.
     */
    public function onMap(): static
    {
        return $this->state(function (): array {
            $spot = $this->faker->randomElement(self::SPOTS);

            return [
                ...$this->activityAt($spot),
                'world_x' => $spot['x'],
                'world_y' => $spot['y'],
                'world_plane' => 0,
                'position_updated_at' => now()->subSeconds($this->faker->numberBetween(0, 45)),
            ];
        });
    }

    /**
     * Status-orb values, in the shape the plugin pushes them: current/max
     * hitpoints and prayer, run energy and special attack as percentages.
     * `vitalsPayload()` returns null until `vitals_updated_at` is set, so the
     * orbs only appear once all six have been pushed together.
     */
    public function withVitals(): static
    {
        $hitpointsMax = $this->faker->numberBetween(70, 99);
        $prayerMax = $this->faker->numberBetween(43, 99);

        return $this->state(fn () => [
            'hitpoints' => $this->faker->numberBetween((int) ($hitpointsMax * 0.4), $hitpointsMax),
            'hitpoints_max' => $hitpointsMax,
            'prayer' => $this->faker->numberBetween(0, $prayerMax),
            'prayer_max' => $prayerMax,
            'run_energy' => $this->faker->numberBetween(0, 100),
            'special_attack' => $this->faker->randomElement([0, 25, 50, 75, 100]),
            'vitals_updated_at' => now()->subSeconds($this->faker->numberBetween(0, 45)),
        ]);
    }

    /**
     * The status-push columns for one spot.
     *
     * @param  array{activity: string, icon: ?string, location: string, x: int, y: int}  $spot
     * @return array<string, mixed>
     */
    private function activityAt(array $spot): array
    {
        return [
            'activity' => $spot['activity'],
            'activity_icon' => $spot['icon'],
            'location' => $spot['location'],
            'world_number' => $this->faker->randomElement(self::WORLDS),
        ];
    }

    /**
     * A username a real OSRS player could have: at most 12 characters, letters,
     * digits and single interior spaces only. The sequence number guarantees
     * uniqueness against `accounts.username`, and starts past the rows already
     * present so that re-running a seeder tops up instead of colliding.
     */
    private function generateUsername(): string
    {
        if (self::$usernameSequence === null) {
            self::$usernameSequence = Account::query()->count();
        }

        $sequence = ++self::$usernameSequence;
        $stem = self::USERNAME_STEMS[($sequence - 1) % count(self::USERNAME_STEMS)];

        return $stem.' '.$sequence;
    }
}
