<script setup>
import {ref} from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <AppLayout title="Account">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="card-lg resource-pack-dialog">
                    <div class="grid grid-cols-3">
                        <div class="col-span-1">
                            <h3 class="text-center header-chatbox-sword">{{ account.username }}</h3>

                            <div class="max-w-md mx-auto mt-6 lg:mt-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="col-span-1">
                                        <img :src="`https://www.osrsbox.com/osrsbox-db/items-icons/${account.icon_id}.png`"
                                             class="object-contain h-16 w-16 m-4">
                                    </div>

                                    <div class="col-span-1 grid grid-cols-2 gap-6">
                                        <div class="col-span-1">
                                            <label for="rank" class="block text-sm font-medium text-gray-700 dark:text-white">Rank</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.rank }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="xp" class="block text-sm font-medium text-gray-700 dark:text-white">Total XP</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.xp }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="level" class="block text-sm font-medium text-gray-700 dark:text-white">Total level</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.level }}</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label for="joined" class="block text-sm font-medium text-gray-700 dark:text-white">Joined</label>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ account.created_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <h3 class="text-center header-chatbox-sword">Adventurer's Log</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
