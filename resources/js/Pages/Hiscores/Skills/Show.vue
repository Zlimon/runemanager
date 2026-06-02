<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import HiscoresTable from "@/Pages/Hiscores/Partials/HiscoresTable.vue";

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

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Level', key: 'level' },
    { label: 'XP', key: 'xp', format: 'number' },
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
                    <HiscoresTable :columns="columns" :hiscores="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
