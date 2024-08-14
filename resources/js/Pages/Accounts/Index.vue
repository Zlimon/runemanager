<script setup>
import {onMounted, ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import {Link} from '@inertiajs/vue3';

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
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="card-lg resource-pack-dialog">
                    <h3 class="text-center header-chatbox-sword">Search for accounts</h3>

                    <div class="max-w-md mx-auto mt-6 lg:mt-6">
                        <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>

                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <TextInput v-model="search"
                                       type="search"
                                       id="default-search"
                                       class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                       :placeholder="accounts.data !== undefined && accounts.data.length > 0 ? accounts.data[Math.floor(Math.random() * accounts.data.length)].username : 'No results'"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 mt-12">
                    <div v-for="account in accounts.data" :key="account.id">
                        <Link :href="route('accounts.show', account)"
                              class="flex flex-col items-center md:flex-row md:max-w-xl hover:bg-gray-100 dark:hover:bg-gray-700 m-6 lg:m-8 card-sm resource-pack-dialog">
                            <img :src="`https://www.osrsbox.com/osrsbox-db/items-icons/${account.icon_id}.png`"
                                 class="object-contain h-16 w-16 m-4">
                            <div class="flex flex-col justify-between p-4 leading-normal">
                                <div class="flex">
                                    <img v-if="account.account_type === 'ironman'"
                                         :src="`/images/ironman.png`"
                                         class="object-contain h-8 w-8">
                                    <img v-else-if="account.account_type !== 'normal'"
                                         :src="`/images/${account.account_type}_ironman.png`"
                                         class="object-contain h-8 w-8">
                                    <h5 class="">{{ account.username }}</h5>
                                </div>

                                <div class="flex">
                                    <img src="/images/skill/total.webp"
                                         class="object-contain h-8 w-8">
                                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ account.level }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
