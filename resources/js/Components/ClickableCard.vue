<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/*
 * A card that is itself the click target — the account grid, the resource-pack
 * picker, the loot board. Use this instead of hand-rolling a hover on a card or
 * on a <Link> wrapped around one, so every clickable card in the app lifts the
 * same way.
 *
 * Hover lifts the surface and raises the card; the accent edge is reserved for
 * `selected`, and the accent outline for keyboard focus. See
 * .pack-card-interactive in app.css for why those are outline/box-shadow rather
 * than a border (a pack suppresses the CSS border on cards).
 *
 * Renders a <Link> when given `href`, otherwise a <button> — a card that does
 * something without navigating (the picker tiles) is still a real button, so it
 * keeps keyboard activation and focus for free.
 *
 * `surface` exists for the one case where the content already draws its own
 * card: Accounts/Index nests an AccountCard, which is also rendered bare as a
 * map tooltip and so has to keep its own fill. Pass false there and this
 * provides only the interaction, not a second panel.
 */
const props = defineProps({
    // Navigates when set; omit for a button that only emits `click`.
    href: {
        type: String,
        default: null,
    },
    selected: {
        type: Boolean,
        default: false,
    },
    // Draw the card surface. Off when the slot content is already a card.
    surface: {
        type: Boolean,
        default: true,
    },
    padding: {
        type: String,
        default: 'p-4',
    },
    // Matches Card.vue: which sprite the active pack paints as the edge.
    frame: {
        type: String,
        default: 'border',
        validator: (value) => ['border', 'dialog'].includes(value),
    },
});

defineEmits(['click']);

const frameClass = computed(() => ({
    border: 'resource-pack-border',
    dialog: 'resource-pack-dialog',
}[props.frame]));

const surfaceClasses = computed(() => (props.surface
    ? ['pack-bg-card', 'border', 'pack-accent-border', frameClass.value, props.padding]
    : []));
</script>

<template>
    <component :is="href ? Link : 'button'"
               :href="href ?? undefined"
               :type="href ? undefined : 'button'"
               class="pack-card-interactive block w-full rounded text-left"
               :class="[surfaceClasses, { 'pack-card-selected': selected }]"
               @click="$emit('click')">
        <slot />
    </component>
</template>
