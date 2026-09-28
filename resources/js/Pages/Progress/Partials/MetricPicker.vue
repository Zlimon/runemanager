<script setup>
import { computed } from 'vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';

/*
 * Picks the skill, boss or activity a board ranks on: a native <select>
 * grouped as Wise Old Man groups its metrics, with the chosen metric's icon
 * beside it (options cannot carry images).
 */
const props = defineProps({
    modelValue: { type: String, required: true },
    // { skills, bosses, activities: [{ metric, label, icon }] }
    metrics: { type: Object, required: true },
});

defineEmits(['update:modelValue']);

const { packIcon } = useResourcePackIcon();

const GROUPS = [
    { key: 'skills', label: 'Skills' },
    { key: 'bosses', label: 'Bosses' },
    { key: 'activities', label: 'Activities' },
];

const selected = computed(() => GROUPS
    .flatMap((group) => props.metrics[group.key] ?? [])
    .find((option) => option.metric === props.modelValue));

const icon = computed(() => {
    const value = selected.value?.icon;

    return value?.kind ? packIcon(value.kind, value.slug) : value;
});
</script>

<template>
    <div class="flex items-center gap-2">
        <img v-if="icon" v-bind="icon" alt="" class="h-6 w-6 shrink-0 object-contain">
        <div class="w-48">
            <SelectInput :model-value="modelValue" class="select-sm" aria-label="Metric"
                         @update:model-value="$emit('update:modelValue', $event)">
                <optgroup v-for="group in GROUPS" :key="group.key" :label="group.label">
                    <option v-for="option in metrics[group.key] ?? []" :key="option.metric" :value="option.metric">
                        {{ option.label }}
                    </option>
                </optgroup>
            </SelectInput>
        </div>
    </div>
</template>
