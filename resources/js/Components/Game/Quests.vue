<script setup>
import { ref, onMounted } from "vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const questsLoading = ref(true);
const quests = ref(null);

const getQuests = () => {
    questsLoading.value = true;

    axios.get(route('api.accounts.quests.show', props.account))
        .then((response) => {
            quests.value = response.data.quests;
        })
        .catch((error) => {
            console.error(error);
        })
        .finally(() => {
            questsLoading.value = false;
        });
};

onMounted(getQuests);
</script>

<template>
    <div v-if="!questsLoading" class="overflow-y-scroll max-h-[15rem]">
        <div v-if="quests !== null && quests.quests.length > 0">
            <div class="m-2">
                <div v-for="quest in quests.quests" :key="quest[0]">
                    <p :class="{ 'text-green-500': quest[1] === 901389, 'text-yellow-500': quest[1] === 16776960, 'text-red-500': quest[1] === 16711680 }">
                        {{ quest[0] }}
                    </p>
                </div>
            </div>
        </div>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No quests found for this account</p>
        </div>
    </div>
    <Loader :loading="questsLoading" :component="true" />
</template>
