<script setup>
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    modelValue: String,
    error: Boolean,
    disabled: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

const classes = computed(() => {
    return [
        props.disabled === true ? '!bg-gray-200 cursor-not-allowed dark:text-gray-500' : '!bg-gray-50',
        props.error === true ? 'bg-red-50 border-red-500 text-red-900 placeholder-red-700 focus:ring-red-500 focus:border-red-500 dark:bg-red-100 dark:border-red-400' : '',
    ];
});
</script>

<template>
    <input
        ref="input"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resource-pack-iron-rivets"
        :class="classes"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :disabled="disabled"
    >
</template>
