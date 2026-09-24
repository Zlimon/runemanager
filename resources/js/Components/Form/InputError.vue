<script setup>
import { computed } from "vue";

/*
 * Field error display. Accepts either prop as a single string or an array of
 * strings — Inertia reports one string per field, so `:messages="form.errors.x"`
 * (a string) must NOT be iterated character-by-character. Normalise to a list.
 */
const props = defineProps({
    message: { type: [String, Array], default: null },
    messages: { type: [String, Array], default: null },
});

const items = computed(() => {
    const raw = props.message ?? props.messages;
    if (!raw) {
        return [];
    }
    return Array.isArray(raw) ? raw.filter(Boolean) : [raw];
});
</script>

<template>
    <div v-if="items.length">
        <p v-for="(item, index) in items" :key="index" class="mt-2 text-sm text-error">
            {{ item }}
        </p>
    </div>
</template>
