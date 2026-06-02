<script setup>
import { Link } from '@inertiajs/vue3';

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
    <table class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Name</th>
                <th v-for="column in columns" :key="column.key" scope="col" class="px-6 py-3">
                    {{ column.label }}
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="hiscores.length === 0">
                <td :colspan="columns.length + 1"
                    class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    No hiscores found
                </td>
            </tr>
            <tr v-for="hiscore in hiscores" :key="hiscore.account.username"
                class="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600">
                <th scope="row">
                    <Link :href="route('accounts.show', hiscore.account)"
                          class="flex items-center whitespace-nowrap px-6 py-4 text-gray-900 dark:text-white">
                        <img :src="`data:image/jpeg;base64,${hiscore.account.user.icon}`"
                             class="h-10 w-10 object-contain"
                             alt="">
                        <div class="ps-3">
                            <div class="text-base font-semibold">{{ hiscore.account.username }}</div>
                        </div>
                    </Link>
                </th>
                <td v-for="column in columns" :key="column.key" class="px-6 py-4">
                    {{ formatValue(hiscore[column.key], column.format) }}
                </td>
            </tr>
        </tbody>
    </table>
</template>
