<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\User;
use App\Models\WomAchievement;
use App\Models\WomRecord;
use App\Services\Accounts\AdventurersLogCopy;

/**
 * An account's Wise Old Man data as its pages show it: the stored summary,
 * its personal records and its achievements. Reads our own tables only;
 * gains and charts come from {@see WomMetricGains}.
 */
class WomAccountView
{
    /**
     * The stored WOM row, or null when the account has never been looked up.
     * The owner's own choices (`tracking_opt_in`, `show_on_progress`) are only
     * included when `$viewer` owns the account.
     *
     * @return array{linked: bool, not_found: bool, status: ?string, type: ?string, build: ?string, exp: int, ehp: float, ehb: float, ttm: float, tt200m: float, combat_level: ?int, registered_at: ?string, synced_at: ?string, url: ?string, tracking_opt_in?: bool, show_on_progress?: bool}|null
     */
    public function summary(Account $account, ?User $viewer = null): ?array
    {
        $player = $account->womPlayer;

        if ($player === null) {
            return null;
        }

        $linked = $player->isLinked();

        return [
            'linked' => $linked,
            'not_found' => ! $linked && $player->not_found_at !== null,
            'status' => $player->status,
            'type' => $player->type,
            'build' => $player->build,
            'exp' => (int) $player->exp,
            'ehp' => (float) $player->ehp,
            'ehb' => (float) $player->ehb,
            'ttm' => (float) $player->ttm,
            'tt200m' => (float) $player->tt200m,
            'combat_level' => $player->combat_level,
            'registered_at' => $player->registered_at?->toIso8601String(),
            'synced_at' => $player->synced_at?->toIso8601String(),
            'url' => $linked ? self::profileUrl($account->username) : null,
            ...($viewer !== null && $viewer->id === $account->user_id ? [
                'tracking_opt_in' => $player->tracking_opt_in,
                'show_on_progress' => $player->show_on_progress,
            ] : []),
        ];
    }

    /** The public wiseoldman.net page for a username. */
    public static function profileUrl(string $username): string
    {
        return 'https://wiseoldman.net/players/'.rawurlencode(WiseOldManClient::standardise($username));
    }

    /**
     * The account's personal bests per metric, grouped by metric in catalogue
     * order (overall, skills, bosses, activities). EHP/EHB records are left out.
     *
     * @return list<array{metric: string, label: string, icon: array<string, string>|null, day?: array{value: int, achieved_at: string}, week?: array{value: int, achieved_at: string}, month?: array{value: int, achieved_at: string}, year?: array{value: int, achieved_at: string}}>
     */
    public function records(Account $account): array
    {
        $order = array_flip([...WomMetrics::SKILLS, ...WomMetrics::BOSSES, ...WomMetrics::ACTIVITIES]);

        return $account->womRecords()
            ->get(['metric', 'period', 'value', 'achieved_at'])
            ->filter(fn (WomRecord $record): bool => WomMetrics::isValid($record->metric))
            ->groupBy('metric')
            ->sortBy(fn ($records, string $metric): int => $order[$metric] ?? PHP_INT_MAX)
            ->map(fn ($records, string $metric): array => [
                'metric' => $metric,
                'label' => WomMetrics::label($metric),
                'icon' => WomMetrics::icon($metric),
                ...$records->mapWithKeys(fn (WomRecord $record): array => [$record->period => [
                    'value' => (int) round($record->value),
                    'achieved_at' => $record->achieved_at->toIso8601String(),
                ]])->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * The account's WOM achievements, newest first and undated ones last.
     * `approximate` marks a date WOM only knows to within more than a day.
     *
     * @return list<array{name: string, metric: string, measure: string, threshold: int, achieved_at: ?string, approximate: bool, legacy: bool, icon: array<string, string>|null}>
     */
    public function achievements(Account $account): array
    {
        return $account->womAchievements()
            ->orderByRaw('achieved_at is null')
            ->orderByDesc('achieved_at')
            ->orderBy('id')
            ->get()
            ->map(fn (WomAchievement $achievement): array => [
                'name' => $achievement->name,
                'metric' => $achievement->metric,
                'measure' => $achievement->measure,
                'threshold' => (int) $achievement->threshold,
                'achieved_at' => $achievement->achieved_at?->toIso8601String(),
                'approximate' => AdventurersLogCopy::isApproximate($achievement),
                'legacy' => (bool) $achievement->legacy,
                'icon' => WomMetrics::icon($achievement->metric),
            ])
            ->all();
    }
}
