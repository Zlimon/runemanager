<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import OnlineStatus from "@/Components/OnlineStatus.vue";
import dayjs from "dayjs";
import Icon from "@/Pages/Accounts/Partials/Icon.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

const { packIcon } = useResourcePackIcon();

defineProps({
    account: {
        type: Object,
        required: true,
    },
    activity: {
        type: String,
        default: null,
    },
    activityIcon: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="flex flex-col justify-between gap-y-7">
        <div class="flex items-center gap-x-5">
            <Icon :account="account" />
            <div class="flex flex-col">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img v-if="account.account_type && account.account_type !== 'normal'"
                             :src="`/images/${account.account_type}.png`"
                             class="h-8 w-8 object-contain">
                        <h1 class="text-xl font-bold md:text-3xl">
                            {{ account.username }}
                        </h1>
                    </div>
                </div>

                <p class="mt-1 text-xs text-base-content/60">
                    <span class="capitalize">
                        {{ account.account_type }}
                    </span>
                    <template v-if="account.clan_title">
                        ·
                        <span>{{ account.clan_title }}</span>
                    </template>
                    ·
                    <span>
                        Last updated
                        {{ dayjs(account.updated_at).format('MMMM D, YYYY h:mm A') }}
                    </span>
                </p>

                <p v-if="account.online && activity"
                   class="mt-1 flex items-center gap-1.5 text-xs font-medium text-success">
                    <img v-if="activityIcon" class="h-4 w-4 object-contain" alt=""
                         v-bind="packIcon('skill', activityIcon)">
                    <span v-else class="inline-block h-1.5 w-1.5 rounded-full bg-success"></span>
                    {{ activity }}
                </p>
            </div>
        </div>
    </div>
</template>
