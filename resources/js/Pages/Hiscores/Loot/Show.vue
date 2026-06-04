<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import HiscoresTable from "@/Pages/Hiscores/Partials/HiscoresTable.vue";

const props = defineProps({
    hiscores: {
        type: Array,
        required: true,
    },
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Total value', key: 'total_value', format: 'number' },
    { label: 'Drops', key: 'drops', format: 'number' },
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
    <AppLayout title="Loot">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader title="Loot" v-model:search="search" />
                    <HiscoresTable :columns="columns" :hiscores="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
