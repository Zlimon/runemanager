import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { router, usePage } from '@inertiajs/vue3';

/*
 * The query behind every hiscore board: the username search and the column
 * sort. Both run on the server, because a board is paginated — a player who
 * sits on page 7 must still be findable from page 1, and a sort has to reorder
 * the whole board rather than the 25 rows on screen.
 *
 * Every change is one partial visit carrying the whole query, so searching
 * keeps the sort and sorting keeps the search. `page` is deliberately left
 * out: a re-ordered or narrowed board starts at its first page again. The URL
 * comes from `window.location` rather than being rebuilt with Ziggy because
 * the boards differ in their route parameters (`hiscores.loot.show` even takes
 * a `.*` wildcard source), and the current path is already the board itself.
 */

/*
 * Clicking a board column means "best first", and which direction that is
 * depends on what the column holds. A rank is a position: its best value is 1,
 * so it opens ascending. Every other column on a board is a metric — level,
 * xp, points, tasks, diary tiers, kill count, loot value — whose best value is
 * the largest, so it opens descending.
 */
const POSITION_COLUMNS = ['rank', 'position'];

const opensWith = (column) => (POSITION_COLUMNS.includes(column) ? 'asc' : 'desc');

/**
 * @param getFilters () => the page's `filters` prop, as a getter so the
 *                   composable reads the server's answer after every visit.
 */
export function useHiscoreBoard(getFilters) {
    const filters = computed(() => getFilters() ?? {});
    const search = ref(filters.value.username ?? '');

    /*
     * A term the server refused. Validation failures redirect back rather than
     * returning the board, so the results on screen stay as they were and the
     * only sign anything happened is this message.
     */
    const page = usePage();
    const error = computed(() => page.props.errors?.username ?? null);

    /*
     * The sort a click asked for, before the server has echoed it back. A
     * second click landing inside that round trip would otherwise read the old
     * answer and re-send the direction it just asked for instead of flipping
     * it. The echo clears this the moment it lands, so the server stays the
     * authority on what the board is actually sorted by.
     */
    const pending = ref(null);

    watch(filters, () => { pending.value = null; });

    const sorting = computed(() => pending.value ?? {
        sort: filters.value.sort ?? null,
        direction: filters.value.direction ?? null,
    });

    const visit = (query) => {
        router.get(window.location.pathname, query, {
            only: ['hiscores', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    /*
     * The board's whole query, with whatever this change alters applied last.
     * `per_page` rides along because the pager's links carry it: leaving it
     * out would quietly reset a board opened at ?per_page=10 to the default
     * as soon as anyone typed.
     */
    const query = (overrides = {}) => ({
        username: search.value,
        per_page: filters.value.per_page ?? undefined,
        sort: sorting.value.sort ?? undefined,
        direction: sorting.value.direction ?? undefined,
        ...overrides,
    });

    const reload = debounce(() => visit(query()), 300);

    watch(search, reload);

    /*
     * Clicking the column already sorted on flips it; any other column opens
     * best-first. A search still waiting on its debounce is folded into this
     * visit rather than left to fire afterwards with the sort it was typed
     * under.
     */
    const sortBy = (column) => {
        reload.cancel();

        const direction = sorting.value.sort === column
            ? (sorting.value.direction === 'asc' ? 'desc' : 'asc')
            : opensWith(column);

        pending.value = { sort: column, direction };

        visit(query({ sort: column, direction }));
    };

    return { search, sortBy, error };
}
