<script setup>
import { Link } from "@inertiajs/vue3";

/*
 * A single button/link in a DaisyUI `join` pagination group — shared by
 * DataTable.vue (client-side windowed page numbers, driven by a `goTo(page)`
 * callback) and Accounts/Index.vue (server-driven pages from a Laravel
 * resource's `meta.links`, navigated via an Inertia `<Link>`). The two
 * pagers have genuinely different navigation mechanisms — one is a JS
 * callback, the other a real href — so this only unifies the rendered
 * item: pass `href` for a link, omit it for a click-driven button.
 */
defineProps({
    href: { type: String, default: null },
    active: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    ariaLabel: { type: String, default: null },
});

defineEmits(['click']);
</script>

<template>
    <Link v-if="href && !disabled" :href="href"
          class="btn btn-sm join-item"
          :class="active ? 'btn-primary' : 'btn-ghost'"
          :aria-current="active ? 'page' : null"
          :aria-label="ariaLabel"
          preserve-scroll preserve-state>
        <slot />
    </Link>
    <button v-else type="button"
            class="btn btn-sm join-item"
            :class="[active ? 'btn-primary' : 'btn-ghost', { 'btn-disabled': disabled }]"
            :disabled="disabled"
            :aria-current="active ? 'page' : null"
            :aria-label="ariaLabel"
            @click="$emit('click')">
        <slot />
    </button>
</template>
