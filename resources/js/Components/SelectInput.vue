<script setup>
import { onMounted, ref } from 'vue';

/*
 * Mirrors TextInput.vue's API so `<select>` call sites are as consistent as
 * `<input>` ones: v-model via modelValue/update:modelValue, an `error` prop
 * toggling `select-error`, autofocus support, and a forwarded `focus()`.
 * Pass <option> elements via the default slot, exactly like a native <select>.
 */
defineProps({
    modelValue: [String, Number, Boolean],
    error: Boolean,
    disabled: Boolean,
});

defineEmits(['update:modelValue']);

const select = ref(null);

onMounted(() => {
    if (select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <select
        ref="select"
        class="select select-bordered w-full"
        :class="{ 'select-error': error }"
        :value="modelValue"
        :disabled="disabled"
        @change="$emit('update:modelValue', $event.target.value)"
    >
        <slot />
    </select>
</template>
