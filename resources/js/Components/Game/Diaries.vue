<script setup>
import { computed } from "vue";

/*
 * Achievement Diary completion grid: 12 areas × 4 tiers. A filled cell means the
 * tier is complete. Driven by the {area: {tier: bool}} map pushed by the plugin;
 * renders in a fixed area/tier order regardless of key order.
 */
const props = defineProps({
    diaries: {
        type: Object,
        default: () => ({}),
    },
});

const TIERS = ['Easy', 'Medium', 'Hard', 'Elite'];
const AREAS = [
    'Ardougne', 'Desert', 'Falador', 'Fremennik', 'Kandarin', 'Karamja',
    'Kourend', 'Lumbridge', 'Morytania', 'Varrock', 'Western', 'Wilderness',
];

const isDone = (area, tier) => props.diaries?.[area]?.[tier] === true;

const completed = computed(() =>
    AREAS.reduce((total, area) =>
        total + TIERS.filter((tier) => isDone(area, tier)).length, 0));

const hasData = computed(() => completed.value > 0 || Object.keys(props.diaries ?? {}).length > 0);
</script>

<template>
    <div v-if="hasData">
        <div class="mb-2 flex justify-end">
            <span class="text-xs text-base-content/60">{{ completed }} / 48 completed</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th v-for="tier in TIERS" :key="tier" class="text-center">{{ tier }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="area in AREAS" :key="area">
                        <td class="font-medium">{{ area }}</td>
                        <td v-for="tier in TIERS" :key="tier" class="text-center">
                            <span class="inline-block h-3 w-3 rounded-full"
                                  :class="isDone(area, tier) ? 'bg-success' : 'bg-base-300'"
                                  :title="`${area} ${tier}: ${isDone(area, tier) ? 'complete' : 'incomplete'}`"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div v-else class="flex h-32 items-center justify-center text-base-content/60">
        No achievement diary data yet.
    </div>
</template>
