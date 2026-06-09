<script setup>
import { computed, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import Search from "@/Components/Search.vue";
import ItemSlot from "@/Components/Game/ItemSlot.vue";

const props = defineProps({
    source: {
        type: String,
        required: true,
    },
    category: {
        type: String,
        default: null,
    },
    hiscores: {
        type: Array,
        required: true,
    },
});

const ownUserId = computed(() => usePage().props.auth?.user?.id ?? null);

const formatValue = (gp) => {
    if (!gp) {
        return '0';
    }
    if (gp >= 1_000_000) {
        return `${(gp / 1_000_000).toFixed(1)}M`;
    }
    if (gp >= 1_000) {
        return `${(gp / 1_000).toFixed(1)}K`;
    }
    return gp.toLocaleString('en-US');
};

const search = ref('');
const filteredHiscores = computed(() => {
    if (!search.value) {
        return props.hiscores;
    }

    const needle = search.value.toLowerCase();
    return props.hiscores.filter((entry) =>
        entry.account.username.toLowerCase().includes(needle),
    );
});
</script>

<template>
    <AppLayout :title="source">
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <Link :href="route('hiscores.loot.index')"
                          class="text-sm text-base-content/60 hover:text-base-content">
                        ← Loot
                    </Link>
                    <h1 class="text-2xl font-bold dark:text-white">{{ source }}</h1>
                    <span v-if="category" class="badge badge-neutral">{{ category }}</span>
                    <div class="ml-auto w-full max-w-xs">
                        <Search v-model="search" placeholder="Search by username" />
                    </div>
                </div>

                <ul class="space-y-3">
                    <li v-for="entry in filteredHiscores" :key="entry.account.username"
                        class="rounded p-4 pack-bg-card resource-pack-border"
                        :class="{ 'ring-2 ring-primary/40': entry.account.user_id === ownUserId }">
                        <div class="flex items-center justify-between gap-3">
                            <Link :href="route('accounts.show', entry.account)"
                                  class="flex items-center gap-3 whitespace-nowrap font-semibold text-base-content">
                                <span class="text-base-content/50">#{{ entry.rank }}</span>
                                <img :src="`data:image/jpeg;base64,${entry.account.user?.icon ?? entry.account.icon}`"
                                     class="h-9 w-9 object-contain" alt="">
                                {{ entry.account.username }}
                            </Link>
                            <div class="text-right">
                                <p class="font-semibold text-base-content">{{ formatValue(entry.total_value) }} gp</p>
                                <p class="text-xs text-base-content/50">{{ entry.drops.toLocaleString('en-US') }} drops</p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <ItemSlot v-for="item in entry.items" :key="item.id"
                                      :icon="item.icon" :name="item.name ?? `Item ${item.id}`"
                                      :quantity="item.quantity" />
                        </div>
                    </li>

                    <li v-if="filteredHiscores.length === 0"
                        class="rounded p-8 text-center text-base-content/60 pack-bg-card resource-pack-border">
                        No accounts match this search
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
