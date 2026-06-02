<script setup>
import { computed, ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import HiscoresHeader from "@/Pages/Hiscores/Partials/HiscoresHeader.vue";
import HiscoresTable from "@/Pages/Hiscores/Partials/HiscoresTable.vue";

const props = defineProps({
    skillNameProp: String,
    skillSlugProp: String,
    hiscoresProp: Array,
});

const columns = [
    { label: 'Rank', key: 'rank', format: 'number' },
    { label: 'Level', key: 'level' },
    { label: 'XP', key: 'xp', format: 'number' },
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
    <AppLayout :title="skillNameProp">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <HiscoresHeader :title="skillNameProp" v-model:search="search" />
                    <HiscoresTable :columns="columns" :hiscores="filteredHiscores" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
