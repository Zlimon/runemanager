<script setup>
import { computed } from "vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import DataTable from "@/Components/Tables/DataTable.vue";
import Badge from "@/Components/Display/Badge.vue";

/*
 * One group of WomMetricGains rows (skills, bosses or activities). Only
 * metrics that moved are listed unless `showUnchanged` is on; a metric new to
 * the hiscores has no gain (null) and shows a "new" badge instead. Picking a
 * name charts that metric.
 */
const props = defineProps({
    rows: { type: Array, default: () => [] },
    // 'skills' | 'bosses' | 'activities'
    group: { type: String, required: true },
    showUnchanged: { type: Boolean, default: false },
    selected: { type: String, default: null },
});

const emit = defineEmits(['select']);

const { packIcon } = useResourcePackIcon();
const iconAttrs = (icon) => (icon?.kind ? packIcon(icon.kind, icon.slug) : icon);

const VALUE_COLUMNS = {
    skills: [
        { key: 'level', label: 'Level', format: 'number', align: 'right' },
        { key: 'xp', label: 'XP', format: 'number', align: 'right' },
        { key: 'gained', label: 'XP gained', format: 'number', align: 'right' },
    ],
    bosses: [
        { key: 'kills', label: 'Kills', format: 'number', align: 'right' },
        { key: 'gained', label: 'Kills gained', format: 'number', align: 'right' },
    ],
    activities: [
        { key: 'score', label: 'Score', format: 'number', align: 'right' },
        { key: 'gained', label: 'Gained', format: 'number', align: 'right' },
    ],
};

const columns = computed(() => [
    { key: 'label', label: props.group === 'skills' ? 'Skill' : (props.group === 'bosses' ? 'Boss' : 'Activity') },
    ...VALUE_COLUMNS[props.group],
]);

// Biggest gain first; new-on-the-hiscores rows (null) after the gainers.
const visible = computed(() => props.rows
    .filter((row) => props.showUnchanged || row.gained === null || row.gained > 0)
    .sort((a, b) => (b.gained ?? -1) - (a.gained ?? -1)));
</script>

<template>
    <DataTable :columns="columns" :rows="visible" row-key="metric"
               :row-class="(row) => row.metric === selected ? 'bg-base-300/60' : ''">
        <template #cell-label="{ row }">
            <button type="button" class="flex items-center gap-2 text-left hover:underline"
                    :title="`Chart ${row.label}`" @click="emit('select', row.metric)">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                    <img v-if="row.icon" v-bind="iconAttrs(row.icon)" class="max-h-5 max-w-5 object-contain" alt="">
                </span>
                {{ row.label }}
            </button>
        </template>
        <template #cell-gained="{ row }">
            <Badge v-if="row.gained === null" size="sm">new</Badge>
            <span v-else :class="row.gained > 0 ? 'font-semibold text-success' : 'text-base-content/50'">
                {{ row.gained > 0 ? '+' : '' }}{{ row.gained.toLocaleString('en-US') }}
            </span>
            <span v-if="row.levels_gained > 0" class="ml-1 text-xs text-base-content/60">
                (+{{ row.levels_gained }} lvl)
            </span>
        </template>
        <template #empty>Nothing gained this period.</template>
    </DataTable>
</template>
