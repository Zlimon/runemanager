<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import LeaderboardTable from "@/Components/LeaderboardTable.vue";

const props = defineProps({
    hiscores: {
        type: Array,
        required: true,
    },
    sources: {
        type: Array,
        default: () => [],
    },
    selectedSource: {
        type: String,
        default: null,
    },
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Total value', key: 'total_value', format: 'number' },
    { label: 'Drops', key: 'drops', format: 'number' },
];

const title = computed(() => props.selectedSource ?? 'Loot');

const source = ref(props.selectedSource ?? '');
const changeSource = () => {
    router.get(route('hiscores.loot.index'), source.value ? { source: source.value } : {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

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
                    <HiscoresHeader :title="title" v-model:search="search">
                        <div class="w-full md:w-64">
                            <select v-model="source" class="select select-bordered w-full"
                                    @change="changeSource">
                                <option value="">All sources</option>
                                <option v-for="name in sources" :key="name" :value="name">
                                    {{ name }}
                                </option>
                            </select>
                        </div>
                    </HiscoresHeader>
                    <LeaderboardTable :columns="columns" :rows="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
