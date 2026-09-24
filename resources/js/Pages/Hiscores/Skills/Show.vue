<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/Tables/LeaderboardTable.vue";
import { useHiscoreBoard } from "@/composables/useHiscoreBoard";

const props = defineProps({
    skillName: {
        type: String,
        required: true,
    },
    skillSlug: {
        type: String,
        required: true,
    },
    hiscores: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const overall = props.skillSlug === 'overall';
const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: overall ? 'Total level' : 'Level', key: 'level', format: 'number' },
    { label: overall ? 'Total XP' : 'XP', key: 'xp', format: 'number' },
];

const { search, sortBy, error } = useHiscoreBoard(() => props.filters);
</script>

<template>
    <AppLayout :title="skillName">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="skillName" v-model:search="search" :error="error" />
                    <LeaderboardTable :columns="columns" :hiscores="hiscores" :filters="filters"
                                      @sort="sortBy" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
