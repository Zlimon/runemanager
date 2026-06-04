<script setup>
/*
 * Primary action button. A plain DaisyUI btn that automatically picks up the
 * active resource pack's button texture via the .btn rule in per-pack.css.
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
        class="btn btn-neutral uppercase tracking-widest"
    >
        <slot />
    </button>
</template>
