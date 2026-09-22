<script setup>
import { computed } from 'vue';

/*
 * Account online indicator. Pass `online` (the account.online flag, 1/0 or
 * bool); renders a coloured dot and, by default, an "Online"/"Offline" label.
 * Use `:show-label="false"` for a bare dot (e.g. as an .indicator-item overlay
 * on an avatar). Colours come from theme tokens so it adapts to light/dark.
 */
const props = defineProps({
    online: {
        type: [Boolean, Number],
        default: false,
    },
    showLabel: {
        type: Boolean,
        default: true,
    },
});

const isOnline = computed(() => Boolean(props.online));
</script>

<template>
    <span class="inline-flex items-center gap-1.5"
          :title="isOnline ? 'Online' : 'Offline'">
        <span class="inline-block h-2.5 w-2.5 rounded-full"
              :class="isOnline ? 'bg-success' : 'bg-base-content/30'" />
        <span v-if="showLabel"
              class="text-xs font-medium"
              :class="isOnline ? 'text-success' : 'text-base-content/60'">
            {{ isOnline ? 'Online' : 'Offline' }}
        </span>
    </span>
</template>
