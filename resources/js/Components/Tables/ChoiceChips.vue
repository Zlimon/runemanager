<script setup>
/*
 * A row of single-choice filter buttons (range, period, metric) in the same
 * `join` button language as SortChips' column headers — for a filter that
 * only ever has one active value, rather than a sort with a direction.
 */
defineProps({
    // [{ value, label }], in display order.
    options: { type: Array, required: true },
    modelValue: { type: String, default: null },
    label: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="join border border-base-300" role="group" :aria-label="label">
        <button v-for="option in options" :key="option.value" type="button"
                class="btn btn-sm join-item"
                :class="modelValue === option.value ? 'btn-primary btn-active' : 'btn-ghost'"
                :aria-pressed="modelValue === option.value"
                @click="$emit('update:modelValue', option.value)">
            {{ option.label }}
        </button>
    </div>
</template>
