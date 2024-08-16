<script setup>
import {ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from '@inertiajs/vue3';
import Loader from "@/Components/Loader.vue";
import CollectionLog from "@/Components/CollectionLog.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

let accounts = ref([]);
let search = ref('');
let payload = {};
let perPage = ref(10);
let searchAccountsLoading = ref(false);
let showSearchAccountsDropdown = ref(true);

watch(search, debounce(function (value) {
    payload = {
        ...payload,
        'search': value,
    };

    searchAccounts(payload)
}, 500));

const searchAccounts = (query, load = true) => {
    searchAccountsLoading.value = load;

    query = {
        ...payload,
        'per_page': perPage.value,
    };

    axios
        .post(route('api.accounts.search'), query)
        .then((response) => {
            accounts.value = response.data;
        }).catch(error => {
        console.error(error)
    }).finally(() => {
        searchAccountsLoading.value = false;
    });
};
</script>

<template>
    <AppLayout title="Account">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <div class="flex flex-col"
                         @mouseover="showSearchAccountsDropdown = true"
                         @mouseleave="showSearchAccountsDropdown = false">
                        <div class="flex flex-wrap items-center justify-end flex-column md:flex-row">
                            <label for="table-search" class="sr-only">
                                Search other accounts
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 flex items-center rtl:inset-r-0 start-0 ps-3">
                                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400"aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <TextInput v-model="search"
                                           type="search"
                                           id="table-search-users"
                                           class="block w-80 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm text-gray-900 ps-10 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                           placeholder="Search other accounts"
                                />
                            </div>
                        </div>

                        <div class="relative z-50 flex justify-end">
                            <div v-if="showSearchAccountsDropdown && (accounts.data !== undefined && accounts.data.length > 0)"
                                 class="w-[calc(100%+4rem)] absolute overflow-y-auto rounded-b-lg rounded-tl-lg border-r-2 border-b-2 border-l-2 border-gray-800 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                <Link v-for="account in accounts.data"
                                      :href="route('accounts.show', account)"
                                      class="flex flex-row items-center border-b border-gray-300 py-4 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <img :src="`data:image/jpeg;base64,${account.icon}`"
                                         class="m-2 h-8 w-8 object-contain">
                                    <div class="flex flex-col">
                                        <div class="flex items-center">
                                            <img v-if="account.account_type === 'ironman'"
                                                 :src="`/images/ironman.png`"
                                                 class="h-6 w-6 object-contain">
                                            <img v-else-if="account.account_type !== 'normal'"
                                                 :src="`/images/${account.account_type}_ironman.png`"
                                                 class="h-6 w-6 object-contain">
                                            <p>
                                                {{ account.username }}
                                            </p>
                                        </div>

                                        <div class="flex items-center">
                                            <img src="/images/skill/total.webp"
                                                 class="h-6 w-6 object-contain">
                                            <p class="font-normal text-gray-700 dark:text-gray-400">
                                                {{ account.level }}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 card-lg resource-pack-dialog">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <div class="flex items-center justify-center header-chatbox-sword">
                                <img v-if="account.account_type === 'ironman'"
                                     :src="`/images/ironman.png`"
                                     class="h-8 w-8 object-contain">
                                <img v-else-if="account.account_type !== 'normal'"
                                     :src="`/images/${account.account_type}_ironman.png`"
                                     class="h-8 w-8 object-contain">
                                <h3>
                                    {{ account.username }}
                                </h3>
                            </div>

                            <div class="m-6 flex flex-col items-center md:max-w-xl md:flex-row lg:m-8">
                                <img :src="`data:image/jpeg;base64,${account.icon}`"
                                     class="m-4 h-16 w-16 object-contain">
                                <div class="flex flex-col justify-between p-4 leading-normal">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="col-span-1">
                                            <label for="rank" class="block text-sm font-medium text-gray-700 dark:text-white">
                                                Rank
                                            </label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                                {{ account.rank }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="xp" class="block text-sm font-medium text-gray-700 dark:text-white">
                                                Total XP
                                            </label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                                {{ account.xp.toLocaleString('en-US') }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="level" class="block text-sm font-medium text-gray-700 dark:text-white">
                                                Total level
                                            </label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                                {{ account.level }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="joined" class="block text-sm font-medium text-gray-700 dark:text-white">
                                                Joined
                                            </label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                                {{ account.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <h3 class="text-center header-chatbox-sword">
                                Skills
                            </h3>

                            <div class="mx-auto mt-6">
                                <ul class="grid grid-cols-8 gap-1">
                                    <li>
                                        <Link :href="route('accounts.index')"
                                              data-tooltip-target="total-tooltip-bottom"
                                              data-tooltip-placement="bottom"
                                              type="button"
                                              class="flex items-center justify-center gap-2 rounded-lg bg-gray-100 p-1 text-gray-900 dark:bg-gray-700 dark:text-white">
                                            <img src="/images/skill/total.webp"
                                                 class="h-6 w-6 object-contain"/>
                                            <span class="text-xs font-semibold capitalize">
                                                {{ account.level }}
                                            </span>
                                        </Link>

                                        <div id="total-tooltip-bottom"
                                             role="tooltip"
                                             class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                            <p>
                                                Total level
                                            </p>
                                            <p>
                                                {{ account.xp.toLocaleString('en-US') }}
                                            </p>
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </li>
                                    <li v-for="skill in account.skills" :key="skill.name">
                                        <Link :href="route('hiscores.skills.index', skill.slug)"
                                              :data-tooltip-target="`${skill.slug}-tooltip-bottom`"
                                              data-tooltip-placement="bottom"
                                              type="button"
                                              class="flex items-center justify-center gap-2 rounded-lg bg-gray-100 p-1 text-gray-900 dark:bg-gray-700 dark:text-white">
                                            <img :src="`/images/skill/${skill.slug}.webp`"
                                                 class="h-6 w-6 object-contain"/>
                                            <span class="text-xs font-semibold capitalize">
                                                {{ skill.level }}
                                            </span>
                                        </Link>

                                        <div :id="`${skill.slug}-tooltip-bottom`"
                                             role="tooltip"
                                             class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                            <p>
                                                {{ skill.name }}
                                            </p>
                                            <p>
                                                {{ skill.xp.toLocaleString('en-US') }}
                                            </p>
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <h3 class="mt-6 text-center header-chatbox-sword">
                                Collection Log
                            </h3>

                            <div class="mt-6">
                                <CollectionLog :accountProp="account"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
