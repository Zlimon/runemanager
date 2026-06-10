<script setup>
/*
 * A thumbnail gallery for choosing a resource pack (icon.png), used for both the
 * owner's global default and a user's personal override. v-model is the selected
 * pack id (or '' / null for "none"). Pass `defaultId` to badge the global default
 * on the "None" tile.
 */
const props = defineProps({
    modelValue: { type: [Number, String, null], default: '' },
    packs: { type: Array, default: () => [] },
    defaultId: { type: [Number, null], default: null },
    allowNone: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

const isSelected = (id) => String(props.modelValue ?? '') === String(id ?? '');

const select = (id) => emit('update:modelValue', id);
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        <button v-if="allowNone" type="button"
                class="flex flex-col items-center gap-2 rounded border p-3 text-center transition"
                :class="isSelected('') ? 'border-primary bg-primary/10' : 'border-base-300 hover:border-primary/50'"
                @click="select('')">
            <div class="flex h-16 w-16 items-center justify-center rounded bg-base-300 text-xs text-base-content/60">
                None
            </div>
            <span class="text-sm font-medium">
                Default
                <span v-if="defaultId" class="block text-xs text-base-content/50">(instance theme)</span>
            </span>
        </button>

        <button v-for="pack in packs" :key="pack.id" type="button"
                class="flex flex-col items-center gap-2 rounded border p-3 text-center transition"
                :class="isSelected(pack.id) ? 'border-primary bg-primary/10' : 'border-base-300 hover:border-primary/50'"
                @click="select(pack.id)">
            <img v-if="pack.icon_url" :src="pack.icon_url" :alt="pack.alias"
                 class="h-16 w-16 rounded object-cover [image-rendering:pixelated]"
                 onerror="this.style.display='none'">
            <div v-else class="flex h-16 w-16 items-center justify-center rounded bg-base-300 text-xs text-base-content/60">
                {{ pack.alias }}
            </div>
            <span class="flex items-center gap-1 text-sm font-medium">
                <span class="max-w-[8rem] truncate" :title="pack.alias">{{ pack.alias }}</span>
                <span v-if="pack.id === defaultId" class="badge badge-neutral badge-xs">Default</span>
            </span>
        </button>
    </div>
</template>
