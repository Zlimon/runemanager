<script setup>
/*
 * A single resource-pack tile: icon (or fallback text), name (wrapping up to
 * two lines rather than truncating to one, so it stays readable), an optional
 * badge, and a selected/boundary state. Shared by ResourcePackPicker.vue (a
 * selectable grid — pass `interactive` to render the tile itself as the
 * clickable control) and Admin/Packs.vue's hub browser (not selectable; put
 * an Install button in the `#action` slot instead, since a tile can't be both
 * a button and contain one).
 */
defineProps({
    iconUrl: { type: String, default: null },
    alias: { type: String, required: true },
    // Shown instead of an icon, e.g. "None" / "Plain" for the synthetic tiles.
    fallbackText: { type: String, default: null },
    sublabel: { type: String, default: null },
    selected: { type: Boolean, default: false },
    interactive: { type: Boolean, default: false },
});

defineEmits(['click']);
</script>

<template>
    <component :is="interactive ? 'button' : 'div'"
               :type="interactive ? 'button' : undefined"
               class="relative flex flex-col items-center gap-2 rounded-lg p-3 text-center transition pack-bg-card border resource-pack-border"
               :class="selected
                   ? 'border-primary ring-2 ring-primary ring-offset-2 ring-offset-base-100'
                   : 'pack-accent-border hover:border-primary/60'"
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
