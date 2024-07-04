<script setup>
import {onMounted, ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';

let accounts = ref([]);
let search = ref('');
let payload = {};
let perPage = ref(10);
let loading = ref(false);

onMounted(() => {
    searchAccounts(payload);
});

watch(search, debounce(function (value) {
    value = value.replaceAll(' ', '-').toLowerCase();

    payload = {
        ...payload,
        'search': value,
    };

    searchAccounts(payload)
}, 500));

const searchAccounts = (query, load = true) => {
    loading.value = load;

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
            loading.value = false;
    });
};
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center header-chatbox-sword">
                            Search for accounts
                        </h1>

                        <div class="max-w-md mx-auto mt-6 lg:mt-6">
                            <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>

                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <input v-model="search"
                                       type="search"
                                       id="default-search"
                                       class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                       :placeholder="accounts.data !== undefined && accounts.data.length > 0 ? accounts.data[Math.floor(Math.random() * accounts.data.length)].username : 'No results'" />
                                <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Search</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-12">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200 grid sm:grid-cols-3">
                        <div v-for="account in accounts.data" :key="account.id">
                            <a href="#" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 m-6 lg:m-8">
                                <img :src="`https://www.osrsbox.com/osrsbox-db/items-icons/${account.user.icon_id}.png`"
                                     class="object-contain h-16 w-16 m-4">
                                <div class="flex flex-col justify-between p-4 leading-normal">
                                    <div class="flex">
                                        <img v-if="account.account_type === 'ironman'"
                                             :src="`/images/ironman.png`"
                                             class="object-contain h-8 w-8">
                                        <img v-else-if="account.account_type !== 'normal'"
                                             :src="`/images/${account.account_type}_ironman.png`"
                                             class="object-contain h-8 w-8">
                                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ account.username }}</h5>
                                    </div>

                                    <div class="flex">
                                        <img src="/images/skill/total.webp"
                                             class="object-contain h-8 w-8">
                                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ account.level }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
