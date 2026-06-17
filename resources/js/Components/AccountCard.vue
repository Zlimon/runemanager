<script setup>
import Icon from "@/Pages/Accounts/Partials/Icon.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

const { skillIcon, onIconError } = useResourcePackIcon();

/*
 * The themed account box used on the Accounts index — avatar (with online dot),
 * account-type badge, username and total level — extracted so the same box can
 * be reused elsewhere (e.g. as a Live Map marker label). Presentational only:
 * wrap it in a <Link> (or handle clicks) where navigation is wanted.
 */
defineProps({
    account: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="flex flex-col items-center rounded px-2 py-3 pack-bg-card resource-pack-border md:max-w-xl md:flex-row">
        <Icon :account="account" />
        <div class="flex flex-col justify-between p-4 leading-normal">
            <div class="flex items-center space-x-1">
                <img v-if="account.account_type && account.account_type !== 'normal'"
                     :src="`/images/${account.account_type}.png`"
                     class="h-6 w-6 object-contain" alt="">
                <h5>{{ account.username }}</h5>
            </div>

            <div v-if="account.level" class="flex items-center space-x-1">
                <img class="h-6 w-6 object-contain" alt=""
                     :src="skillIcon('overall') ?? '/images/skill/overall.png'"
                     @error="onIconError($event, '/images/skill/overall.png')">
                <p class="font-normal text-base-content/70">{{ account.level }}</p>
            </div>

            <p v-if="account.online && account.activity"
               class="mt-0.5 flex items-center gap-1 truncate text-xs text-success" :title="account.activity">
                <img v-if="account.activity_icon" class="h-3.5 w-3.5 object-contain" alt=""
                     :src="skillIcon(account.activity_icon) ?? `/images/skill/${account.activity_icon}.png`"
                     @error="onIconError($event, `/images/skill/${account.activity_icon}.png`)">
                {{ account.activity }}
            </p>
            <p v-if="account.online && account.world" class="text-xs text-base-content/50">
                World {{ account.world }}
            </p>
        </div>
    </div>
</template>
