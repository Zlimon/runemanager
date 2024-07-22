<script setup>
const props = defineProps({
    loading: Boolean,
    component: Boolean,
});

</script>

<template>
    <div v-if="loading"
         class="z-50 flex flex-col items-center justify-center overflow-hidden"
         :class="{ 'fixed top-0 right-0 bottom-0 left-0 h-screen w-full bg-gray-700': !component, 'p-5 bg-gray-500': component }">
        <img src="../../images/loading-rat.gif" class="h-20 w-20 fill-blue-600 text-gray-200 dark:text-gray-600">
    </div>
</template>
