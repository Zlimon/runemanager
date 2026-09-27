<script setup>
import Card from '@/Components/Cards/Card.vue';
import AccountName from '@/Pages/Progress/Partials/AccountName.vue';

/* One EHP or EHB board: members ranked by their current efficient hours. */
defineProps({
    title: { type: String, required: true },
    rows: { type: Array, default: () => [] },
});

const hours = (value) => `${Number(value).toLocaleString('en-US', { maximumFractionDigits: 1 })} h`;
</script>

<template>
    <Card padding="p-4">
        <h2 class="mb-3 text-xl font-bold">{{ title }}</h2>
        <ol v-if="rows.length" class="divide-y divide-base-content/10">
            <li v-for="(row, index) in rows" :key="row.account_id ?? `${index}-${row.username}`"
                class="flex items-center gap-3 px-2 py-2">
                <span class="w-6 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                <AccountName class="flex-1" :username="row.username" :account-type="row.account_type"
                             :account-id="row.account_id" />
                <span class="text-sm font-semibold">{{ hours(row.value) }}</span>
            </li>
        </ol>
        <p v-else class="py-8 text-center text-sm text-base-content/60">No one on this board yet.</p>
    </Card>
</template>
