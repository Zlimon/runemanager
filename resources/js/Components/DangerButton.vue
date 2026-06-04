<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="btn btn-error btn-sm uppercase tracking-widest">
        <slot />
    </button>
</template>
