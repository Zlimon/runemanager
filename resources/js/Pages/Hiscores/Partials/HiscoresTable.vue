<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    columns: {
        type: Array,
        required: true,
    },
    hiscores: {
        type: Array,
        required: true,
    },
});

const page = usePage();

// SPEC §7.3 — highlight the viewer's own accounts in the ranking.
const ownUserId = computed(() => page.props.auth?.user?.id ?? null);

const formatValue = (value, format) => {
    if (value === null || value === undefined) {
        return '';
    }

    if (format === 'number') {
        return Number(value).toLocaleString('en-US');
    }

    return value;
};
</script>

<template>
    <div class="overflow-x-auto rounded pack-bg-card resource-pack-border">
        <table class="table">
            <thead>
                <tr class="text-base-content/70">
                    <th>Name</th>
                    <th v-for="column in columns" :key="column.key">{{ column.label }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="hiscores.length === 0">
                    <td :colspan="columns.length + 1" class="py-8 text-center text-base-content/60">
                        No hiscores found
                    </td>
                </tr>
                <tr v-for="hiscore in hiscores" :key="hiscore.account.username"
                    :class="hiscore.account.user_id === ownUserId ? 'bg-primary/15' : 'hover:bg-base-200'">
                    <td>
                        <Link :href="route('accounts.show', hiscore.account)"
                              class="flex items-center gap-3 whitespace-nowrap font-semibold text-base-content">
                            <img :src="`data:image/jpeg;base64,${hiscore.account.user?.icon ?? hiscore.account.icon}`"
                                 class="h-9 w-9 object-contain" alt="">
                            {{ hiscore.account.username }}
                        </Link>
                    </td>
                    <td v-for="column in columns" :key="column.key">
                        {{ formatValue(hiscore[column.key], column.format) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
