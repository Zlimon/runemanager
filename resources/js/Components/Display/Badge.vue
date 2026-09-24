<script setup>
import { computed } from "vue";

/*
 * A small labelled chip that picks up the active resource pack's metal button
 * face (see .resource-pack-badge in per-pack.css) so it matches the rest of the
 * UI, falling back to the plain DaisyUI badge when no pack is active. Used for
 * labels — event types, categories, "Default" markers — and for chips that
 * attach their own @click, such as the account-type filters on Accounts.
 */
const props = defineProps({
    // '', 'xs', 'sm', 'lg' — maps to DaisyUI's badge sizes.
    size: { type: String, default: '' },
    /*
     * DaisyUI badge colour ('neutral', 'info', 'primary', …). It only shows in
     * the untextured interface: .resource-pack-badge paints the pack's button
     * face over every variant, so under a pack all badges look alike by design.
     */
    variant: { type: String, default: 'neutral' },
});

const sizeClass = computed(() => (props.size ? `badge-${props.size}` : ''));
</script>

<template>
    <span class="badge resource-pack-badge gap-1 px-2" :class="[`badge-${variant}`, sizeClass]">
        <slot />
    </span>
</template>
