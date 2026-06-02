<script setup>
import { computed } from "vue";
import dayjs from "dayjs";

const props = defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

const formatValue = (gp) => {
    if (!gp) {
        return '';
    }
    if (gp >= 1_000_000) {
        return `${(gp / 1_000_000).toFixed(1)}M`;
    }
    if (gp >= 1_000) {
        return `${(gp / 1_000).toFixed(1)}K`;
    }
    return gp.toLocaleString('en-US');
};

const hasEntries = computed(() => props.entries.length > 0);
</script>

<template>
    <div v-if="hasEntries"
         class="max-h-[28rem] overflow-y-scroll bg-base-200 border border-base-300 rounded">
        <ul class="divide-y divide-base-300">
            <li v-for="(entry, index) in entries" :key="index"
                class="p-3">
                <div class="flex items-baseline justify-between">
                    <span class="font-semibold">{{ entry.source }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                          :title="dayjs(entry.killed_at).format('MMM D, YYYY h:mm A')">
                        {{ dayjs(entry.killed_at).fromNow() }}
                    </span>
                </div>

                <div class="mt-1 flex flex-wrap gap-2">
                    <div v-for="item in entry.items" :key="item.id"
                         class="box flex h-12 w-12 items-center justify-center hover:bg-base-300"
                         :title="`${item.name ?? 'Item ' + item.id} × ${item.quantity}`">
                        <span v-if="item.quantity > 1"
                              class="absolute p-1 text-xs font-bold">
                            {{ item.quantity > 1000 ? `${Math.floor(item.quantity / 1000)}K` : item.quantity }}
                        </span>
                        <img v-if="item.icon"
                             :src="`data:image/jpeg;base64,${item.icon}`"
                             class="object-contain"
                             loading="lazy">
                        <span v-else class="text-xs">{{ item.name ?? item.id }}</span>
                    </div>
                </div>

                <p v-if="entry.total_value > 0"
                   class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ formatValue(entry.total_value) }} gp
                </p>
            </li>
        </ul>
    </div>
    <div v-else class="flex h-32 items-center justify-center">
        <p class="text-gray-500 dark:text-gray-400">No loot recorded for this account yet</p>
    </div>
</template>
