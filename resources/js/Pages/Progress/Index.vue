<script setup>
import { Deferred } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatTile from '@/Components/Display/StatTile.vue';
import Loader from '@/Components/Display/Loader.vue';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';
import TotalsChart from '@/Pages/Progress/Partials/TotalsChart.vue';
import TopGainers from '@/Pages/Progress/Partials/TopGainers.vue';
import EfficiencyBoard from '@/Pages/Progress/Partials/EfficiencyBoard.vue';

/*
 * The instance's Wise Old Man progress, over members whose linked profile is
 * active and not hidden by its owner. Everything below the tiles is deferred.
 */
defineProps({
    filters: { type: Object, default: () => ({ range: 'year', period: 'week', metric: 'exp' }) },
    stats: { type: Object, default: () => ({ active_members: 0, total_ehp: 0, total_ehb: 0, week_exp_gained: 0 }) },
    series: { type: Array, default: null },
    topGainers: { type: Array, default: null },
    efficiency: { type: Object, default: null },
    // True when this viewer sees the board anonymized: topGainers/efficiency
    // rows then carry no account_id and a masked username, so the boards
    // below never render a profile link for them (see AccountName).
    anonymized: { type: Boolean, default: false },
});

const { packIcon } = useResourcePackIcon();
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
                <StatTile label="Total EHP" :value="stats.total_ehp" format="hours" />
                <StatTile label="Total EHB" :value="stats.total_ehb" format="hours" />
            </div>

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <TotalsChart :series="series" :range="filters.range" />
                </div>
                <TopGainers :gainers="topGainers" :period="filters.period" :metric="filters.metric" />
            </div>

            <Deferred data="efficiency">
                <template #fallback>
                    <div class="mt-8 flex h-40 items-center justify-center">
                        <Loader :component="true" :loading="true" />
                    </div>
                </template>

                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <EfficiencyBoard title="Efficient hours played" :rows="efficiency?.ehp ?? []" />
                    <EfficiencyBoard title="Efficient hours bossed" :rows="efficiency?.ehb ?? []" />
                </div>
            </Deferred>
        </div>
    </AppLayout>
</template>
