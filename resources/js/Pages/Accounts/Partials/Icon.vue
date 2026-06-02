<script setup>
import {ref} from "vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <div class="indicator">
        <span v-show="account.online === 1"
              class="indicator-item badge badge-secondary"/>
        <img :src="`data:image/jpeg;base64,${account.icon}`"
             class="h-16 w-16 rounded-full p-2 ring-2 ring-base-300">
    </div>
</template>
