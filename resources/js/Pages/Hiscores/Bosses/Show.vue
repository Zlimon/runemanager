<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/Tables/LeaderboardTable.vue";
import { useHiscoreBoard } from "@/composables/useHiscoreBoard";

const props = defineProps({
    collectionName: {
        type: String,
        required: true,
    },
    collectionSlug: {
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

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Score', key: 'kill_count', format: 'number' },
];

const { search, sortBy, error } = useHiscoreBoard(() => props.filters);
</script>

<template>
    <AppLayout :title="collectionName">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="collectionName" v-model:search="search" :error="error" />
                    <LeaderboardTable :columns="columns" :hiscores="hiscores" :filters="filters"
                                      @sort="sortBy" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
