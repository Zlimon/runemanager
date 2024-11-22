<script setup>
import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import dayjs from "dayjs";
import Icon from "@/Pages/Accounts/Components/Icon.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <div class="flex flex-col justify-between gap-y-7">
        <div class="flex items-center gap-x-5">
            <Icon :accountProp="account"/>
            <div class="flex flex-col">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img v-if="account.account_type === 'ironman'"
                             :src="`/images/ironman.png`"
                             class="h-8 w-8 object-contain">
                        <img v-else-if="account.account_type !== 'normal'"
                             :src="`/images/${account.account_type}_ironman.png`"
                             class="h-8 w-8 object-contain">
                        <h1 class="text-xl font-bold md:text-3xl">
                            {{ account.username }}
                        </h1>
                    </div>

                    <div class="flex shrink-0 items-center">
                        <PrimaryButton>
                            Update
                        </PrimaryButton>
                    </div>
                </div>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="capitalize">
                        {{ account.account_type }}
                    </span>
                    ·
                    <span>
                        Last updated
                        {{ dayjs(account.updated_at).format('MMMM D, YYYY h:mm A') }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</template>
