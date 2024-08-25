<script setup>
import {ref} from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    bossProp: String,
    hiscoresProp: Object,
});

let hiscores = ref(props.hiscoresProp);
</script>

<template>
    <AppLayout title="Profile">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <div class="flex items-center justify-between flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 pb-4">
                        <h1>{{ bossProp }}</h1>

                        <label for="table-search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="text" id="table-search-users"
                                   class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="Search for users">
                        </div>
                    </div>
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Rank
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Score
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Obtained
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="hiscore in hiscores"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <th scope="row">
                                    <Link :href="route('accounts.show', hiscore.account)"
                                          class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                        <img :src="`data:image/jpeg;base64,${hiscore.account.user.icon}`"
                                             class="object-contain h-10 w-10">
                                        <div class="ps-3">
                                            <div class="text-base font-semibold">{{ hiscore.account.username }}</div>
                                        </div>
                                    </Link>
                                </th>
                                <td class="px-6 py-4">
                                    {{ hiscore.rank.toLocaleString('en-US') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ hiscore.kill_count.toLocaleString('en-US') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ hiscore.obtained.toLocaleString('en-US') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
