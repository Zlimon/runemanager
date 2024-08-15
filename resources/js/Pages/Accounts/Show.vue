<script setup>
import {ref, onMounted} from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/vue3';
import Loader from "@/Components/Loader.vue";
import CollectionLog from "@/Components/CollectionLog.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <AppLayout title="Account">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-end flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 pb-4">
                    <label for="table-search" class="sr-only">Search other accounts</label>
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
                               placeholder="Search other accounts">
                    </div>
                </div>

                <div class="card-lg resource-pack-dialog">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <div class="flex justify-center items-center header-chatbox-sword">
                                <img v-if="account.account_type === 'ironman'"
                                     :src="`/images/ironman.png`"
                                     class="object-contain h-8 w-8">
                                <img v-else-if="account.account_type !== 'normal'"
                                     :src="`/images/${account.account_type}_ironman.png`"
                                     class="object-contain h-8 w-8">
                                <h3>{{ account.username }}</h3>
                            </div>

                            <div class="flex flex-col items-center md:flex-row md:max-w-xl m-6 lg:m-8">
                                <img :src="`data:image/jpeg;base64,${account.icon}`"
                                     class="object-contain h-16 w-16 m-4">
                                <div class="flex flex-col justify-between p-4 leading-normal">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="col-span-1">
                                            <label for="rank" class="block text-sm font-medium text-gray-700 dark:text-white">Rank</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.rank }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="xp" class="block text-sm font-medium text-gray-700 dark:text-white">Total XP</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.xp.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="level" class="block text-sm font-medium text-gray-700 dark:text-white">Total level</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.level }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="joined" class="block text-sm font-medium text-gray-700 dark:text-white">Joined</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.created_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <h3 class="text-center header-chatbox-sword">Skills</h3>

                            <div class="mx-auto mt-6">
                                <ul class="grid grid-cols-8 gap-1">
                                    <li>
                                        <div class="flex items-center justify-center gap-2 rounded-lg p-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <img :src="`/images/skill/total.webp`"
                                                 class="h-6 w-6 object-contain"/>
                                            <div class="font-semibold capitalize text-xs">{{ account.level }}</div>
                                        </div>
                                    </li>
                                    <li v-for="skill in account.skills" :key="skill.id">
                                        <Link :href="route('hiscores.skills.index', skill.slug)"
                                              class="flex items-center justify-center gap-2 rounded-lg p-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <img :src="`/images/skill/${skill.slug}.webp`"
                                                 class="h-6 w-6 object-contain"/>
                                            <div class="font-semibold capitalize text-xs">{{ skill.level }}</div>
                                        </Link>
                                    </li>
                                </ul>
                            </div>

                            <h3 class="text-center header-chatbox-sword mt-6">Collection Log</h3>

                            <div class="mt-6">
                                <CollectionLog :accountProp="account" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
