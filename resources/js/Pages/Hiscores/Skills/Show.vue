<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/LeaderboardTable.vue";

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
        type: Array,
        required: true,
    },
});

const overall = props.skillSlug === 'overall';
const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: overall ? 'Total level' : 'Level', key: 'level', format: 'number' },
    { label: overall ? 'Total XP' : 'XP', key: 'xp', format: 'number' },
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
    <AppLayout :title="skillName">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="skillName" v-model:search="search" />
                    <LeaderboardTable :columns="columns" :rows="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
