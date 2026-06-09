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
    { label: 'Slots unlocked', key: 'obtained', format: 'number' },
    { label: 'Total', key: 'total', format: 'number' },
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
    <AppLayout title="Collection Log">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader title="Collection Log" v-model:search="search" />
                    <LeaderboardTable :columns="columns" :rows="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
