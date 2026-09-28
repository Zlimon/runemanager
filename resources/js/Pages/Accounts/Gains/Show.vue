<script setup>
import { computed, ref } from "vue";
import { Deferred, Link, router, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import AccountLayout from "@/Layouts/AccountLayout.vue";
import Card from "@/Components/Cards/Card.vue";
import TabbedCard from "@/Components/Cards/TabbedCard.vue";
import Loader from "@/Components/Display/Loader.vue";
import ChoiceChips from "@/Components/Tables/ChoiceChips.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import WomLine from "@/Pages/Accounts/Gains/Partials/WomLine.vue";
import GainsTable from "@/Pages/Accounts/Gains/Partials/GainsTable.vue";
import MetricChart from "@/Pages/Accounts/Gains/Partials/MetricChart.vue";
import Records from "@/Pages/Accounts/Gains/Partials/Records.vue";
import Achievements from "@/Pages/Accounts/Gains/Partials/Achievements.vue";

/*
 * What the account gained in skills, bosses and activities over a period
 * (Wise Old Man snapshots), a chart of the picked metric, and its personal
 * records and achievements. `gains` and `metricSeries` are deferred; a filter
 * change reloads only the prop it affects.
 */
const props = defineProps({
    account: { type: Object, required: true },
    position: { type: Object, default: null },
    vitals: { type: Object, default: null },
    location: { type: String, default: null },
    // WomAccountView::summary(), or null when WOM has never been asked.
    wom: { type: Object, default: null },
    filters: { type: Object, default: () => ({ period: 'week', metric: 'overall', range: 'year' }) },
    gains: { type: Object, default: null },
    metricSeries: { type: Object, default: null },
    records: { type: Array, default: () => [] },
    achievements: { type: Array, default: () => [] },
});

const page = usePage();
const { packIcon } = useResourcePackIcon();

const PERIODS = [
    { value: 'day', label: 'Day' },
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
];

const groupTabs = [
    { key: 'skills', label: 'Skills', icon: packIcon('tab', 'stats') },
    { key: 'bosses', label: 'Bosses', icon: { src: '/images/boss/bosses.png' } },
    { key: 'activities', label: 'Clues & minigames', icon: { src: '/images/clue/clues.png' } },
];

const activeGroup = ref('skills');
const showUnchanged = ref(false);

const period = ref(props.filters.period);
const metric = ref(props.filters.metric);
const range = ref(props.filters.range);

const isOwner = computed(() => page.props.auth?.user?.id === props.account.user_id);
const linked = computed(() => props.wom?.linked === true);
const unusualStatus = computed(() => linked.value && props.wom.status && props.wom.status !== 'active'
    ? props.wom.status
    : null);

const isSkillMetric = computed(() => metric.value === 'overall'
    || (props.gains?.skills ?? []).some((row) => row.metric === metric.value));

const reload = (only) => router.reload({
    only: [only],
    data: { period: period.value, metric: metric.value, range: range.value },
    preserveState: true,
    preserveScroll: true,
    replace: true,
});

const pickPeriod = (value) => {
    period.value = value;
    reload('gains');
};

const pickMetric = (value) => {
    metric.value = value;
    reload('metricSeries');
};

const pickRange = (value) => {
    range.value = value;
    reload('metricSeries');
};
</script>

<template>
    <AccountLayout :account="account" :position="position" :vitals="vitals" :location="location">
        <!-- Not linked: never checked, or WOM does not know the name. -->
        <Card v-if="!linked" frame="dialog" padding="p-6" class="text-center text-sm text-base-content/70">
            <p v-if="wom?.not_found">{{ account.username }} is not tracked on Wise Old Man.</p>
            <p v-else>{{ account.username }} has not been checked on Wise Old Man yet.</p>
            <Link v-if="isOwner" :href="route('wise-old-man.index')" class="link mt-2 inline-block">
                Manage Wise Old Man tracking
            </Link>
        </Card>

        <template v-else>
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <WomLine :wom="wom" />
                <ChoiceChips :model-value="period" :options="PERIODS" label="Period" @update:model-value="pickPeriod" />
            </div>

            <p v-if="unusualStatus" class="mb-3 rounded bg-warning/15 px-3 py-2 text-sm text-warning-content">
                Wise Old Man marks this profile as <strong>{{ unusualStatus }}</strong>, so its numbers may be out of date.
            </p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Deferred data="gains">
                    <template #fallback>
                        <div class="flex h-60 items-center justify-center">
                            <Loader :component="true" :loading="true" />
                        </div>
                    </template>

                    <template #default="{ reloading }">
                        <div :class="{ 'opacity-50': reloading }">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-xs text-base-content/60">
                                <span v-if="gains?.since">
                                    <!-- Overall comes from the longer total-XP history. -->
                                    Skill, boss and activity history starts {{ dayjs(gains.since).format('MMM D, YYYY') }},
                                    so their gains count from then.
                                </span>
                                <span v-else></span>
                                <label class="flex cursor-pointer items-center gap-2">
                                    <Checkbox v-model:checked="showUnchanged" />
                                    Show unchanged
                                </label>
                            </div>

                            <TabbedCard :tabs="groupTabs" v-model="activeGroup">
                                <template #default="{ active }">
                                    <GainsTable :rows="gains?.[active] ?? []" :group="active"
                                                :show-unchanged="showUnchanged" :selected="metric"
                                                @select="pickMetric" />
                                </template>
                            </TabbedCard>
                        </div>
                    </template>
                </Deferred>

                <Card padding="p-4">
                    <MetricChart :series="metricSeries" :range="range" :is-skill="isSkillMetric"
                                 @update:range="pickRange" />
                </Card>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Records :records="records" />
                <Achievements :achievements="achievements" />
            </div>
        </template>
    </AccountLayout>
</template>
