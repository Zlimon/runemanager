<script setup>
import Badge from "@/Components/Badge.vue";
import ResourcePackTile from "@/Components/ResourcePackTile.vue";

/*
 * A thumbnail gallery for choosing a resource pack (icon.png), used for both the
 * owner's global default and a user's personal override. v-model is the selected
 * pack id, '' for "Default" (follow the instance theme), or 'none' for an explicit
 * no-pack / non-textured interface. Pass `defaultId` to badge the global default,
 * and `allowNoPack` to offer the "No resource pack" tile.
 */
const props = defineProps({
    modelValue: { type: [Number, String, null], default: '' },
    packs: { type: Array, default: () => [] },
    defaultId: { type: [Number, null], default: null },
    allowNone: { type: Boolean, default: true },
    allowNoPack: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const isSelected = (id) => String(props.modelValue ?? '') === String(id ?? '');

const select = (id) => emit('update:modelValue', id);
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        <ResourcePackTile v-if="allowNone" interactive alias="Default" fallback-text="None"
                           :sublabel="defaultId ? '(instance theme)' : null"
                           :selected="isSelected('')" @click="select('')" />

        <ResourcePackTile v-if="allowNoPack" interactive alias="No resource pack" fallback-text="Plain"
                           sublabel="(no textures)"
                           :selected="isSelected('none')" @click="select('none')" />

        <ResourcePackTile v-for="pack in packs" :key="pack.id" interactive
                           :icon-url="pack.icon_url" :alias="pack.alias"
                           :selected="isSelected(pack.id)" @click="select(pack.id)">
            <template v-if="pack.id === defaultId" #badge>
                <Badge size="xs">Default</Badge>
            </template>
        </ResourcePackTile>
    </div>
</template>
