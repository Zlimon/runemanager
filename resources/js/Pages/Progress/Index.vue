<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatTile from '@/Components/Display/StatTile.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';
import TopGainers from '@/Pages/Progress/Partials/TopGainers.vue';
import SkillLeaders from '@/Pages/Progress/Partials/SkillLeaders.vue';

/*
 * The instance's Wise Old Man progress, over members whose linked profile is
 * active and not hidden by its owner. Both boards are deferred; the period
 * applies to both, the metric only to the top gainers.
 */
const props = defineProps({
    filters: { type: Object, default: () => ({ period: 'week', metric: 'overall' }) },
    // { skills, bosses, activities: [{ metric, label, icon }] } for the metric picker.
    metrics: { type: Object, default: () => ({ skills: [], bosses: [], activities: [] }) },
    stats: {
        type: Object,
        default: () => ({ active_members: 0, week_exp_gained: 0, week_levels_gained: 0, week_boss_kills: 0 }),
    },
    topGainers: { type: Array, default: null },
    skillLeaders: { type: Array, default: null },
    // True when this viewer sees the boards anonymized: their rows then carry
    // no account_id and a masked username, so they never render a profile
    // link (see AccountName).
    anonymized: { type: Boolean, default: false },
});

const { packIcon } = useResourcePackIcon();

const PERIODS = [
    { value: 'day', label: 'Day' },
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
];

const period = ref(props.filters.period);
const metric = ref(props.filters.metric);

const reload = (only) => router.reload({
    only,
    data: { period: period.value, metric: metric.value },
    preserveState: true,
    preserveScroll: true,
    replace: true,
});

const pickPeriod = (value) => { period.value = value; reload(['topGainers', 'skillLeaders']); };
const pickMetric = (value) => { metric.value = value; reload(['topGainers']); };
</script>

<template>
    <AppLayout title="Progress">
        <div class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h1 class="header-chatbox-sword text-2xl font-bold">Progress</h1>
                <p class="mt-1 text-sm text-base-content/60">
                    Tracked by <a href="https://wiseoldman.net" target="_blank" rel="noopener" class="link">Wise Old Man</a>
                </p>
                <p v-if="anonymized" class="mt-1 text-xs text-base-content/50">
                    Player names are anonymized on this public board.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatTile label="Active members" :value="stats.active_members" />
                <StatTile label="XP gained this week" :value="stats.week_exp_gained" format="xp"
                          :icon="packIcon('skill', 'overall')" />
                <StatTile label="Levels gained this week" :value="stats.week_levels_gained" />
                <StatTile label="Boss kills this week" :value="stats.week_boss_kills" />
            </div>

            <div class="mt-8 flex justify-center">
                <ChoiceChips :model-value="period" :options="PERIODS" label="Period" @update:model-value="pickPeriod" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <TopGainers :gainers="topGainers" :metric="metric" :metrics="metrics" @update:metric="pickMetric" />
                <div class="lg:col-span-2">
                    <SkillLeaders :leaders="skillLeaders" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
