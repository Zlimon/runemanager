<script setup>
import { onMounted, ref } from 'vue';

defineProps({
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
</script>

<template>
    <input
        ref="input"
        class="input input-bordered w-full"
        :class="{ 'input-error': error }"
        :value="modelValue"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
