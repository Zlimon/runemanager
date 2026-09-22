<script setup>
import { computed } from "vue";
import dayjs from "dayjs";
import LootItems from "@/Components/Game/Items/LootItems.vue";

/*
 * Recent Loot, grouped by source like the Loot hiscores: one card per source
 * with duplicate items merged (quantities summed, sorted by quantity), the
 * combined GP value, the drop count, and the time of the latest drop. Entries
 * arrive newest-first, so the first entry seen per source carries its latest
 * killed_at and groups keep that order.
 */
const props = defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

const groups = computed(() => {
    const bySource = new Map();

    for (const entry of props.entries) {
        let group = bySource.get(entry.source);
        if (!group) {
            group = {
                source: entry.source,
                drops: 0,
                total_value: 0,
                latest: entry.killed_at,
                items: new Map(),
            };
            bySource.set(entry.source, group);
        }

        group.drops += 1;
        group.total_value += entry.total_value ?? 0;

        for (const item of entry.items ?? []) {
            const merged = group.items.get(item.id);
            if (merged) {
                merged.quantity += item.quantity;
            } else {
                group.items.set(item.id, { ...item });
            }
        }
    }

    return [...bySource.values()].map((group) => ({
        ...group,
        items: [...group.items.values()].sort((a, b) => b.quantity - a.quantity),
    }));
});
</script>

<template>
    <div v-if="groups.length"
         class="max-h-[28rem] overflow-y-scroll bg-base-200 border border-base-300 rounded resource-pack-dialog p-3">
        <ul class="divide-y divide-base-300">
            <li v-for="group in groups" :key="group.source"
                class="p-3">
                <div class="flex items-baseline justify-between">
                    <span class="font-semibold">
                        {{ group.source }}
                        <span v-if="group.drops > 1" class="text-xs font-normal text-base-content/50">
                            · {{ group.drops }} drops
                        </span>
                    </span>
                    <span class="text-xs text-base-content/70"
                          :title="dayjs(group.latest).format('MMM D, YYYY h:mm A')">
                        {{ dayjs(group.latest).fromNow() }}
                    </span>
                </div>

                <LootItems :items="group.items" :total-value="group.total_value" class="mt-1" />
            </li>
        </ul>
    </div>
    <div v-else class="flex h-32 items-center justify-center">
        <p class="text-base-content/70">No loot recorded for this account yet</p>
    </div>
</template>
