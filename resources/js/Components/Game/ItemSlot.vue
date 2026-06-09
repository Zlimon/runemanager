<script setup>
import { ref } from "vue";

/*
 * A single OSRS item cell: icon with a quantity badge and a hover tooltip — the
 * slot first used on the Loot hiscores, reused for the inventory, looting bag
 * and bank. Empty slots (no icon and no name) render a blank cell so grids keep
 * their shape. The alch/examine lines only appear when those props are supplied.
 */
defineProps({
    icon: { type: String, default: null }, // base64 jpeg
    name: { type: String, default: null },
    quantity: { type: Number, default: 0 },
    examine: { type: String, default: null },
    highalch: { type: Number, default: null },
    lowalch: { type: Number, default: null },
});

const hovered = ref(false);

const formatQuantity = (q) => {
    if (q >= 1_000_000) {
        return `${Math.floor(q / 1_000_000)}M`;
    }
    if (q > 1000) {
        return `${Math.floor(q / 1000)}K`;
    }
    return q.toLocaleString("en-US");
};
</script>

<template>
    <div class="relative flex h-12 w-12 items-center justify-center rounded hover:bg-white/10"
         @mouseenter="hovered = true" @mouseleave="hovered = false">
        <template v-if="icon || name">
            <span v-if="quantity > 1"
                  class="map-quantity absolute left-0 top-0 z-10 px-0.5 text-xs font-bold text-yellow-300">
                {{ formatQuantity(quantity) }}
            </span>

            <img v-if="icon"
                 :src="`data:image/jpeg;base64,${icon}`"
                 class="object-contain" loading="lazy" :alt="name ?? ''">
            <span v-else class="text-center text-[10px] leading-tight">{{ name }}</span>

            <div v-if="hovered && name"
                 class="box-tooltip bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap">
                <p class="font-semibold">{{ name }}</p>
                <p v-if="examine" class="opacity-80">{{ examine }}</p>
                <p v-if="quantity > 0 && highalch">
                    HA: {{ (highalch * quantity).toLocaleString("en-US") }} gp
                    <span v-if="quantity > 1">({{ highalch.toLocaleString("en-US") }} ea)</span>
                </p>
                <p v-if="quantity > 0 && lowalch">
                    LA: {{ (lowalch * quantity).toLocaleString("en-US") }} gp
                    <span v-if="quantity > 1">({{ lowalch.toLocaleString("en-US") }} ea)</span>
                </p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.map-quantity {
    text-shadow: 0 0 2px #000, 0 0 2px #000;
}
</style>
