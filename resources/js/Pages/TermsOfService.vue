<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/Auth/AuthenticationCardLogo.vue';

defineProps({
    terms: String,
});
</script>

<template>
    <Head title="Terms of Service" />

    <div class="font-sans text-base-content antialiased">
        <div class="pt-4 bg-base-200">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                <div>
                    <AuthenticationCardLogo />
                </div>

                <div
                    class="w-full sm:max-w-2xl mt-6 p-6 bg-base-100 border border-base-300 shadow-md overflow-hidden sm:rounded-lg prose prose-headings:text-base-content prose-p:text-base-content/70 prose-li:text-base-content/70 prose-blockquote:text-base-content/70 prose-strong:text-base-content prose-a:text-primary"
                    v-html="terms"
                />
            </div>
        </div>
    </div>
</template>
