<script setup>
import { ref, watch } from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import InputLabel from "@/Components/Form/InputLabel.vue";
import Search from "@/Components/Form/Search.vue";
import SelectInput from "@/Components/Form/SelectInput.vue";
import AccountCard from "@/Components/Cards/AccountCard.vue";
import ClickableCard from "@/Components/Cards/ClickableCard.vue";
import PaginationItem from "@/Components/Tables/PaginationItem.vue";
import Badge from "@/Components/Display/Badge.vue";

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
                <h3 class="text-left">Search for accounts</h3>

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
                        <SelectInput id="account_types"
                                     class="max-w-xs"
                                     :model-value="'All'"
                                     @update:model-value="appendAccountTypeSearch">
                            <option value="All">All</option>
                            <option v-for="accountType in accountTypes"
                                    :key="accountType"
                                    :value="accountType">
                                {{ formatAccountTypeName(accountType) }}
                            </option>
                        </SelectInput>

                        <div class="flex flex-wrap space-x-2">
                            <div v-if="form.account_types.length > 0"
                                 class="mt-2 flex items-center">
                                <Badge variant="info"
                                       class="cursor-pointer"
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
                                </Badge>
                            </div>

                            <div v-for="accountType in form.account_types"
                                 :key="accountType"
                                 class="mt-2">
                                <Badge variant="primary"
                                       class="cursor-pointer"
                                       @click="removeAccountTypeSearch(accountType)">
                                    {{ formatAccountTypeName(accountType) }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid sm:grid-cols-4 gap-4">
                    <div v-for="account in accounts.data" :key="account.username">
                        <ClickableCard :href="route('accounts.show', account)" :surface="false">
                            <AccountCard :account="account" />
                        </ClickableCard>
                    </div>

                    <div v-if="accounts.data.length === 0"
                         class="col-span-full text-center text-base-content/60 py-12">
                        No accounts match these filters
                    </div>
                </div>

                <nav v-if="accounts.meta && accounts.meta.last_page > 1"
                     class="mt-8 flex justify-center" aria-label="Pagination">
                    <div class="join border border-base-300">
                        <PaginationItem v-for="(link, index) in accounts.meta.links" :key="index"
                                        :href="link.url"
                                        :only="link.url ? ['accounts', 'filters'] : undefined"
                                        :active="link.active"
                                        :disabled="!link.url">
                            <span v-html="link.label" />
                        </PaginationItem>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
