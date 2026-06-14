<script setup>
/*
 * The hover tooltip for an OSRS item — name and examine text. Positioned above
 * its (relatively-positioned) parent cell. Shared by ItemSlot (inventory, bank,
 * loot, …) and the Equipment widget so every item reads the same. Styling comes
 * from the .box-tooltip class (pack-aware card surface).
 */
defineProps({
    name: { type: String, default: null },
    examine: { type: String, default: null },
    // Optional muted line beneath the examine, e.g. the collection-log obtained date.
    subtext: { type: String, default: null },
});
</script>

<template>
    <div class="box-tooltip bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap">
        <p class="font-semibold">{{ name }}</p>
        <p v-if="examine" class="opacity-80">{{ examine }}</p>
        <p v-if="subtext" class="opacity-60">{{ subtext }}</p>
    </div>
</template>
