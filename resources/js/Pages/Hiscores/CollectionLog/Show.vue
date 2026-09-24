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
    { label: 'Slots unlocked', key: 'obtained', format: 'number' },
    { label: 'Total', key: 'total', format: 'number' },
];

const { search, sortBy, error } = useHiscoreBoard(() => props.filters);
</script>

<template>
    <AppLayout title="Collection Log">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader title="Collection Log" v-model:search="search" :error="error" />
                    <LeaderboardTable :columns="columns" :hiscores="hiscores" :filters="filters"
                                      @sort="sortBy" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
