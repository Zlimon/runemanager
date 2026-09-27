<script setup>
import { computed, ref } from "vue";
import { Deferred, Link, router, usePage } from "@inertiajs/vue3";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import TabbedCard from "@/Components/Cards/TabbedCard.vue";
import LineChart from "@/Components/Display/LineChart.vue";
import StatTile from "@/Components/Display/StatTile.vue";
import Loader from "@/Components/Display/Loader.vue";
import ChoiceChips from "@/Components/Tables/ChoiceChips.vue";

/*
 * The account's Wise Old Man panel: its XP or EHP/EHB history over a range,
 * and what it gained over a period. `series` and `gains` are deferred props
 * (womSeries / womGains); switching range or period reloads only that prop.
 */
const props = defineProps({
    account: { type: Object, required: true },
    // WomAccountView::summary(), or null when WOM has never been asked.
    wom: { type: Object, default: null },
    series: { type: Array, default: null },
    gains: { type: Object, default: null },
    filters: { type: Object, default: () => ({ range: 'year', period: 'week' }) },
});

const page = usePage();
const { packIcon } = useResourcePackIcon();

const tabs = [
    { key: 'progress', label: 'Progress', icon: packIcon('skill', 'overall') },
    { key: 'gains', label: 'Gains', icon: packIcon('tab', 'stats') },
];

const RANGES = [
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
    { value: 'all', label: 'All' },
];
const PERIODS = [
    { value: 'day', label: 'Day' },
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
];
const METRICS = [
    { value: 'xp', label: 'XP' },
    { value: 'efficiency', label: 'EHP / EHB' },
];

const range = ref(props.filters.range ?? 'year');
const period = ref(props.filters.period ?? 'week');
const metric = ref('xp');

const isOwner = computed(() => page.props.auth?.user?.id === props.account.user_id);
const linked = computed(() => props.wom?.linked === true);
const unusualStatus = computed(() => linked.value && props.wom.status && props.wom.status !== 'active'
    ? props.wom.status
    : null);

const reload = (only, data) => router.reload({
    only: [only],
    data,
    preserveState: true,
    preserveScroll: true,
    replace: true,
});

const pickRange = (key) => {
    range.value = key;
    reload('womSeries', { wrange: key });
};

const pickPeriod = (key) => {
    period.value = key;
    reload('womGains', { wperiod: key });
};

const chartSeries = computed(() => {
    const points = props.series ?? [];

    if (metric.value === 'xp') {
        return [{ label: 'Total XP', points: points.map((p) => ({ t: p.t, v: p.exp })) }];
    }

    return [
        { label: 'EHP', points: points.map((p) => ({ t: p.t, v: p.ehp })) },
        { label: 'EHB', points: points.map((p) => ({ t: p.t, v: p.ehb })) },
    ];
});

const label = (metricKey) => metricKey.split('_').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

// Boss icons are plain files named after the hiscore slug with dashes; WOM's
// metric names match them except where WOM shortens the name.
const BOSS_FILES = { tombs_of_amascut_expert: 'tombs-of-amascut-expert-mode' };
const bossIcon = (metricKey) => ({ src: `/images/boss/${BOSS_FILES[metricKey] ?? metricKey.replaceAll('_', '-')}.png` });

// WOM's skill metric names match the pack sprite slugs (useResourcePackIcon
// separately maps 'overall' to the "total" sprite) except runecrafting,
// which packs — and OSRS itself — call "runecraft".
const SKILL_SPRITE_SLUGS = { runecrafting: 'runecraft' };
const skillIcon = (metricKey) => packIcon('skill', SKILL_SPRITE_SLUGS[metricKey] ?? metricKey);
const hideBroken = (event) => { event.target.style.visibility = 'hidden'; };
</script>

<template>
    <div>
        <div class="mb-2 flex items-baseline justify-between">
            <h3>Wise Old Man</h3>
            <a v-if="wom?.url" :href="wom.url" target="_blank" rel="noopener"
               class="link link-hover text-sm text-base-content/70">
                View on Wise Old Man
            </a>
        </div>

        <!-- Not linked: never checked, or WOM does not know the name. -->
        <div v-if="!linked"
             class="rounded border border-base-300 bg-base-200 p-6 text-center text-sm text-base-content/70 resource-pack-dialog">
            <p v-if="wom?.not_found">{{ account.username }} is not tracked on Wise Old Man.</p>
            <p v-else>{{ account.username }} has not been checked on Wise Old Man yet.</p>
            <Link v-if="isOwner" :href="route('wise-old-man.index')" class="link mt-2 inline-block">
                Manage Wise Old Man tracking
            </Link>
        </div>

        <template v-else>
            <p v-if="unusualStatus" class="mb-2 rounded bg-warning/15 px-3 py-2 text-sm text-warning-content">
                Wise Old Man marks this profile as <strong>{{ unusualStatus }}</strong>, so its numbers may be out of date.
            </p>

            <TabbedCard :tabs="tabs" body-class="p-4">
                <template #progress>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <ChoiceChips v-model="metric" :options="METRICS" label="Series" />
                        <ChoiceChips :model-value="range" :options="RANGES" label="Range" @update:model-value="pickRange" />
                    </div>

                    <Deferred data="womSeries">
                        <template #fallback>
                            <div class="flex h-60 items-center justify-center">
                                <Loader :component="true" :loading="true" />
                            </div>
                        </template>

                        <template #default="{ reloading }">
                            <div :class="{ 'opacity-50': reloading }">
                                <LineChart :series="chartSeries" :format="metric === 'xp' ? 'xp' : 'hours'" />
                            </div>
                        </template>
                    </Deferred>
                </template>

                <template #gains>
                    <div class="mb-3 flex justify-end">
                        <ChoiceChips :model-value="period" :options="PERIODS" label="Period" @update:model-value="pickPeriod" />
                    </div>

                    <Deferred data="womGains">
                        <template #fallback>
                            <div class="flex h-40 items-center justify-center">
                                <Loader :component="true" :loading="true" />
                            </div>
                        </template>

                        <template #default="{ reloading }">
                            <div v-if="gains" :class="{ 'opacity-50': reloading }">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <StatTile label="XP gained" :value="gains.headline.exp" format="xp"
                                              :icon="packIcon('skill', 'overall')" />
                                    <StatTile label="EHP gained" :value="gains.headline.ehp" format="hours" />
                                    <StatTile label="EHB gained" :value="gains.headline.ehb" format="hours" />
                                </div>

                                <div v-if="gains.skills.length || gains.bosses.length"
                                     class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <table v-if="gains.skills.length" class="table table-sm">
                                        <thead>
                                            <tr><th>Skill</th><th class="text-right">XP gained</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in gains.skills" :key="row.metric">
                                                <td>
                                                    <span class="flex items-center gap-2">
                                                        <img v-bind="skillIcon(row.metric)" class="h-5 w-5 object-contain" alt="">
                                                        {{ label(row.metric) }}
                                                        <span class="text-xs text-base-content/50">{{ row.level }}</span>
                                                    </span>
                                                </td>
                                                <td class="text-right">{{ row.gained.toLocaleString('en-US') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table v-if="gains.bosses.length" class="table table-sm">
                                        <thead>
                                            <tr><th>Boss</th><th class="text-right">Kills</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in gains.bosses" :key="row.metric">
                                                <td>
                                                    <span class="flex items-center gap-2">
                                                        <img v-bind="bossIcon(row.metric)" class="h-5 w-5 object-contain" alt=""
                                                             @error="hideBroken">
                                                        {{ label(row.metric) }}
                                                    </span>
                                                </td>
                                                <td class="text-right">{{ row.gained.toLocaleString('en-US') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="mt-4 text-center text-sm text-base-content/60">
                                    No skill or boss gains to show for this period.
                                </p>
                            </div>
                        </template>
                    </Deferred>
                </template>
            </TabbedCard>
        </template>
    </div>
</template>
