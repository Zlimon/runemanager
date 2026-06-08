<script setup>
import { ref, watch } from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import Search from "@/Components/Search.vue";
import AccountCard from "@/Components/AccountCard.vue";

const props = defineProps({
    accountTypes: {
        type: Array,
        required: true,
    },
    accounts: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const formatAccountTypeName = (accountType) =>
    accountType.replace(/_/g, " ").replace(/(^\w|\s\w)(\S*)/g, (_, m1, m2) => m1.toUpperCase() + m2.toLowerCase());

const form = ref({
    username: props.filters.username ?? '',
    account_types: props.filters.account_types ?? [],
    per_page: props.filters.per_page ?? 16,
});

const reload = debounce(() => {
    router.get(route('accounts.index'), form.value, {
        only: ['accounts', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(form, reload, { deep: true });

const appendAccountTypeSearch = (accountType) => {
    if (accountType === 'All') {
        clearAccountTypeSearch();
        return;
    }

    if (form.value.account_types.includes(accountType)) {
        return;
    }

    form.value.account_types.push(accountType);
};

const removeAccountTypeSearch = (accountType) => {
    form.value.account_types = form.value.account_types.filter((item) => item !== accountType);
};

const clearAccountTypeSearch = () => {
    form.value.account_types = [];
};
</script>

<template>
    <AppLayout title="Accounts">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <h3 class="text-left header-chatbox-sword">Search for accounts</h3>

                <div class="mt-2 grid grid-cols-2 gap-12">
                    <div class="col-span-1 max-w-md">
                        <InputLabel for="form-username"
                                    value="Search for any account by username" />
                        <Search id="form-username"
                                v-model="form.username"
                                placeholder="Username" />
                    </div>

                    <div class="col-span-1 max-w-md">
                        <InputLabel for="account_types"
                                    value="Filter by account type" />
                        <select id="account_types"
                                class="select select-bordered w-full max-w-xs"
                                @change="appendAccountTypeSearch($event.target.value); $event.target.value = 'All'">
                            <option value="All">All</option>
                            <option v-for="accountType in accountTypes"
                                    :key="accountType"
                                    :value="accountType">
                                {{ formatAccountTypeName(accountType) }}
                            </option>
                        </select>

                        <div class="flex flex-wrap space-x-2">
                            <div v-if="form.account_types.length > 0"
                                 class="mt-2 flex items-center">
                                <div class="badge badge-info cursor-pointer"
                                     @click="clearAccountTypeSearch()">
                                    <svg class="inline-block h-4 w-4 stroke-current"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 18L18 6M6 6l12 12"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2" />
                                    </svg>
                                </div>
                            </div>

                            <div v-for="accountType in form.account_types"
                                 :key="accountType"
                                 class="mt-2">
                                <div class="badge badge-primary cursor-pointer"
                                     @click="removeAccountTypeSearch(accountType)">
                                    {{ formatAccountTypeName(accountType) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid sm:grid-cols-4 gap-4">
                    <div v-for="account in accounts.data" :key="account.username">
                        <Link :href="route('accounts.show', account)" class="block hover:opacity-90">
                            <AccountCard :account="account" />
                        </Link>
                    </div>

                    <div v-if="accounts.data.length === 0"
                         class="col-span-full text-center text-gray-500 dark:text-gray-400 py-12">
                        No accounts match these filters
                    </div>
                </div>

                <div v-if="accounts.meta && accounts.meta.last_page > 1"
                     class="mt-8 flex justify-center">
                    <div class="join">
                        <template v-for="(link, index) in accounts.meta.links" :key="index">
                            <Link v-if="link.url"
                                  :href="link.url"
                                  :only="['accounts', 'filters']"
                                  preserve-scroll
                                  preserve-state
                                  class="btn btn-sm join-item"
                                  :class="{ 'btn-active': link.active }"
                                  v-html="link.label" />
                            <span v-else
                                  class="btn btn-sm join-item btn-disabled"
                                  v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
