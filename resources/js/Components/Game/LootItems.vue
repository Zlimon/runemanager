<script setup>
/*
 * The item-icon grid for a single loot drop: each item as a 48px cell with its
 * sprite and a stacked-quantity badge, plus an optional total GP value beneath.
 * Shared by the account "Recent Loot" panel and the Live Feed so a drop reads
 * the same in both.
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

const stackLabel = (qty) => (qty > 1000 ? `${Math.floor(qty / 1000)}K` : qty);
</script>

<template>
    <div>
        <div class="flex flex-wrap gap-2">
            <div v-for="item in items" :key="item.id"
                 class="relative flex h-12 w-12 items-center justify-center rounded hover:bg-white/10"
                 :title="`${item.name ?? 'Item ' + item.id} × ${item.quantity}`">
                <span v-if="item.quantity > 1"
                      class="absolute left-0 top-0 p-0.5 text-xs font-bold text-yellow-300"
                      style="text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.85)">
                    {{ stackLabel(item.quantity) }}
                </span>
                <img v-if="item.icon"
                     :src="`data:image/jpeg;base64,${item.icon}`"
                     class="object-contain"
                     loading="lazy">
                <span v-else class="text-xs">{{ item.name ?? item.id }}</span>
            </div>
        </div>

        <p v-if="totalValue > 0" class="mt-1 text-xs text-base-content/70">
            {{ formatValue(totalValue) }} gp
        </p>
    </div>
</template>
