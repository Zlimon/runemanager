<script setup>
import { Link } from "@inertiajs/vue3";

/* A single link inside a NavDropdown. */
defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
});
</script>

<template>
    <!-- `nav-menu-link` / `is-active` are app.css's hooks for repainting the row
         from the pack palette; the DaisyUI classes are what renders with no pack. -->
    <Link :href="href"
          class="nav-menu-link block rounded px-3 py-2 text-base-content hover:bg-base-300"
          :class="{ 'is-active bg-base-300 font-semibold': active }">
        <slot />
    </Link>
</template>
