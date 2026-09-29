<?php

namespace App\Services\Accounts;

use App\Models\FeedEvent;
use App\Models\WomAchievement;
use App\Services\WiseOldMan\WomMetrics;
use Illuminate\Support\Str;

/**
 * The first-person sentences of the Adventurer's Log, in the voice of the old
 * RuneScape log ("I've reached level 99 in the Cooking skill."). Every event
 * gets a short title and a body; the page never builds copy itself.
 */
class AdventurersLogCopy
{
    /** WOM's experience threshold for level 99. */
    public const LEVEL_99_EXPERIENCE = 13_034_431;

    /** An achievement date WOM is less sure of than this (ms) reads "around". */
    public const APPROXIMATE_AFTER_MS = 86_400_000;

    /** Record periods, shortest first. */
    private const PERIOD_ORDER = ['day', 'week', 'month', 'year'];

    private const PERIOD_ADJECTIVES = ['day' => 'daily', 'week' => 'weekly', 'month' => 'monthly', 'year' => 'yearly'];

    /**
     * @param  ?string  $topItem  The name of the drop's most valuable item, when known.
     * @return array{title: string, body: string}
     */
    public static function forFeedEvent(FeedEvent $event, ?string $topItem = null): array
    {
        $payload = is_array($event->payload) ? $event->payload : [];

        return match ($event->type) {
            FeedEvent::TYPE_LEVEL_UP => self::levelUp(self::text($payload, 'skill'), (int) ($payload['level'] ?? 0)),
            FeedEvent::TYPE_QUEST_COMPLETE => self::questComplete(self::text($payload, 'quest')),
            FeedEvent::TYPE_COMBAT_ACHIEVEMENT => self::combatTask(self::text($payload, 'task'), self::text($payload, 'tier')),
            FeedEvent::TYPE_COLLECTION_LOG => [
                'title' => 'New collection log item.',
                'body' => 'I added '.(self::text($payload, 'item') ?? 'an item').' to my collection log.',
            ],
            FeedEvent::TYPE_PET => [
                'title' => 'Found a pet!',
                'body' => "I have a funny feeling I'm being followed.",
            ],
            FeedEvent::TYPE_LOOT_DROP => self::lootDrop($payload, $topItem),
            default => [
                'title' => Str::headline((string) $event->type).'.',
                'body' => '',
            ],
        };
    }

    /**
     * @return array{title: string, body: string}
     */
    public static function forAchievement(WomAchievement $achievement): array
    {
        $label = WomMetrics::label($achievement->metric);
        $threshold = (int) $achievement->threshold;

        $body = match ($achievement->measure) {
            'experience' => match (true) {
                $achievement->metric === 'overall' => 'I\'ve reached '.self::shortNumber($threshold).' total experience.',
                $threshold === self::LEVEL_99_EXPERIENCE => "I've reached level 99 in the {$label} skill.",
                default => 'I\'ve reached '.self::shortNumber($threshold)." experience in {$label}.",
            },
            'kills' => "I've killed {$label} ".number_format($threshold).' times.',
            'score' => "I've reached ".number_format($threshold)." in {$label}.",
            default => $achievement->name === 'Maxed Overall'
                ? "I've reached level 99 in every skill."
                : "I've reached {$achievement->name}.",
        };

        if (self::isApproximate($achievement) && $achievement->achieved_at !== null) {
            $body = 'Around '.$achievement->achieved_at->format('j F Y').', '.$body;
        }

        return ['title' => $achievement->name, 'body' => $body];
    }

    /**
     * One personal best, possibly set over several periods by the same WOM
     * update. The sentence gives the shortest period's value; the longer
     * periods are mentioned as further bests ("also a new monthly best").
     *
     * @param  array<string, int|float>  $valuesByPeriod  Record value keyed by period (`day`…`year`).
     * @return array{title: string, body: string}
     */
    public static function forRecord(string $metric, array $valuesByPeriod): array
    {
        $label = WomMetrics::label($metric);
        $periods = array_values(array_intersect(self::PERIOD_ORDER, array_keys($valuesByPeriod)));
        $period = array_shift($periods) ?? 'period';
        $amount = number_format((int) round((float) ($valuesByPeriod[$period] ?? 0)));
        $also = $periods === []
            ? ''
            : ' (also a new '.self::joinList(array_map(fn (string $period): string => self::PERIOD_ADJECTIVES[$period], $periods)).' best)';
        $single = "in a single {$period}, my best yet{$also}.";

        $body = match (WomMetrics::group($metric)) {
            'boss' => "I killed {$label} {$amount} times {$single}",
            'activity' => "I scored {$amount} in {$label} {$single}",
            default => $metric === 'overall'
                ? "I gained {$amount} experience {$single}"
                : "I gained {$amount} {$label} experience {$single}",
        };

        return ['title' => "New personal best: {$label}.", 'body' => $body];
    }

    public static function isApproximate(WomAchievement $achievement): bool
    {
        return (int) $achievement->accuracy > self::APPROXIMATE_AFTER_MS;
    }

    /**
     * The skill's display label from the plugin's skill name ("Runecraft",
     * "hunter").
     */
    public static function skillLabel(string $skill): string
    {
        return WomMetrics::label(self::skillMetric($skill));
    }

    /** The WOM metric for a plugin skill name ("Runecraft" → `runecrafting`). */
    public static function skillMetric(string $skill): string
    {
        return WomMetrics::fromLocalSlug(Str::lower(trim($skill)));
    }

    /**
     * @return array{title: string, body: string}
     */
    private static function levelUp(?string $skill, int $level): array
    {
        if ($skill === null) {
            return ['title' => 'Levelled up.', 'body' => "I've reached level {$level} in a skill."];
        }

        $label = self::skillLabel($skill);

        if ($level === 99) {
            return [
                'title' => "Mastered {$label}.",
                'body' => "I've reached level 99 in the {$label} skill, the highest level possible.",
            ];
        }

        return [
            'title' => "Levelled up {$label}.",
            'body' => "I've reached level {$level} in the {$label} skill.",
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{title: string, body: string}
     */
    private static function lootDrop(array $payload, ?string $topItem): array
    {
        $source = self::text($payload, 'source') ?? 'a monster';
        $value = number_format((int) ($payload['total_value'] ?? 0));

        if ($topItem === null || $topItem === '') {
            return [
                'title' => "Received loot from {$source}.",
                'body' => "I received loot worth {$value} coins from {$source}.",
            ];
        }

        $others = max(0, count(is_array($payload['items'] ?? null) ? $payload['items'] : []) - 1);
        $more = $others > 0 ? " (+{$others} more)" : '';

        return [
            'title' => "Found {$topItem}.",
            'body' => "I received {$topItem}{$more} worth {$value} coins from {$source}.",
        ];
    }

    /**
     * "a", "a and b", "a, b and c".
     *
     * @param  list<string>  $items
     */
    private static function joinList(array $items): string
    {
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }

        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }

    /**
     * @return array{title: string, body: string}
     */
    private static function questComplete(?string $quest): array
    {
        if ($quest === null) {
            return ['title' => 'Quest complete.', 'body' => 'I completed a quest.'];
        }

        return ['title' => "Quest complete: {$quest}.", 'body' => "I completed the quest {$quest}."];
    }

    /**
     * @return array{title: string, body: string}
     */
    private static function combatTask(?string $task, ?string $tier): array
    {
        $tier = $tier !== null ? ucfirst($tier).' ' : '';

        return [
            'title' => 'Combat task complete.',
            'body' => $task !== null
                ? "I completed the {$tier}combat task '{$task}'."
                : 'I completed '.(preg_match('/^[AEIOU]/', $tier) ? 'an' : 'a')." {$tier}combat task.",
        ];
    }

    /**
     * A payload string, or null when it is missing, blank or not a string, so
     * one malformed row cannot break the whole log.
     *
     * @param  array<string, mixed>  $payload
     */
    private static function text(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    /** 13034431 → "13.03m", 50000000 → "50m", 1000000000 → "1b". */
    private static function shortNumber(int $value): string
    {
        foreach ([1_000_000_000 => 'b', 1_000_000 => 'm', 1_000 => 'k'] as $unit => $suffix) {
            if ($value >= $unit) {
                return rtrim(rtrim(number_format($value / $unit, 2, '.', ''), '0'), '.').$suffix;
            }
        }

        return (string) $value;
    }
}
