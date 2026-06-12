<script setup>
import { computed } from "vue";
import dayjs from "dayjs";
import LootItems from "@/Components/Game/LootItems.vue";

const props = defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

const hasEntries = computed(() => props.entries.length > 0);
</script>

<template>
    <div v-if="hasEntries"
         class="max-h-[28rem] overflow-y-scroll bg-base-200 border border-base-300 rounded resource-pack-dialog p-3">
        <ul class="divide-y divide-base-300">
            <li v-for="(entry, index) in entries" :key="index"
                class="p-3">
                <div class="flex items-baseline justify-between">
                    <span class="font-semibold">{{ entry.source }}</span>
                    <span class="text-xs text-gray-700 dark:text-gray-200"
                          :title="dayjs(entry.killed_at).format('MMM D, YYYY h:mm A')">
                        {{ dayjs(entry.killed_at).fromNow() }}
                    </span>
                </div>

                <LootItems :items="entry.items" :total-value="entry.total_value" class="mt-1" />
            </li>
        </ul>
    </div>
    <div v-else class="flex h-32 items-center justify-center">
        <p class="text-gray-700 dark:text-gray-200">No loot recorded for this account yet</p>
    </div>
</template>
