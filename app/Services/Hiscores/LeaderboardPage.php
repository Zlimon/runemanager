<?php

namespace App\Services\Hiscores;

use App\Http\Requests\FilterHiscoresRequest;
use App\Models\Account;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * Cuts one page out of a hiscore board that is already ranked in full.
 *
 * Every board is assembled as a plain PHP array — the rows come from jsonb
 * maps and Mongo aggregations, not from a query that could be ordered and
 * limited in SQL — so the whole board is ranked first and only then narrowed,
 * reordered and sliced. That order is the point: a row keeps the standing it
 * earned against every account in the instance, whichever page it lands on,
 * whatever the username filter is, and whichever column it is sorted by.
 *
 * The board's own query state is what decides all three, so this reads the
 * request, as the paginator it builds already does for the current page.
 */
class LeaderboardPage
{
    /** Rows per page, shared by every hiscore board. */
    public const PER_PAGE = 25;

    /**
     * @param  array<int, array<string, mixed>>  $rows  the whole board, already ranked
     * @return AnonymousResourceCollection Passed to Inertia unresolved, for `data`/`links`/`meta`.
     */
    public function of(array $rows, FilterHiscoresRequest $filters): AnonymousResourceCollection
    {
        $matches = $this->sorted(
            $this->matching($rows, $filters->username()),
            $filters->sort(),
            $filters->direction(),
        );

        $page = Paginator::resolveCurrentPage();
        $perPage = $filters->perPage();

        $paginator = new LengthAwarePaginator(
            array_slice($matches, ($page - 1) * $perPage, $perPage),
            count($matches),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );

        // Carries the search and the sort onto every page link. `page` itself
        // is excluded, so a link never ends up with two of them.
        return JsonResource::collection($paginator->withQueryString());
    }

    /**
     * The final tie-break every board shares: username, ascending and
     * case-insensitively.
     *
     * Without it a board has no total order. Rows tying on every metric would
     * keep whatever order the database happened to return them in, and that
     * order is not stable between requests — Postgres reads an unordered
     * `get()` straight off the heap, and `hiscores:sync` rewrites those rows
     * continuously. Page 1 and page 2 are separate requests, so a board with
     * no tie-break could show a row on both pages and omit another entirely,
     * and a stamped rank could move under the reader between page loads. A
     * fresh instance, where most accounts tie on 0, is the worst case.
     *
     * SQLite returns a stable order for the same rows, so the test suite does
     * not reproduce this on its own — {@see tests/Feature/HiscoreTieBreakTest}
     * pins it by seeding a tied board in reverse alphabetical order.
     *
     * @param  array{account: array<string, mixed>}  $a
     * @param  array{account: array<string, mixed>}  $b
     */
    public static function compareByUsername(array $a, array $b): int
    {
        return strcasecmp($a['account']['username'] ?? '', $b['account']['username'] ?? '');
    }

    /**
     * Orders a board by one metric, best first, breaks ties by username, and
     * stamps each row with its standing. This is for the boards built from our
     * own data, whose rows carry no rank of their own; the OSRS-sourced boards
     * bring both their order and their rank from the hiscores.
     *
     * It runs on the whole board, before {@see of()} narrows or slices it, so
     * the number a row shows is its place among every account rather than its
     * offset within a page or a search.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public function rankedBy(array $rows, string $metric): array
    {
        usort($rows, fn (array $a, array $b): int => ($b[$metric] <=> $a[$metric])
            ?: self::compareByUsername($a, $b));

        return array_map(
            fn (array $row, int $index): array => [...$row, 'rank' => $index + 1],
            $rows,
            array_keys($rows),
        );
    }

    /**
     * The rows whose account username contains $username, case-insensitively.
     * This is the PHP form of {@see Account::scopeSearchUsername()}, so a term
     * finds the same players on a board as it does on the account index; a
     * blank term leaves the board whole.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function matching(array $rows, ?string $username): array
    {
        $term = Str::lower(trim((string) $username));

        if ($term === '') {
            return array_values($rows);
        }

        return array_values(array_filter(
            $rows,
            fn (array $row): bool => str_contains(Str::lower($row['account']['username'] ?? ''), $term),
        ));
    }

    /**
     * The board reordered by one column, or exactly as it came when no column
     * is asked for. Only the order moves: a row's `rank` is the standing it
     * earned in the canonical order and is never recomputed here, so sorting
     * by another column shows the same player the same rank.
     *
     * PHP's sort is stable, so rows tying on the column keep the canonical
     * order between them — which is why sorting by `rank` ascending reproduces
     * the board's default order, tie-breaks and all.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function sorted(array $rows, ?string $sort, ?string $direction): array
    {
        if ($sort === null) {
            return $rows;
        }

        $descending = $direction === 'desc';

        usort($rows, function (array $a, array $b) use ($sort, $descending): int {
            $comparison = $this->sortValue($a, $sort) <=> $this->sortValue($b, $sort);

            return $descending ? -$comparison : $comparison;
        });

        return $rows;
    }

    /**
     * The value one row sorts by. An unranked row — the OSRS hiscores report
     * rank -1, which every board shows as 0 — counts as the worst rank there
     * is rather than the best, so it stays at the bottom of a best-first board
     * and moves to the top of a worst-first one.
     *
     * @param  array<string, mixed>  $row
     */
    private function sortValue(array $row, string $sort): mixed
    {
        $value = $row[$sort] ?? 0;

        return $sort === 'rank' && $value <= 0 ? PHP_INT_MAX : $value;
    }
}
