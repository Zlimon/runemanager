<script setup>
import {ref, watch} from "vue";
import debounce from 'lodash/debounce';
import {Link, useForm, usePage} from '@inertiajs/vue3';
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Search from "@/Components/Search.vue";

let accounts = ref([]);
let showSearchAccountsDropdown = ref(true);

let searchAccountForm = useForm({
    username: '',
});

watch(() => searchAccountForm.username, debounce((value) => {
    searchAccounts();
}, 500));

let loading = ref(false);

const searchAccounts = (load = true) => {
    loading.value = load;

    searchAccountForm.per_page = 10;

    axios.post(route('api.accounts.search'), searchAccountForm)
        .then((response) => {
            accounts.value = response.data;

            searchAccountForm.errors = {};
        }).catch(error => {
        searchAccountForm.errors = error.response.data.errors || {};

        console.error(error)
    }).finally(() => {
        loading.value = false;

        showSearchAccountsDropdown.value = true;
    });
};
</script>

<template>
    <div class="flex flex-col"
         @mouseleave="showSearchAccountsDropdown = false"
         @mouseover="showSearchAccountsDropdown = true">
        <div class="flex flex-wrap items-center justify-end flex-column md:flex-row">
            <InputLabel class="sr-only"
                        for="searchAccountForm-username"
                        value="Search for any account by username"/>
            <Search id="searchAccountForm-username"
                    v-model="searchAccountForm.username"
                    :error="searchAccountForm.errors.username !== undefined"
                    @search="searchAccounts"/>
            <InputError v-if="searchAccountForm.errors.username !== undefined"
                        :messages="searchAccountForm.errors.username"/>
        </div>
        <InputError v-if="searchAccountForm.errors.username !== undefined"
                    :messages="searchAccountForm.errors.username"/>

        <div class="relative z-50 flex justify-end">
            <div
                v-if="showSearchAccountsDropdown && (accounts.data !== undefined && accounts.data.length > 0)"
                class="w-[calc(100%+4rem)] absolute overflow-y-auto bg-beige-600 border-2 border-beige-700 rounded-lg dark:border-gray-700 dark:bg-gray-800 ">
                <div v-for="account in accounts.data" :key="account.id">
                    <Link :href="route('accounts.show', account)"
                          class="flex flex-row items-center border-b p-2 border-beige-700 space-x-2 hover:bg-beige-200 dark:hover:bg-gray-700">
                        <img :src="`data:image/jpeg;base64,${account.icon}`"
                             class="h-16 w-16 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500">
                        <div class="flex flex-col justify-between">
                            <div class="flex items-center space-x-1">
                                <img v-if="account.account_type === 'ironman'"
                                     :src="`/images/ironman.png`"
                                     class="h-6 w-6 object-contain">
                                <img v-else-if="account.account_type !== 'normal'"
                                     :src="`/images/${account.account_type}_ironman.png`"
                                     class="h-6 w-6 object-contain">
                                <p class="text-xl">
                                    {{ account.username }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-1">
                                <img class="h-6 w-6 object-contain"
                                     src="/images/skill/total.webp">
                                <p class="font-normal text-gray-700 text-md dark:text-gray-400">
                                    {{ account.level }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
