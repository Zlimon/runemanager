<script setup>
import Search from "@/Components/Form/Search.vue";
import InputError from "@/Components/Form/InputError.vue";

defineProps({
    title: {
        type: String,
        required: true,
    },
    // A search term the server refused, if any. The board keeps its last
    // results in that case, so without this nothing would say why.
    error: {
        type: String,
        default: null,
    },
});

const search = defineModel('search', { type: String, default: '' });
</script>

<template>
    <div class="flex flex-column flex-wrap items-center justify-between space-y-4 pb-4 md:flex-row md:space-y-0">
        <h1 class="text-2xl font-bold text-base-content">{{ title }}</h1>

        <div class="flex w-full flex-col gap-4 md:w-auto md:flex-row md:items-center">
            <slot />
            <div class="w-full md:w-80">
                <Search v-model="search" :error="Boolean(error)" placeholder="Search by username" />
                <InputError :message="error" />
            </div>
        </div>
    </div>
</template>
