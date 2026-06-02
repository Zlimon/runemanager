<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import HiscoresTable from "@/Pages/Hiscores/Partials/HiscoresTable.vue";

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
        type: Array,
        required: true,
    },
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Score', key: 'kill_count', format: 'number' },
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
    <AppLayout :title="collectionName">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="collectionName" v-model:search="search" />
                    <HiscoresTable :columns="columns" :hiscores="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
