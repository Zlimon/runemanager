<?php

namespace App\Http\Requests;

use App\Rules\AccountUsernameRule;
use App\Services\Hiscores\LeaderboardPage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The username search, page size and column sort shared by every hiscore
 * board. The username rules match {@see FilterAccountsRequest} so the same
 * term is accepted on a board as on the account index.
 */
class FilterHiscoresRequest extends FormRequest
{
    /**
     * The columns each board may be sorted by, keyed by route name. A board's
     * list holds only values its own rows carry and its page shows, so a sort
     * never moves rows around a number the reader cannot see. The frontend
     * reads the list back from the `filters` prop to decide which headers are
     * clickable, and a board missing from this map cannot be sorted at all.
     *
     * Every board carries a `rank`: the OSRS rank on the hiscore-sourced
     * boards, and the standing stamped by
     * {@see LeaderboardPage::rankedBy()} on the rest.
     *
     * @var array<string, array<int, string>>
     */
    public const SORTABLE = [
        'hiscores.overall.index' => ['rank', 'level', 'xp'],
        'hiscores.skills.index' => ['rank', 'level', 'xp'],
        'hiscores.bosses.index' => ['rank', 'kill_count'],
        'hiscores.clues.index' => ['rank', 'kill_count'],
        'hiscores.diaries.index' => ['rank', 'completed', 'easy', 'medium', 'hard', 'elite'],
        'hiscores.combat-achievements.index' => ['rank', 'points', 'tasks_completed'],
        'hiscores.collection-log.index' => ['rank', 'obtained', 'total'],
        'hiscores.loot.show' => ['rank', 'total_value', 'drops'],
    ];

    /** Access is enforced by the route middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string', new AccountUsernameRule],
            'per_page' => ['nullable', 'integer', 'min:4', 'max:64'],
            'sort' => ['nullable', 'string', Rule::in($this->sortable())],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    /** The search term, or null when the board is unfiltered. */
    public function username(): ?string
    {
        return $this->validated('username');
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? LeaderboardPage::PER_PAGE);
    }

    /**
     * The columns this board may be sorted by.
     *
     * @return array<int, string>
     */
    public function sortable(): array
    {
        return self::SORTABLE[$this->route()?->getName()] ?? [];
    }

    /** The column to sort by, or null while the board is in its canonical rank order. */
    public function sort(): ?string
    {
        return $this->validated('sort');
    }

    /**
     * The direction in effect, and null whenever {@see sort()} is. A column
     * asked for without one is read best-first, which is ascending for `rank`
     * (rank 1 is the best there is) and descending for every metric.
     */
    public function direction(): ?string
    {
        $sort = $this->sort();

        if ($sort === null) {
            return null;
        }

        return $this->validated('direction') ?? ($sort === 'rank' ? 'asc' : 'desc');
    }

    /**
     * The filter prop every board echoes back, so the search box, the column
     * headers and the pagination links keep their state across visits.
     *
     * @return array{username: string, per_page: int, sort: ?string, direction: ?string, sortable: array<int, string>}
     */
    public function filters(): array
    {
        return [
            'username' => $this->username() ?? '',
            'per_page' => $this->perPage(),
            'sort' => $this->sort(),
            'direction' => $this->direction(),
            'sortable' => $this->sortable(),
        ];
    }
}
