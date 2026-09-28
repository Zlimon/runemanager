<script setup>
import { Deferred } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import Loader from '@/Components/Display/Loader.vue';
import AccountName from '@/Pages/Progress/Partials/AccountName.vue';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';

/*
 * The period's top gainer of every skill (the deferred `skillLeaders` prop);
 * skills nobody trained are absent.
 */
defineProps({
    leaders: { type: Array, default: null },
});

const { packIcon } = useResourcePackIcon();

const icon = (value) => (value?.kind ? packIcon(value.kind, value.slug) : value);

const compact = (value) => Math.round(value).toLocaleString('en-US', { notation: 'compact', maximumFractionDigits: 1 });
</script>

<template>
    <Card padding="p-4">
        <h2 class="mb-3 text-xl font-bold">Top gainer per skill</h2>

        <Deferred data="skillLeaders">
            <template #fallback>
                <div class="flex h-40 items-center justify-center">
                    <Loader :component="true" :loading="true" />
                </div>
            </template>

            <template #default="{ reloading }">
                <ul v-if="leaders?.length" class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3"
                    :class="{ 'opacity-50': reloading }">
                    <li v-for="row in leaders" :key="row.metric"
                        class="flex items-center gap-3 rounded border border-base-content/10 px-3 py-2">
                        <img v-if="row.icon" v-bind="icon(row.icon)" alt="" class="h-6 w-6 shrink-0 object-contain">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs uppercase tracking-wide text-base-content/60">{{ row.label }}</p>
                            <AccountName :username="row.username" :account-type="row.account_type"
                                         :account-id="row.account_id" />
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-success">+{{ compact(row.gained) }} xp</span>
                    </li>
                </ul>
                <p v-else class="py-8 text-center text-sm text-base-content/60">
                    Nobody trained a skill in this period yet.
                </p>
            </template>
        </Deferred>
    </Card>
</template>
