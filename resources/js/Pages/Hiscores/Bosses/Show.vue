<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import HiscoresTable from "@/Pages/Hiscores/Partials/HiscoresTable.vue";

const props = defineProps({
    collectionNameProp: String,
    collectionSlugProp: String,
    hiscoresProp: Array,
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Score', key: 'kill_count', format: 'number' },
];

const search = ref('');
const filteredHiscores = computed(() => {
    if (!search.value) {
        return props.hiscoresProp;
    }

    const needle = search.value.toLowerCase();
    return props.hiscoresProp.filter((hiscore) =>
        hiscore.account.username.toLowerCase().includes(needle),
    );
});
</script>

<template>
    <AppLayout :title="collectionNameProp">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="collectionNameProp" v-model:search="search" />
                    <HiscoresTable :columns="columns" :hiscores="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
