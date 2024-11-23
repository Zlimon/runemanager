<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";
import ItemSlot from "@/Components/RuneManager/ItemSlot.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

onMounted(() => {
    getBank();
});

let bankLoading = ref(true);
let bank = ref(null);
let activeTab = ref(null);
let activeTabItems = ref(null);

const getBank = () => {
    bankLoading.value = true;

    axios.get(route('api.accounts.bank.show', account.value))
        .then((response) => {
            bank.value = response.data.bank;
            setActiveTab(Object.keys(bank.value.tabs)[0], bank.value.tabs[Object.keys(bank.value.tabs)[0]]);
        }).catch(error => {
        console.error(error)
    }).finally(() => {
        bankLoading.value = false;
    });
};

function setActiveTab(tab, items) {
    activeTab.value = tab;
    activeTabItems.value = items;
}
</script>

<template>
    <div v-if="!bankLoading">
        <div v-if="bank !== null">
            <div class="flex flex-col">
                <!-- Vertical tabs -->
                <div class="tabs tabs-lifted" role="tablist">
                    <a v-for="(items, tab, index) in bank.tabs"
                       :class="{ 'tab-active !bg-base-200': activeTab === tab }"
                       class="tab !h-12"
                       role="tab"
                       @click="setActiveTab(tab, items)">
                        <img v-if="index >= 1 && items[0].item"
                             :src="`data:image/jpeg;base64,${items[0].item.icon}`"
                             class="object-contain"
                             loading="lazy">
                        <img v-else
                             :src="`data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAADd0lEQVR4Xu2Xv2sVQRDH3/0HqV5zzcEVBweCTQqLgM3rxMbCSgsLBbFQRFFEUQiiEDAQCBICKkHFXyghEoJKCAZ/BX9HBQURQURERERELMZ8bh1ub+/evTu9YOPCgklmdz4z853Zs9X6vxpdnuT3P1ueXD8R5HZ3KBe80QCKYeKwlThZGQDW2748gFrLky8Lsfx83pGPt2J5OhnKzHg50Kc7cQ6mu33t5cm3hwANyIf5SB5dCWXqeJkDTz7fi5Mzr2YimT0VyPigX2Jfe3ny9f4S0GK/vL8ZyYNLoVweKXNgMsqZF1dNNscOldnXWl5SpqRkiyvk3Vwkd8+Hcu6Y/1sPRWI1GWI/mwpleiyQ0QPt5oDQA9H+eBLLmxuRzJ8O5MyQXyJWEwRbyzuyry2dVQ0D2ZpwBZuF8hKtsSnv5Gggw3vasmFtXx2gbvMiFShlU03wbxySMbK1rmNHnwLRkVqyHRsrA+Xnhh2tApEpHFACBXo5HcnJw76sWV0MRADch83Bbe0qQHmYLJTpmO+PDYB2GL97O2syQAdl9eEl3Yg9JZ6bCJImoGyVgNAH0XOJWwIbyO4w7F9fM4DMmKw+UiDuu33W6AjwWkBETIpxoCVQILvDJo76SRn5mS4CcNcmVx+mbAQBNLrjnLZ+liGzjEY4iB44TL23rDcO7AyREdLPxQTBGeYMmhrc3rayau5VHWHDuYvDlcpmngVmBhGjCQ7u35rOjSIg1ZSKli7SILRsOosIlMxWBGKZslF3bWsGmToggy7Q3s19iTNEi0b4HUEM9KelViDuVGEP7a4ExDIX4EB1olkiQ2TRBqJEKmx0RPS0tQ3E3/8KyHZAa+NUgcggoicbmnrsKTO66wakGmK6071Hdv4BUJEDoPSlZ/LSwpSym72WulEgd1C6A9O1LwIiCBqFc1rqCkCm03Sy4kBnjQujc4S5pJpTe7fLioAqPB/mctUD+qEkpDb5Slz6BlLtkHIFstte7dWRDcSs4k46t+J7ZvSh7xIdxkEFtdvd/gxF5PosYJ//+DIltYE4T+f2AGLZnx3pBsj+DGU2paO/99bnSBuhBlCrpam2U65vHJnjcXTLonauzlT4aEyBeF7oTAZqJaDilY86BTF/d0Fs4bMp68KFxoDymctf5CX/G0ErOGagZsF7BbUsq9yhG9Ayw5jVlMNfYvqkTwc5XXUAAAAASUVORK5CYII=`"
                             class="object-contain"
                             loading="lazy">
                    </a>
                </div>
            </div>

            <!-- Tab content -->
            <div class="bg-base-200 border-x border-b border-base-300 rounded-b">
                <ul v-if="activeTab !== null"
                    class="grid grid-cols-8 gap-2 p-4">
                    <li v-for="(item, slot) in activeTabItems">
                        <ItemSlot :itemProp="item"/>
                    </li>
                </ul>
            </div>
        </div>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No bank found for this account</p>
        </div>
    </div>
    <Loader :component="true" :loading="bankLoading"></Loader>
</template>
