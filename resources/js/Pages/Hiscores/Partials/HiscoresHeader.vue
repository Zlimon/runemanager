<script setup>
import Search from "@/Components/Search.vue";

defineProps({
    title: {
        type: String,
        required: true,
    },
});

const search = defineModel('search', { type: String, default: '' });
</script>

<template>
    <div class="flex flex-column flex-wrap items-center justify-between space-y-4 pb-4 md:flex-row md:space-y-0">
        <h1 class="text-2xl font-bold dark:text-white">{{ title }}</h1>

        <div class="w-full md:w-80">
            <Search v-model="search" placeholder="Search by username" />
        </div>
    </div>
</template>
