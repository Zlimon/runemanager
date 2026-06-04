<script setup>
defineProps({
    message: String,
    messages: Array,
});
</script>

<template>
    <div v-if="message" v-show="message">
        <p class="mt-2 text-sm text-error">
            {{ message }}
        </p>
    </div>
    <div v-else v-for="message in messages" v-show="messages">
        <p class="mt-2 text-sm text-error">
            {{ message }}
        </p>
    </div>
</template>
