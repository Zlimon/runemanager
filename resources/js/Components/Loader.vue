<script setup>
defineProps({
    loading: Boolean,
    // Inline boxed loader that sits in the normal flow (legacy).
    component: Boolean,
    // Just the rat + caption, no wrapper styling — for embedding inside a modal
    // or other surface that supplies its own background/positioning.
    bare: Boolean,
});
</script>

<template>
    <div v-if="loading"
         class="z-50 flex flex-col items-center justify-center gap-3 overflow-hidden text-center"
         :class="{
             'fixed inset-0 h-screen w-full bg-gray-700 p-5': !component && !bare,
             'p-5': component,
         }">
        <img src="../../images/loading-rat.gif" alt="Loading"
             class="h-20 w-20 rounded-full object-cover [image-rendering:pixelated]">
        <slot />
    </div>
</template>
