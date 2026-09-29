<?php

namespace App\Services\WiseOldMan;

use Illuminate\Support\Str;

/**
 * Wise Old Man's metric catalogue, in WOM's own order and naming, and how
 * each metric maps onto a `WomDailySnapshot` field, a label and an icon.
 * A snapshot stores every key WOM sends, so a metric missing here still has
 * data; it just has no group until it is listed.
 */
class WomMetrics
{
    /** Skills (experience), `overall` first. */
    public const SKILLS = [
        'overall', 'attack', 'defence', 'strength', 'hitpoints', 'ranged', 'prayer', 'magic', 'cooking',
        'woodcutting', 'fletching', 'fishing', 'firemaking', 'crafting', 'smithing', 'mining', 'herblore',
        'agility', 'thieving', 'slayer', 'farming', 'runecrafting', 'hunter', 'construction', 'sailing',
    ];

    /** Bosses (kills). */
    public const BOSSES = [
        'abyssal_sire', 'alchemical_hydra', 'amoxliatl', 'araxxor', 'artio', 'barrows_chests', 'brutus',
        'bryophyta', 'callisto', 'calvarion', 'cerberus', 'chambers_of_xeric', 'chambers_of_xeric_challenge_mode',
        'chaos_elemental', 'chaos_fanatic', 'commander_zilyana', 'corporeal_beast', 'crazy_archaeologist',
        'dagannoth_prime', 'dagannoth_rex', 'dagannoth_supreme', 'deranged_archaeologist', 'doom_of_mokhaiotl',
        'duke_sucellus', 'general_graardor', 'giant_mole', 'grotesque_guardians', 'hespori', 'kalphite_queen',
        'king_black_dragon', 'kraken', 'kreearra', 'kril_tsutsaroth', 'lunar_chests', 'mad_angel', 'maggot_king',
        'mimic', 'nex', 'nightmare', 'phosanis_nightmare', 'obor', 'phantom_muspah', 'sarachnis', 'scorpia',
        'scurrius', 'shellbane_gryphon', 'skotizo', 'sol_heredit', 'spindel', 'tempoross', 'the_gauntlet',
        'the_corrupted_gauntlet', 'the_hueycoatl', 'the_leviathan', 'the_royal_titans', 'the_whisperer',
        'theatre_of_blood', 'theatre_of_blood_hard_mode', 'thermonuclear_smoke_devil', 'tombs_of_amascut',
        'tombs_of_amascut_expert', 'tzkal_zuk', 'tztok_jad', 'vardorvis', 'venenatis', 'vetion', 'vorkath',
        'wintertodt', 'yama', 'zalcano', 'zulrah',
    ];

    /** Activities (score): clues, minigames and the collection log. */
    public const ACTIVITIES = [
        'bounty_hunter_hunter', 'bounty_hunter_rogue', 'clue_scrolls_all', 'clue_scrolls_beginner',
        'clue_scrolls_easy', 'clue_scrolls_medium', 'clue_scrolls_hard', 'clue_scrolls_elite',
        'clue_scrolls_master', 'last_man_standing', 'pvp_arena', 'soul_wars_zeal', 'guardians_of_the_rift',
        'colosseum_glory', 'collections_logged',
    ];

    /** Labels that {@see Str::headline()} gets wrong. */
    private const LABELS = [
        'runecrafting' => 'Runecraft',
        'chambers_of_xeric_challenge_mode' => 'Chambers of Xeric: CM',
        'theatre_of_blood_hard_mode' => 'Theatre of Blood: HM',
        'tombs_of_amascut_expert' => 'Tombs of Amascut: Expert',
        'kreearra' => "Kree'Arra",
        'kril_tsutsaroth' => "K'ril Tsutsaroth",
        'vetion' => "Vet'ion",
        'calvarion' => "Calvar'ion",
        'phosanis_nightmare' => "Phosani's Nightmare",
        'tzkal_zuk' => 'TzKal-Zuk',
        'tztok_jad' => 'TzTok-Jad',
        'pvp_arena' => 'PvP Arena',
        'bounty_hunter_hunter' => 'Bounty Hunter (Hunter)',
        'bounty_hunter_rogue' => 'Bounty Hunter (Rogue)',
        'clue_scrolls_all' => 'Clue Scrolls (All)',
        'clue_scrolls_beginner' => 'Clue Scrolls (Beginner)',
        'clue_scrolls_easy' => 'Clue Scrolls (Easy)',
        'clue_scrolls_medium' => 'Clue Scrolls (Medium)',
        'clue_scrolls_hard' => 'Clue Scrolls (Hard)',
        'clue_scrolls_elite' => 'Clue Scrolls (Elite)',
        'clue_scrolls_master' => 'Clue Scrolls (Master)',
        'ehp' => 'EHP',
        'ehb' => 'EHB',
    ];

    /**
     * WOM metric => our slug, where they differ: skill slugs, and the keys
     * `HiscoresSync` derives from the official hiscore names.
     */
    private const LOCAL_SLUGS = [
        'runecrafting' => 'runecraft',
        'last_man_standing' => 'lms_rank',
        'pvp_arena' => 'pvp_arena_rank',
        'guardians_of_the_rift' => 'rifts_closed',
        'tombs_of_amascut_expert' => 'tombs_of_amascut_expert_mode',
    ];

    /** `/images/boss` files not named after the dashed metric. */
    private const IMAGE_FILES = [
        'tombs_of_amascut_expert' => 'tombs-of-amascut-expert-mode',
        'pvp_arena' => 'pvp-arena-rank',
        'guardians_of_the_rift' => 'rifts-closed',
    ];

    /** @var array<string, bool> */
    private static array $imageExists = [];

    /** @return 'skill'|'boss'|'activity'|null */
    public static function group(string $metric): ?string
    {
        return match (true) {
            in_array($metric, self::SKILLS, true) => 'skill',
            in_array($metric, self::BOSSES, true) => 'boss',
            in_array($metric, self::ACTIVITIES, true) => 'activity',
            default => null,
        };
    }

    public static function isValid(string $metric): bool
    {
        return self::group($metric) !== null;
    }

    /**
     * The metric's field on a `WomDailySnapshot` document: `overall` is the
     * headline `exp`, the rest live in the `skills`/`bosses`/`activities` maps.
     */
    public static function path(string $metric): string
    {
        return match (self::group($metric)) {
            'skill' => $metric === 'overall' ? 'exp' : "skills.{$metric}",
            'boss' => "bosses.{$metric}",
            'activity' => "activities.{$metric}",
            default => throw new \InvalidArgumentException("Unknown Wise Old Man metric [{$metric}]."),
        };
    }

    public static function label(string $metric): string
    {
        if (isset(self::LABELS[$metric])) {
            return self::LABELS[$metric];
        }

        return preg_replace_callback(
            '/(?<!^)\b(Of|The|And)\b/',
            fn (array $match): string => strtolower($match[0]),
            Str::headline($metric),
        );
    }

    /** Our slug for a WOM metric (skills and hiscores say `runecraft`). */
    public static function localSlug(string $metric): string
    {
        return self::LOCAL_SLUGS[$metric] ?? $metric;
    }

    /** The WOM metric for one of our slugs; the inverse of {@see localSlug()}. */
    public static function fromLocalSlug(string $slug): string
    {
        return array_flip(self::LOCAL_SLUGS)[$slug] ?? $slug;
    }

    /**
     * A WOM snapshot's `data` as the maps a `WomDailySnapshot` stores: every
     * key WOM sent (so a new boss needs no code change), skills without
     * `overall` (that is the headline `exp`), and unranked (-1) values left out.
     *
     * @param  array<string, mixed>  $data
     * @return array{skills: array<string, int>, bosses: array<string, int>, activities: array<string, int>}
     */
    public static function snapshotMaps(array $data): array
    {
        $map = function (string $group, string $field) use ($data): array {
            $values = [];

            foreach ((array) ($data[$group] ?? []) as $metric => $entry) {
                $value = (int) ($entry[$field] ?? -1);

                if ($metric !== 'overall' && $value >= 0) {
                    $values[$metric] = $value;
                }
            }

            return $values;
        };

        return [
            'skills' => $map('skills', 'experience'),
            'bosses' => $map('bosses', 'kills'),
            'activities' => $map('activities', 'score'),
        ];
    }

    /**
     * A skill's pack sprite for `packIcon()`, or a plain image for a boss,
     * clue tier or minigame. Null when there is no picture.
     *
     * @return array{kind: string, slug: string}|array{src: string}|null
     */
    public static function icon(string $metric): ?array
    {
        if (self::group($metric) === 'skill') {
            return ['kind' => 'skill', 'slug' => self::localSlug($metric)];
        }

        if (str_starts_with($metric, 'clue_scrolls_')) {
            return ['src' => '/images/clue/'.substr($metric, strlen('clue_scrolls_')).'-treasure-trails.png'];
        }

        if (self::group($metric) === null) {
            return null;
        }

        $src = '/images/boss/'.(self::IMAGE_FILES[$metric] ?? str_replace('_', '-', $metric)).'.png';

        return (self::$imageExists[$src] ??= is_file(public_path($src))) ? ['src' => $src] : null;
    }
}
