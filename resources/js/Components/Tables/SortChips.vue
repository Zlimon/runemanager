<script setup>
/*
 * A table's sortable column headers, for a board that has no table. The loot
 * board renders cards, and a card list paged 25 at a time needs a sort just as
 * a table does — so the headers become a DaisyUI `join` of buttons, the
 * sorted column carrying its direction arrow. The button language is the
 * pager's, so the two controls read as one family under every pack.
 *
 * Clicking emits the column key and nothing else; what a sort means is the
 * owner's to decide, exactly as with DataTable's headers.
 */
defineProps({
    // The sortable column keys, in the order they should read.
    columns: { type: Array, default: () => [] },
    // key -> label. A key with no entry here labels itself.
    labels: { type: Object, default: () => ({}) },
    // The column sorted on, and the direction it is sorted in.
    sort: { type: String, default: null },
    direction: { type: String, default: 'asc' },
});

defineEmits(['sort']);
</script>

<template>
    <div v-if="columns.length" class="join border border-base-300"
         role="group" aria-label="Sort by">
        <button v-for="column in columns" :key="column"
                type="button"
                class="btn btn-sm join-item"
                :class="column === sort ? 'btn-primary' : 'btn-ghost'"
                :aria-pressed="column === sort"
                @click="$emit('sort', column)">
            {{ labels[column] ?? column }}
            <span v-if="column === sort" class="text-xs">
                {{ direction === 'asc' ? '▲' : '▼' }}
            </span>
        </button>
    </div>
</template>
