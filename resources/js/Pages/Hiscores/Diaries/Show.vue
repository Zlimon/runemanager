<script setup>
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/LeaderboardTable.vue";

const props = defineProps({
    hiscores: {
        type: Array,
        required: true,
    },
});

const columns = [
    { label: 'Completed', key: 'completed', format: 'number' },
    { label: 'Easy', key: 'easy', format: 'number' },
    { label: 'Medium', key: 'medium', format: 'number' },
    { label: 'Hard', key: 'hard', format: 'number' },
    { label: 'Elite', key: 'elite', format: 'number' },
];

const search = ref('');
const filteredHiscores = computed(() => {
    if (!search.value) {
        return props.hiscores;
    }

    const needle = search.value.toLowerCase();
    return props.hiscores.filter((hiscore) =>
        hiscore.account.username.toLowerCase().includes(needle),
    );
});
</script>

<template>
    <AppLayout title="Achievement Diaries">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader title="Achievement Diaries" v-model:search="search" />
                    <LeaderboardTable :columns="columns" :rows="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
