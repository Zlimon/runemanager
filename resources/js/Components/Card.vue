<script setup>
import { computed } from 'vue';

/*
 * The standard panel surface used across the app. Uses the pack-aware classes
 * so it picks up the active resource pack's derived card colour + accent border
 * (see app.css .pack-bg-card / .pack-accent-border), falling back to the theme
 * when no pack is active. Pass `padding` to override the default inset.
 *
 * `frame` selects which sprite the active pack paints as the edge. Both are
 * inert with no pack, where the CSS border draws the edge instead. They can't
 * be combined — each sets background-image, so the later rule in per-pack.css
 * would simply win regardless of the order the classes appear in the markup.
 */
const props = defineProps({
    padding: {
        type: String,
        default: 'p-6',
    },
    frame: {
        type: String,
        default: 'border',
        validator: (value) => ['border', 'dialog'].includes(value),
    },
});

const frameClass = computed(() => ({
    // Thin side-panel line — the default card edge.
    border: 'resource-pack-border',
    // Heavier iron-rivets frame, matching the page's tabbed panels. Brings its
    // own parchment fill, so it replaces the pack-derived card colour.
    dialog: 'resource-pack-dialog',
}[props.frame]));
</script>

<template>
    <div class="pack-bg-card border pack-accent-border rounded" :class="[padding, frameClass]">
        <slot />
    </div>
</template>
