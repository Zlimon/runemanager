<script setup>
/*
 * Wise Old Man's efficiency numbers, kept to one muted line: gains in skills
 * and bosses are what the tab is about.
 */
defineProps({
    // WomAccountView::summary() for a linked player.
    wom: { type: Object, required: true },
});

const hours = (value) => `${Number(value ?? 0).toLocaleString('en-US', { maximumFractionDigits: 0 })} h`;
</script>

<template>
    <p class="text-xs text-base-content/60">
        EHP {{ hours(wom.ehp) }} · EHB {{ hours(wom.ehb) }} ·
        <template v-if="wom.ttm > 0">Time to max {{ hours(wom.ttm) }}</template>
        <template v-else>Maxed</template>
        ·
        <a :href="wom.url" target="_blank" rel="noopener" class="link link-hover">Wise Old Man ↗</a>
    </p>
</template>
