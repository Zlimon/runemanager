<script setup>
import { Link } from "@inertiajs/vue3";

/* A single link inside a NavDropdown. */
defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
});
</script>

<template>
    <Link :href="href"
          class="block rounded px-3 py-2 text-base-content hover:bg-base-300"
          :class="{ 'bg-base-300 font-semibold': active }">
        <slot />
    </Link>
</template>
