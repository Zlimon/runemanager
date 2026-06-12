<script setup>
import ItemSlot from "@/Components/Game/ItemSlot.vue";

/*
 * The item grid for a single loot drop: each item rendered through the shared
 * ItemSlot (so the icon, quantity badge and hover tooltip match the inventory,
 * bank, looting bag, etc.), plus an optional total GP value beneath. Shared by
 * the account "Recent Loot" panel and the Live Feed.
 */
defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    totalValue: {
        type: Number,
        default: 0,
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
</script>

<template>
    <div>
        <div class="flex flex-wrap gap-2">
            <ItemSlot v-for="item in items" :key="item.id"
                      :icon="item.icon"
                      :name="item.name ?? `Item ${item.id}`"
                      :quantity="item.quantity"
                      :examine="item.examine"
                      :highalch="item.highalch"
                      :lowalch="item.lowalch" />
        </div>

        <p v-if="totalValue > 0" class="mt-1 text-xs text-base-content/70">
            {{ formatValue(totalValue) }} gp
        </p>
    </div>
</template>
