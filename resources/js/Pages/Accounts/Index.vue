<script setup>
import {computed, onMounted, ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import {Link, useForm} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import InputError from "@/Components/InputError.vue";
import Icon from "@/Pages/Accounts/Components/Icon.vue";
import TextInputIcon from "@/Components/TextInputIcon.vue";
import Search from "@/Components/Search.vue";

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
    per_page: 16,
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
    document.getElementById('account_types').value = 'All';

    searchAccountForm.account_types = [];
};

watch(() => [searchAccountForm.username, searchAccountForm.account_types, searchAccountForm.per_page], debounce((value) => {
    searchAccounts();
}, 500), {deep: true});

let loading = ref(false);

const searchAccounts = (load = true) => {
    loading.value = load;

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
                <h3 class="text-left header-chatbox-sword">Search for accounts</h3>

                <div class="mt-2 grid grid-cols-2 gap-12">
                    <div class="col-span-1 max-w-md">
                        <InputLabel for="searchAccountForm-username"
                                    value="Search for any account by username"/>
                        <Search id="searchAccountForm-username"
                                v-model="searchAccountForm.username"
                                :error="searchAccountForm.errors.username !== undefined"
                                @search="searchAccounts"/>
                        <InputError v-if="searchAccountForm.errors.username !== undefined"
                                    :messages="searchAccountForm.errors.username"/>
                    </div>

                    <div class="col-span-1 max-w-md">
                        <div>
                            <InputLabel for="account_types"
                                        value="Filter by account type"/>
                            <Select id="account_types"
                                    :optionObject=false
                                    :options="formatedAccountTypeNames"
                                    optionDefault="All"
                                    @change="appendAccountTypeSearch($event.target.value)"
                            />
                            <InputError v-if="searchAccountForm.errors.account_types !== undefined"
                                        :messages="searchAccountForm.errors.account_types"/>

                            <div class="flex flex-wrap space-x-2">
                                <div v-if="searchAccountForm.account_types.length > 0"
                                     class="mt-2 flex items-center">
                                    <div class="badge badge-info"
                                         @click="clearAccountTypeSearch()">
                                        <svg
                                            class="inline-block h-4 w-4 stroke-current"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6 18L18 6M6 6l12 12"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div v-for="accountType in searchAccountForm.account_types"
                                     :key="accountType"
                                     class="mt-2">
                                    <div class="badge badge-primary"
                                         @click="removeAccountTypeSearch(accountType)">
                                        {{ formatAccountTypeName(accountType) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid sm:grid-cols-4 gap-4">
                    <div v-for="account in accounts.data" :key="account">
                        <Link :href="route('accounts.show', account)"
                              class="flex flex-col items-center md:flex-row md:max-w-xl box hover:bg-base-200 px-2 resource-pack-dialog">
                            <Icon :accountProp="account"/>
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
                                    <img class="h-6 w-6 object-contain"
                                         src="/images/skill/total.webp">
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
