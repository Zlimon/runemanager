<script setup>
import {ref, onMounted} from 'vue';

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

defineExpose({focus: () => input.value.focus()});
</script>

<template>
    <label :class="{ 'input-error': error }"
           class="input input-bordered flex items-center gap-2">
        <input ref="input"
               :disabled="disabled"
               :value="modelValue"
               class="grow"
               type="search"
               @input="$emit('update:modelValue', $event.target.value)"/>
        <svg
            class="h-4 w-4 opacity-70"
            fill="currentColor"
            viewBox="0 0 16 16"
            xmlns="http://www.w3.org/2000/svg">
            <path
                clip-rule="evenodd"
                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                fill-rule="evenodd"/>
        </svg>
    </label>
</template>
