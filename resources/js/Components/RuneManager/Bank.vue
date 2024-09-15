<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

onMounted(() => {
    getBank();
});

let bankLoading = ref(true);
let bank = ref([]);
const getBank = () => {
    bankLoading.value = true;

    axios.get(route('api.accounts.bank.show', account.value))
    .then((response) => {
        bank.value = response.data.bank;
    }).catch(error => {
        console.error(error)
    }).finally(() => {
        bankLoading.value = false;
    });
};

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <div v-if="!bankLoading">
        <div v-if="bank !== undefined">
            <ul class="-mb-px flex flex-wrap gap-2 text-center text-sm font-medium"
                id="default-tab"
                data-tabs-toggle="#default-tab-content"
                role="tablist">
                <li v-for="(tabs, tab, index) in bank.bank" role="presentation">
                    <button
                        class="inline-block rounded-t-lg p-2 !text-black active bg-beige-300 !border-t !border-b !border-b-beige-300 !border-x !border-beige-700 dark:border-gray-700 dark:bg-gray-800 dark:text-blue-500"
                        :id="`${tab}-tab`"
                        :data-tabs-target="`#${tab}`"
                        type="button"
                        role="tab"
                        :aria-controls="tab"
                        aria-selected="false">
                        <img v-if="index >= 1 && tabs[0].item"
                             :src="`data:image/jpeg;base64,${tabs[0].item.icon}`"
                             class="mx-auto h-10 w-10 object-contain"
                             loading="lazy"
                             @error="handleImageError">
                        <img v-else
                             :src="`data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAADd0lEQVR4Xu2Xv2sVQRDH3/0HqV5zzcEVBweCTQqLgM3rxMbCSgsLBbFQRFFEUQiiEDAQCBICKkHFXyghEoJKCAZ/BX9HBQURQURERERELMZ8bh1ub+/evTu9YOPCgklmdz4z853Zs9X6vxpdnuT3P1ueXD8R5HZ3KBe80QCKYeKwlThZGQDW2748gFrLky8Lsfx83pGPt2J5OhnKzHg50Kc7cQ6mu33t5cm3hwANyIf5SB5dCWXqeJkDTz7fi5Mzr2YimT0VyPigX2Jfe3ny9f4S0GK/vL8ZyYNLoVweKXNgMsqZF1dNNscOldnXWl5SpqRkiyvk3Vwkd8+Hcu6Y/1sPRWI1GWI/mwpleiyQ0QPt5oDQA9H+eBLLmxuRzJ8O5MyQXyJWEwRbyzuyry2dVQ0D2ZpwBZuF8hKtsSnv5Gggw3vasmFtXx2gbvMiFShlU03wbxySMbK1rmNHnwLRkVqyHRsrA+Xnhh2tApEpHFACBXo5HcnJw76sWV0MRADch83Bbe0qQHmYLJTpmO+PDYB2GL97O2syQAdl9eEl3Yg9JZ6bCJImoGyVgNAH0XOJWwIbyO4w7F9fM4DMmKw+UiDuu33W6AjwWkBETIpxoCVQILvDJo76SRn5mS4CcNcmVx+mbAQBNLrjnLZ+liGzjEY4iB44TL23rDcO7AyREdLPxQTBGeYMmhrc3rayau5VHWHDuYvDlcpmngVmBhGjCQ7u35rOjSIg1ZSKli7SILRsOosIlMxWBGKZslF3bWsGmToggy7Q3s19iTNEi0b4HUEM9KelViDuVGEP7a4ExDIX4EB1olkiQ2TRBqJEKmx0RPS0tQ3E3/8KyHZAa+NUgcggoicbmnrsKTO66wakGmK6071Hdv4BUJEDoPSlZ/LSwpSym72WulEgd1C6A9O1LwIiCBqFc1rqCkCm03Sy4kBnjQujc4S5pJpTe7fLioAqPB/mctUD+qEkpDb5Slz6BlLtkHIFstte7dWRDcSs4k46t+J7ZvSh7xIdxkEFtdvd/gxF5PosYJ//+DIltYE4T+f2AGLZnx3pBsj+DGU2paO/99bnSBuhBlCrpam2U65vHJnjcXTLonauzlT4aEyBeF7oTAZqJaDilY86BTF/d0Fs4bMp68KFxoDymctf5CX/G0ErOGagZsF7BbUsq9yhG9Ayw5jVlMNfYvqkTwc5XXUAAAAASUVORK5CYII=`"
                             class="mx-auto h-10 w-10 object-contain"
                             loading="lazy"
                             @error="handleImageError">
                    </button>
                </li>
            </ul>

            <div id="default-tab-content">
                <div v-for="(tabs, tab) in bank.bank"
                     class="hidden rounded-r-lg rounded-b-lg border shadow bg-beige-300 border-beige-700 dark:bg-gray-800"
                     :id="tab"
                     role="tabpanel"
                     :aria-labelledby="`${tab}-tab`">

                    <ul class="m-4 grid grid-cols-8 gap-2">
                        <li v-for="item in tabs" class="flex items-center justify-between">
                            <button :data-tooltip-target="`${tab}-${item.item.id}-tooltip-bottom`"
                                    data-tooltip-placement="bottom"
                                    type="button"
                                    class="relative h-20 w-20 rounded-lg border p-4 border-beige-700 dark:border-gray-700">
                                <span v-if="item.quantity === 0 || item.quantity > 1"
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

                            <div :id="`${tab}-${item.item.id}-tooltip-bottom`"
                                 role="tooltip"
                                 class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
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
            </div>
        </div>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No bank found for this account</p>
        </div>
    </div>
    <Loader :loading="bankLoading" :component="true"></Loader>
</template>
