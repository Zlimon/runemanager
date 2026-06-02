<script setup>
/*
 * A textured button that uses the active resource pack's frame.
 *
 * The .resource-pack-world-map class is provided by the per-pack CSS in
 * public/resource-packs/{name}/resource-pack.css (loaded by app.blade.php).
 * When no pack is active the CSS class is undefined, so the button falls
 * back to .resource-pack-fallback styling for a plain bordered look.
 */
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="resource-pack-world-map resource-pack-fallback px-4 py-3 font-bold uppercase tracking-widest text-sm transition ease-in-out duration-150 disabled:opacity-50"
    >
        <span class="drop-shadow-sm">
            <slot />
        </span>
    </button>
</template>

<style scoped>
/* Fallback frame when no resource pack is loaded — keeps the button visible
   and pressable. The pack's CSS overrides background when present. */
button.resource-pack-fallback:not([style*='url']) {
    background: theme(colors.base-300, #94866D);
    border: 1px solid theme(colors.base-content, #282425);
    color: theme(colors.base-content, #282425);
}
</style>
