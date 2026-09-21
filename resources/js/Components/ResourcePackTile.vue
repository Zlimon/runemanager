<script setup>
import { computed } from 'vue';
import Card from './Card.vue';
import ClickableCard from './ClickableCard.vue';

/*
 * A single resource-pack tile: icon (or fallback text), name (wrapping up to
 * two lines rather than truncating to one, so it stays readable), an optional
 * badge, and a selected/boundary state. Shared by ResourcePackPicker.vue (a
 * selectable grid) and Admin/Packs.vue's hub browser (where clicking the tile
 * starts the install). Pass `interactive` to render the tile as the clickable
 * control; anything in the `#action` slot is then a label rather than a
 * control, since a button cannot contain another one.
 */
const props = defineProps({
    iconUrl: { type: String, default: null },
    alias: { type: String, required: true },
    // Shown instead of an icon, e.g. "None" / "Plain" for the synthetic tiles.
    fallbackText: { type: String, default: null },
    sublabel: { type: String, default: null },
    selected: { type: Boolean, default: false },
    interactive: { type: Boolean, default: false },
    // Only meaningful with `interactive`: the tile stays visible but stops
    // being pressable (the hub browser once the install limit is reached).
    disabled: { type: Boolean, default: false },
});

defineEmits(['click']);

// A selectable tile is a real clickable card and shares its chrome with the
// account grid and the loot board; the hub browser's tiles are plain panels
// with their own Install button, so they stay a Card.
const surface = computed(() => (props.interactive ? ClickableCard : Card));
</script>

<template>
    <component :is="surface"
               v-bind="interactive ? { selected, disabled } : {}"
               padding="p-3"
               class="relative flex flex-col items-center gap-2 text-center"
               @click="$emit('click')">
        <span v-if="selected"
              class="absolute right-1.5 top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-content">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </span>

        <img v-if="iconUrl" :src="iconUrl" :alt="alias"
             class="h-16 w-16 rounded object-cover [image-rendering:pixelated]"
             loading="lazy" onerror="this.style.display='none'">
        <div v-else class="flex h-16 w-16 items-center justify-center rounded bg-base-300 text-xs text-base-content/60">
            {{ fallbackText ?? alias }}
        </div>

        <span class="flex w-full flex-wrap items-center justify-center gap-1 text-sm font-medium">
            <span class="line-clamp-2 max-w-full break-words" :title="alias">{{ alias }}</span>
            <slot name="badge" />
        </span>
        <span v-if="sublabel" class="text-xs text-base-content/50">{{ sublabel }}</span>

        <slot name="action" />
    </component>
</template>
