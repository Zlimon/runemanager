<script setup>
/*
 * A nav-bar dropdown for grouping related links, matching the Hiscores menu's
 * trigger styling and the framed (resource-pack-dialog) panel. Put NavDropdownLink
 * children (and/or a logout button) in the default slot.
 */
defineProps({
    label: { type: String, required: true },
    active: { type: Boolean, default: false },
    /*
     * Which edge the panel lines up with. The default drops it from the
     * trigger's left edge, which is right for the dropdowns in the middle of
     * the bar. The last one in the nav needs `end`, or a panel wider than the
     * gap to the window edge is drawn off-screen and its labels are cut.
     */
    align: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'end'].includes(value),
    },
});
</script>

<template>
    <div class="dropdown" :class="{ 'dropdown-end': align === 'end' }">
        <div tabindex="0" role="button"
             class="flex cursor-pointer items-center gap-1 rounded px-3 py-2 md:p-0"
             :class="active ? 'text-accent font-semibold' : 'text-neutral-content hover:text-accent'">
            {{ label }}
            <svg class="h-2.5 w-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
        </div>

        <div tabindex="0"
             class="dropdown-content z-50 mt-2 w-52 space-y-1 rounded border border-base-300 bg-base-200 p-3 text-base-content shadow-xl resource-pack-dialog">
            <slot />
        </div>
    </div>
</template>
