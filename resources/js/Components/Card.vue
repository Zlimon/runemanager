<script setup>
/*
 * The standard panel surface used across the app. Uses the pack-aware classes
 * so it picks up the active resource pack's derived card colour + accent border
 * (see app.css .pack-bg-card / .pack-accent-border), falling back to the theme
 * when no pack is active. Pass `padding` to override the default inset.
 */
defineProps({
    padding: {
        type: String,
        default: 'p-6',
    },
});
</script>

<template>
    <div class="pack-bg-card border pack-accent-border rounded" :class="padding">
        <slot />
    </div>
</template>
