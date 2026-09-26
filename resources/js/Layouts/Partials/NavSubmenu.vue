<script setup>
import { ref } from 'vue';

/*
 * A row inside a NavDropdown that flies out into a second panel, for grouping
 * a list that would otherwise crowd the dropdown it sits in.
 *
 * It opens to the left because the only dropdown using it is the last in the
 * nav, already against the right edge of the window; a panel opening rightwards
 * there would be drawn off-screen.
 *
 * Put the submenu's rows in the default slot — NavDropdownLink for links, or
 * plain buttons for anything that acts rather than navigates.
 */
defineProps({
    label: { type: String, required: true },
    /** Marks the row when the page it leads to is the current one. */
    active: { type: Boolean, default: false },
});

const open = ref(false);
</script>

<template>
    <!-- `focusin`/`focusout` alongside hover, so the submenu is reachable by
         keyboard and on touch, where there is no hover at all. Because the
         panel is a child of this wrapper, focus moving into it stays inside the
         parent dropdown, which is what keeps that dropdown open too. -->
    <div class="relative"
         @mouseenter="open = true"
         @mouseleave="open = false"
         @focusin="open = true"
         @focusout="open = false">
        <button type="button"
                class="nav-menu-link flex w-full items-center justify-between gap-2 rounded px-3 py-2 text-left text-base-content hover:bg-base-300"
                :class="{ 'is-active bg-base-300': open, 'font-semibold': active }"
                aria-haspopup="true" :aria-expanded="open"
                @click="open = !open">
            <span class="whitespace-nowrap">{{ label }}</span>
            <svg class="h-2.5 w-2.5 shrink-0 text-base-content/60" aria-hidden="true"
                 fill="none" viewBox="0 0 6 10" xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M5 1 1 5l4 4" />
            </svg>
        </button>

        <!-- The wrapper's `pr-4` clears the parent panel's own padding and
             doubles as an invisible bridge, so the submenu does not close while
             the pointer travels to it. -->
        <div v-show="open" class="absolute right-full top-0 z-50 pr-4">
            <div class="w-52 space-y-1 rounded border border-base-300 bg-base-200 p-3 text-base-content shadow-xl resource-pack-dialog">
                <slot />
            </div>
        </div>
    </div>
</template>
