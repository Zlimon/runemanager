<script setup>
import OnlineStatus from "@/Components/OnlineStatus.vue";

defineProps({
    account: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="indicator">
        <OnlineStatus :online="account.online" :show-label="false"
                      class="indicator-item" />
        <img v-if="account.icon"
             :src="`data:image/jpeg;base64,${account.icon}`"
             class="h-16 w-16 rounded-full p-2 ring-2 ring-base-300">
        <div v-else class="h-16 w-16 rounded-full p-2 ring-2 ring-base-300"></div>
    </div>
</template>
