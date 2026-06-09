<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canRegister: { type: Boolean, default: false },
});
</script>

<template>
    <Head title="Setup in progress" />

    <div class="flex min-h-screen items-center justify-center bg-base-200 p-6">
        <div class="max-w-md space-y-4 text-center">
            <h1 class="text-3xl font-bold"
                style="font-family: 'runescape-smooth', sans-serif">
                Setup in progress
            </h1>
            <p class="text-base-content/70">
                This RuneManager instance isn't ready yet — the owner is still setting it up.
                Please check back soon.
            </p>
            <div class="flex justify-center gap-3 pt-2">
                <Link :href="route('login')" class="btn btn-neutral btn-sm">Log in</Link>
                <Link v-if="canRegister" :href="route('register')" class="btn btn-sm">Register</Link>
            </div>
        </div>
    </div>
</template>
