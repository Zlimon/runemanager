<script setup>
import { ref } from "vue";
import Badge from "@/Components/Badge.vue";
import ResourcePackTile from "@/Components/ResourcePackTile.vue";
import ResourcePackConfirmModal from "@/Components/ResourcePackConfirmModal.vue";

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
    /*
     * Ask before applying the choice. Opt-in because it is only honest where a
     * click takes effect on its own: the Appearance page applies immediately and
     * reloads, while the instance-default picker in admin settings feeds a form
     * field that does nothing until Save — a dialog there would be confirming
     * something that has not happened.
     */
    confirm: { type: Boolean, default: false },
    // Passed through so the dialog can say whether confirming moves the
    // interface between the light and dark themes.
    currentlyDark: { type: Boolean, default: null },
});

const emit = defineEmits(['update:modelValue']);

const isSelected = (id) => String(props.modelValue ?? '') === String(id ?? '');

const pending = ref(null);

const select = (id) => {
    if (!props.confirm || isSelected(id)) {
        emit('update:modelValue', id);

        return;
    }

    // The two synthetic tiles have no pack row behind them; describe them well
    // enough for the dialog rather than suppressing it for them.
    pending.value = props.packs.find((pack) => String(pack.id) === String(id))
        ?? { id, alias: id === 'none' ? 'No resource pack' : 'Default', icon_url: null };
};

const applyPending = () => {
    const id = pending.value?.id;
    pending.value = null;
    emit('update:modelValue', id);
};
</script>

<template>
    <div>
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

    <ResourcePackConfirmModal :show="pending !== null"
                              :pack="pending"
                              :currently-dark="currentlyDark"
                              action="switch"
                              @confirm="applyPending"
                              @close="pending = null" />
    </div>
</template>
