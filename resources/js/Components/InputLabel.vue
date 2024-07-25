<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
