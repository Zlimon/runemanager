<script setup>
defineProps({
    quests: {
        type: Object,
        default: null,
    },
});

// The plugin pushes a status code per quest (0 = not started, 1 = in progress,
// 2 = finished). Mirror the OSRS quest journal's red / yellow / green so the
// list reads the same as in-game.
const QUEST_STATUS_COLORS = {
    0: '#ff0000',
    1: '#ffff00',
    2: '#00ff00',
};
</script>

<template>
    <div v-if="quests && quests.quests && quests.quests.length > 0"
         class="overflow-y-scroll max-h-[15rem]">
        <div class="m-2">
            <div v-for="quest in quests.quests" :key="quest[0]">
                <p :style="{ color: QUEST_STATUS_COLORS[quest[1]] }">
                    {{ quest[0] }}
                </p>
            </div>
        </div>
    </div>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-700 dark:text-gray-200">No quests found for this account</p>
    </div>
</template>
