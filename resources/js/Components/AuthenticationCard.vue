<script setup>
import Card from '@/Components/Card.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
        <div>
            <slot name="logo" />
        </div>

        <Card class="w-full sm:max-w-md mt-6 shadow-xl">
            <slot />
        </Card>
    </div>
</template>
