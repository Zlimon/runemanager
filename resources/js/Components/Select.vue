<script setup>
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    modelValue: Object,
    options: Array,
    optionDefault: String,
    optionKey: String,
    optionValue: String,
    optionObject: Boolean,
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
    <select
        ref="input"
        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
        :class="classes"
        @input="$emit('update:modelValue', (optionObject ? JSON.parse($event.target.value) : {id: $event.target.value}))"
        :disabled="disabled">
        <option v-if="optionDefault !== undefined" :value="optionDefault">{{ optionDefault.charAt(0).toUpperCase() + optionDefault.slice(1) }}</option>
        <option v-for="(option, index) in options" :value="((optionKey === undefined || optionKey === '') || (optionValue === undefined || optionValue === '') ? options[index] : option[optionValue])">
            <div v-if="(optionKey === undefined || optionKey === '') || (optionValue === undefined || optionValue === '')">
                {{ option }}
            </div>
            <div v-else-if="option[optionKey] !== undefined && typeof option[optionKey] === 'string'">
                {{ option[optionKey].charAt(0).toUpperCase() + option[optionKey].slice(1) }}
            </div>
            <div v-else>
                {{ option }}
            </div>
        </option>
    </select>
</template>
