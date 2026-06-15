<script setup>
import { computed } from "vue";

/*
 * A small labelled chip that picks up the active resource pack's metal button
 * face (see .resource-pack-badge in per-pack.css) so it matches the rest of the
 * UI, falling back to the plain DaisyUI badge when no pack is active. For
 * non-interactive labels — event types, categories, "Default" markers, etc.
 */
const props = defineProps({
    // '', 'xs', 'sm', 'lg' — maps to DaisyUI's badge sizes.
    size: { type: String, default: '' },
});

const sizeClass = computed(() => (props.size ? `badge-${props.size}` : ''));
</script>

<template>
    <span class="badge resource-pack-badge gap-1 px-2" :class="sizeClass">
        <slot />
    </span>
</template>
