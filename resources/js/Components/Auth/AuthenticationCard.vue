<script setup>
import Card from '@/Components/Cards/Card.vue';
import DarkModeToggle from '@/Components/Buttons/DarkModeToggle.vue';
import GuestPackPicker from '@/Components/ResourcePacks/GuestPackPicker.vue';
</script>

<template>
    <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
        <div class="absolute top-4 right-4 flex items-center gap-1">
            <GuestPackPicker />
            <DarkModeToggle />
        </div>

        <div>
            <slot name="logo" />
        </div>

        <Card class="w-full sm:max-w-md mt-6 shadow-xl">
            <slot />
        </Card>
    </div>
</template>
