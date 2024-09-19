<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

onMounted(() => {
    getLootingBag();
});

let lootingBagLoading = ref(true);
let lootingBag = ref([]);
const getLootingBag = () => {
    lootingBagLoading.value = true;

    axios.get(route('api.accounts.looting-bag.show', account.value))
    .then((response) => {
        lootingBag.value = response.data.looting_bag;
    }).catch(error => {
        console.error(error)
    }).finally(() => {
        lootingBagLoading.value = false;
    });
};
</script>

<template>
    <div v-if="!lootingBagLoading">
        <div v-if="lootingBag !== undefined">
            <ul class="m-2 grid grid-cols-4 gap-2">
                <li v-for="(item, slot) in lootingBag.looting_bag" class="flex items-center justify-between">
                    <button :data-tooltip-target="`lootingBag-${slot}-${item.item.id}-tooltip-bottom`"
                            data-tooltip-placement="bottom"
                            type="button"
                            class="relative h-20 w-20 rounded-lg border p-4 border-beige-700 dark:border-gray-700"
                            :class="{'cursor-default': item.item.id === -1}">
                        <span v-if="item.quantity > 1"
                              class="absolute top-0 left-0 p-1 text-sm">
                            {{ item.quantity }}
                        </span>
                        <img v-if="item.item.icon"
                             :src="`data:image/jpeg;base64,${item.item.icon}`"
                             class="mx-auto h-10 w-10 object-contain"
                             :class="{ 'opacity-50': item.quantity === 0 }"
                             loading="lazy"
                             @error="handleImageError">
                        <span v-else>
                            {{ item.item.name }}
                        </span>
                    </button>

                    <div :id="`lootingBag-${slot}-${item.item.id}-tooltip-bottom`"
                         role="tooltip"
                         class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700"
                         :class="{'hidden': item.item.id === -1}">
                        <p>
                            {{ item.item.name }}
                        </p>
                        <p>
                            {{ item.item.examine }}
                        </p>
                        <p v-if="item.quantity > 0 && item.item.highalch">
                            HA: {{ (item.item.highalch * item.quantity).toLocaleString('en-US') }} gp
                            <span v-if="item.quantity > 1">({{ item.item.highalch.toLocaleString('en-US') }} ea)</span>
                        </p>
                        <p v-if="item.quantity > 0 && item.item.lowalch">
                            LA: {{ (item.item.lowalch * item.quantity).toLocaleString('en-US') }} gp
                            <span v-if="item.quantity > 1">({{ item.item.lowalch.toLocaleString('en-US') }} ea)</span>
                        </p>
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No looting bag found for this account</p>
        </div>
    </div>
    <Loader :loading="lootingBagLoading" :component="true"></Loader>
</template>
