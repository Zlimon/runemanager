<script setup>
/*
 * SPEC §5.3 — per-data-type freshness indicator.
 *
 * Shows "Updated 2 minutes ago" (via dayjs.fromNow()) with the exact timestamp
 * in a tooltip. Flips to a stale style once the data is older than
 * `staleAfterMinutes` (defaults to the server-shared value but accepts an
 * override per call site).
 */
import { computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    updatedAt: {
        type: [String, null],
        default: null,
    },
    staleAfterMinutes: {
        type: Number,
        required: true,
    },
});

const minutesAgo = computed(() => {
    if (!props.updatedAt) {
        return null;
    }
    return Math.max(0, Math.floor((Date.now() - new Date(props.updatedAt).getTime()) / 60000));
});

const isStale = computed(
    () => minutesAgo.value !== null && minutesAgo.value > props.staleAfterMinutes,
);

const label = computed(() => {
    if (!props.updatedAt) {
        return 'Never synced';
    }
    return `Updated ${dayjs(props.updatedAt).fromNow()}`;
});

const exact = computed(() =>
    props.updatedAt ? dayjs(props.updatedAt).format('MMM D, YYYY h:mm A') : '',
);
</script>

<template>
    <span :title="exact"
          :class="{
              'text-error': isStale,
              'text-gray-700 dark:text-gray-200': !isStale,
          }"
          class="text-xs">
        {{ label }}
    </span>
</template>
