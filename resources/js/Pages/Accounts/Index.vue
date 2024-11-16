<script setup>
import {computed, onMounted, ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import {Link, useForm} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    accountTypesProp: Array,
});

let accountTypes = ref(props.accountTypesProp);
let accounts = ref([]);

const formatedAccountTypeNames = computed({
    get: () => {
        return accountTypes.value.map((accountType) => {
            return formatAccountTypeName(accountType);
        });
    }
})

const formatAccountTypeName = (accountType) => {
    return accountType.replace(/_/g, " ").replace(/(^\w|\s\w)(\S*)/g, (_, m1, m2) => m1.toUpperCase() + m2.toLowerCase());
};

//----------------------------------------------------;
// searchAccounts
//----------------------------------------------------;
onMounted(() => {
    searchAccounts();
});

let searchAccountForm = useForm({
    username: '',
    account_types: [],
});

const appendAccountTypeSearch = (accountType) => {
    if (accountType === 'All') {
        clearAccountTypeSearch();
        return;
    }

    // Check if the account type is already in the search
    if (searchAccountForm.account_types.includes(accountType)) {
        return;
    }

    searchAccountForm.account_types.push(accountType);
};

const removeAccountTypeSearch = (accountType) => {
    searchAccountForm.account_types = searchAccountForm.account_types.filter((item) => item !== accountType);
};

const clearAccountTypeSearch = () => {
    document.getElementById('account-type').value = 'All';

    searchAccountForm.account_types = [];
};

watch(() => [searchAccountForm.username, searchAccountForm.account_types, searchAccountForm.per_page], debounce((value) => {
    searchAccounts();
}, 500), {deep: true});

let loading = ref(false);

const searchAccounts = (load = true) => {
    loading.value = load;

    searchAccountForm.per_page = 16;

    axios.post(route('api.accounts.search'), searchAccountForm)
        .then((response) => {
            accounts.value = response.data;

            searchAccountForm.errors = {};
        }).catch(error => {
            console.log(error)
            searchAccountForm.errors = error.response.data.errors || {};

            console.error(error)
    }).finally(() => {
        loading.value = false;
    });
};
//----------------------------------------------------;
// End of searchAccounts
//----------------------------------------------------;
</script>

<template>
    <AppLayout title="Profile">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="card-lg resource-pack-dialog !shadow-lg">
                    <h3 class="text-left header-chatbox-sword">Search for accounts</h3>

                    <div class="mt-2 grid grid-cols-2 gap-12">
                        <div class="col-span-1 max-w-md">
                            <InputLabel for="searchAccountForm-username" value="Search for any account by username"/>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 flex items-center start-0 ps-3">
                                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <TextInput v-model="searchAccountForm.username"
                                           type="search"
                                           id="searchAccountForm-search"
                                           name="searchAccountForm-search"
                                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 ps-10 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                           :error="searchAccountForm.errors.username !== undefined"
                                />
                            </div>
                            <InputError v-if="searchAccountForm.errors.username !== undefined"
                                        :messages="searchAccountForm.errors.username"/>
                        </div>

                        <div class="col-span-1 max-w-md">
                            <div>
                                <InputLabel for="account-type" value="Filter by account type"/>
                                <Select
                                    id="account-type"
                                    :options="formatedAccountTypeNames"
                                    :optionObject=false
                                    optionDefault="All"
                                    @change="appendAccountTypeSearch($event.target.value)"
                                />
                                <InputError v-if="searchAccountForm.errors.account_types !== undefined"
                                            :messages="searchAccountForm.errors.account_types"/>

                                <div class="flex flex-wrap space-x-2">
                                    <div v-if="searchAccountForm.account_types.length > 0"
                                         class="mt-2 flex items-center">
                                        <span @click="clearAccountTypeSearch"
                                              class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-800 hover:cursor-pointer dark:bg-gray-700 dark:text-gray-300">
                                            <svg class="h-2 w-2" aria-hidden="true"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 fill="none" viewBox="0 0 14 14">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                      stroke-linejoin="round" stroke-width="2"
                                                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                            </svg>
                                            <span class="sr-only">Clear account type search</span>
                                        </span>
                                    </div>

                                    <div v-for="accountType in searchAccountForm.account_types" :key="accountType"
                                         class="mt-2">
                                        <span :id="`badge-dismiss-${accountType}`"
                                              class="inline-flex grow items-center rounded bg-blue-100 px-2 py-1 text-sm font-medium text-blue-800 text-nowrap dark:bg-blue-900 dark:text-blue-300">
                                            {{ formatAccountTypeName(accountType) }}
                                            <button @click="removeAccountTypeSearch(accountType)"
                                                    type="button"
                                                    class="inline-flex items-center rounded-sm bg-transparent p-1 text-sm text-blue-400 ms-2 hover:bg-blue-200 hover:text-blue-900 dark:hover:bg-blue-800 dark:hover:text-blue-300"
                                                    :data-dismiss-target="`#badge-dismiss-${accountType}`"
                                                    aria-label="Remove">
                                                <svg class="h-2 w-2" aria-hidden="true"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     fill="none" viewBox="0 0 14 14">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                          stroke-linejoin="round" stroke-width="2"
                                                          d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                </svg>
                                                <span class="sr-only">Remove {{ accountType }} account type</span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid sm:grid-cols-4 gap-4">
                    <div v-for="account in accounts.data" :key="account.id">
                        <Link :href="route('accounts.show', account)"
                              class="flex flex-col items-center md:flex-row md:max-w-xl hover:bg-beige-200 dark:hover:bg-gray-700 card-sm resource-pack-dialog !shadow-lg">
                            <img :src="`data:image/jpeg;base64,${account.icon}`"
                                 class="h-16 w-16 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500">
                            <div class="flex flex-col justify-between p-4 leading-normal">
                                <div class="flex items-center space-x-1">
                                    <img v-if="account.account_type === 'ironman'"
                                         :src="`/images/ironman.png`"
                                         class="h-6 w-6 object-contain">
                                    <img v-else-if="account.account_type !== 'normal'"
                                         :src="`/images/${account.account_type}_ironman.png`"
                                         class="h-6 w-6 object-contain">
                                    <h5 class="">{{ account.username }}</h5>
                                </div>

                                <div class="flex items-center space-x-1">
                                    <img src="/images/skill/total.webp"
                                         class="h-6 w-6 object-contain">
                                    <p class="font-normal text-gray-700 dark:text-gray-400">{{ account.level }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
