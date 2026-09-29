<?php

namespace App\Services\Accounts;

use Carbon\CarbonImmutable;

/**
 * One row of an account's Adventurer's Log, from any of its three sources:
 * a milestone feed event, a Wise Old Man achievement or a (collapsed) WOM
 * personal record. `rank` and `key` place it in the log's order
 * (occurred_at desc, rank asc, key desc) and make up the page cursor.
 */
readonly class RecentEvent
{
    public const SOURCE_FEED = 'feed';

    public const SOURCE_ACHIEVEMENT = 'wom_achievement';

    public const SOURCE_RECORD = 'wom_record';

    /** Tie-break between sources that share a timestamp. */
    public const RANKS = [self::SOURCE_FEED => 0, self::SOURCE_ACHIEVEMENT => 1, self::SOURCE_RECORD => 2];

    /** Id prefix per source. */
    private const PREFIXES = [self::SOURCE_FEED => 'feed', self::SOURCE_ACHIEVEMENT => 'ach', self::SOURCE_RECORD => 'rec'];

    /**
     * @param  array{kind: string, slug: string}|array{src: string}|null  $icon
     * @param  array{x: int, y: int, plane: int, location: ?string}|null  $position  Where a feed event happened.
     * @param  ?bool  $screenshotHidden  Whether the screenshot is hidden; null unless the viewer owns the account or is an admin.
     */
    public function __construct(
        public string $source,
        public int $key,
        public string $type,
        public string $title,
        public string $body,
        public CarbonImmutable $occurredAt,
        public bool $approximate = false,
        public ?array $icon = null,
        public ?int $feedEventId = null,
        public ?bool $pinned = null,
        public ?string $screenshotUrl = null,
        public ?array $position = null,
        public ?bool $screenshotHidden = null,
    ) {}

    /** `feed:123`, `ach:45`, `rec:67`. */
    public function id(): string
    {
        return self::PREFIXES[$this->source].':'.$this->key;
    }

    public function rank(): int
    {
        return self::RANKS[$this->source];
    }

    /**
     * Whether this event comes after the position (in log order).
     */
    public function isAfter(int $timestampMs, int $rank, int $key): bool
    {
        $ms = $this->occurredAt->getTimestampMs();

        return $ms !== $timestampMs
            ? $ms < $timestampMs
            : ($this->rank() !== $rank ? $this->rank() > $rank : $this->key < $key);
    }

    /**
     * Log order: newest first, then source rank, then key descending.
     */
    public static function compare(self $a, self $b): int
    {
        return [$b->occurredAt->getTimestampMs(), $a->rank(), $b->key]
            <=> [$a->occurredAt->getTimestampMs(), $b->rank(), $a->key];
    }
}
