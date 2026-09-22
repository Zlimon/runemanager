<script setup>
import { computed, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from "@/Components/Cards/Card.vue";
import ClickableCard from "@/Components/Cards/ClickableCard.vue";
import Search from "@/Components/Form/Search.vue";
import ItemSlot from "@/Components/Game/Items/ItemSlot.vue";
import Badge from "@/Components/Display/Badge.vue";

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
                    <h1 class="text-2xl font-bold text-base-content">{{ source }}</h1>
                    <Badge v-if="category">{{ category }}</Badge>
                    <div class="ml-auto w-full max-w-xs">
                        <Search v-model="search" placeholder="Search by username" />
                    </div>
                </div>

                <ul class="space-y-3">
                    <li v-for="entry in filteredHiscores" :key="entry.account.username">
                        <ClickableCard :href="route('accounts.show', entry.account)"
                                       :class="{ 'ring-2 pack-ring-soft': entry.account.user_id === ownUserId }">
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex items-center gap-3 whitespace-nowrap font-semibold text-base-content">
                                    <span class="text-base-content/50">#{{ entry.rank }}</span>
                                    <img :src="`data:image/jpeg;base64,${entry.account.icon}`"
                                         class="h-9 w-9 object-contain" alt="">
                                    {{ entry.account.username }}
                                </span>
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
                        </ClickableCard>
                    </li>

                    <li v-if="filteredHiscores.length === 0">
                        <Card padding="p-8" class="text-center text-base-content/60">
                            No accounts match this search
                        </Card>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
