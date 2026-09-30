<script setup>
import { computed } from 'vue';

/*
 * Requests per time bucket as a column chart, errors stacked in the error
 * colour at the foot of each column. Plain CSS columns rather than LineChart,
 * whose axis is labelled in whole days and would hide hourly detail.
 */
const props = defineProps({
    // [{ t: ms, requests, errors }], oldest first.
    buckets: { type: Array, required: true },
});

const peak = computed(() => Math.max(1, ...props.buckets.map((bucket) => bucket.requests)));

const label = (bucket) => {
    const when = new Date(bucket.t).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
    return `${when}: ${bucket.requests.toLocaleString()} requests, ${bucket.errors.toLocaleString()} errors`;
};

const edge = (bucket) => (bucket
    ? new Date(bucket.t).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' })
    : '');
</script>

<template>
    <div>
        <div class="flex h-32 items-end gap-px" role="img"
             :aria-label="`Requests over time, peaking at ${peak.toLocaleString()} per interval`">
            <div v-for="bucket in buckets" :key="bucket.t" :title="label(bucket)"
                 class="flex min-w-0 flex-1 flex-col-reverse overflow-hidden rounded-t-sm bg-primary/70"
                 :style="{ height: `${(bucket.requests / peak) * 100}%` }">
                <div class="w-full bg-error" :style="{ height: `${bucket.requests ? (bucket.errors / bucket.requests) * 100 : 0}%` }" />
            </div>
        </div>
        <div class="mt-1 flex justify-between text-xs text-base-content/60">
            <span>{{ edge(buckets[0]) }}</span>
            <span class="flex items-center gap-3">
                <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-sm bg-primary/70" /> Requests</span>
                <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-sm bg-error" /> Errors</span>
            </span>
            <span>{{ edge(buckets.at(-1)) }}</span>
        </div>
    </div>
</template>
