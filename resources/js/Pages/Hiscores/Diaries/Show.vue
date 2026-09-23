<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/Tables/LeaderboardTable.vue";
import { useHiscoreBoard } from "@/composables/useHiscoreBoard";

const props = defineProps({
    hiscores: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Completed', key: 'completed', format: 'number' },
    { label: 'Easy', key: 'easy', format: 'number' },
    { label: 'Medium', key: 'medium', format: 'number' },
    { label: 'Hard', key: 'hard', format: 'number' },
    { label: 'Elite', key: 'elite', format: 'number' },
];

const { search, sortBy, error } = useHiscoreBoard(() => props.filters);
</script>

<template>
    <AppLayout title="Achievement Diaries">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader title="Achievement Diaries" v-model:search="search" :error="error" />
                    <LeaderboardTable :columns="columns" :hiscores="hiscores" :filters="filters"
                                      @sort="sortBy" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
