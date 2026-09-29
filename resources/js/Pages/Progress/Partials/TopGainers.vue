<script setup>
import { computed } from 'vue';
import { Deferred } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import Loader from '@/Components/Display/Loader.vue';
import AccountName from '@/Pages/Progress/Partials/AccountName.vue';
import MetricPicker from '@/Pages/Progress/Partials/MetricPicker.vue';

/*
 * The period's biggest gainers of one skill, boss or activity (the deferred
 * `topGainers` prop). The page owns the filters and reloads the prop.
 */
const props = defineProps({
    gainers: { type: Array, default: null },
    metric: { type: String, default: 'overall' },
    metrics: { type: Object, required: true },
});

defineEmits(['update:metric']);

// What a gain counts: xp for skills, kills for bosses, a score otherwise.
const unit = computed(() => {
    if ((props.metrics.skills ?? []).some((option) => option.metric === props.metric)) {
        return ' xp';
    }

    return (props.metrics.bosses ?? []).some((option) => option.metric === props.metric) ? ' kills' : '';
});

const format = (value) => `+${Math.round(value).toLocaleString('en-US')}${unit.value}`;
</script>

<template>
    <Card padding="p-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold">Top gainers</h2>
            <MetricPicker :model-value="metric" :metrics="metrics" @update:model-value="$emit('update:metric', $event)" />
        </div>

        <Deferred data="topGainers">
            <template #fallback>
                <div class="flex h-40 items-center justify-center">
                    <Loader :component="true" :loading="true" />
                </div>
            </template>

            <template #default="{ reloading }">
                <ol v-if="gainers?.length" class="divide-y divide-base-content/10" :class="{ 'opacity-50': reloading }">
                    <li v-for="(row, index) in gainers" :key="row.account_id ?? `${index}-${row.username}`"
                        class="flex items-center gap-3 px-2 py-2">
                        <span class="w-6 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                        <AccountName class="flex-1" :username="row.username" :account-type="row.account_type"
                                     :account-id="row.account_id" />
                        <span class="text-sm font-semibold text-success">{{ format(row.gained) }}</span>
                    </li>
                </ol>
                <p v-else class="py-8 text-center text-sm text-base-content/60">
                    Nobody gained anything in this period yet.
                </p>
            </template>
        </Deferred>
    </Card>
</template>
