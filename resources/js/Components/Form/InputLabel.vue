<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block mb-2 text-sm font-medium text-base-content">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
