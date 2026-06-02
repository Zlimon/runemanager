<script setup>
defineProps({
    quests: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <div v-if="quests && quests.quests && quests.quests.length > 0"
         class="overflow-y-scroll max-h-[15rem]">
        <div class="m-2">
            <div v-for="quest in quests.quests" :key="quest[0]">
                <p :class="{
                    'text-green-500': quest[1] === 901389,
                    'text-yellow-500': quest[1] === 16776960,
                    'text-red-500': quest[1] === 16711680,
                }">
                    {{ quest[0] }}
                </p>
            </div>
        </div>
    </div>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-500 dark:text-gray-400">No quests found for this account</p>
    </div>
</template>
