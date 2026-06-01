<script setup>
import {ref, onMounted} from 'vue';

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

defineExpose({focus: () => input.value.focus()});
</script>

<template>
    <select ref="input"
            :class="{ 'select-error': error }"
            :disabled="disabled"
            class="select select-bordered w-full max-w-xs"
            @input="$emit('update:modelValue', (optionObject ? JSON.parse($event.target.value) : {id: $event.target.value}))">
        <option v-if="optionDefault !== undefined" :value="optionDefault">
            {{ optionDefault.charAt(0).toUpperCase() + optionDefault.slice(1) }}
        </option>
        <option v-for="(option, index) in options"
                :value="((optionKey === undefined || optionKey === '') || (optionValue === undefined || optionValue === '') ? options[index] : option[optionValue])">
            <div
                v-if="(optionKey === undefined || optionKey === '') || (optionValue === undefined || optionValue === '')">
                {{ option }}
            </div>
            <div v-else-if="option[optionKey] !== undefined && typeof option[optionKey] === 'string'">
                {{ option[optionKey].charAt(0).toUpperCase() + option[optionKey].slice(1) }}
            </div>
            <div v-else>
                {{ option[optionKey] }}
            </div>
        </option>
    </select>
</template>
