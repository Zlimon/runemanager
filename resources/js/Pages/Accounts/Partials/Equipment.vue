<script setup>
import { computed, ref } from "vue";
import ItemTooltip from "@/Components/Game/ItemTooltip.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const { packIcon } = useResourcePackIcon();

/*
 * The OSRS equipment widget is laid out as a fixed 5-row grid. Each pack ships
 * one slot_tile.png that paints the textured cell background and a
 * slot_{name}.png "ghost" for every empty slot; packIcon falls back to the
 * bundled Vanilla pack when no pack is applied, so slots always render.
 * We render per-slot so an equipped item swaps the empty ghost out cleanly.
 */
const rows = [
    ['head'],
    ['cape', 'neck', 'ammo'],
    ['weapon', 'body', 'shield'],
    ['legs'],
    ['hands', 'feet', 'ring'],
];

const emptyAsset = {
    head: 'slot_head',
    cape: 'slot_cape',
    neck: 'slot_neck',
    ammo: 'slot_ammunition',
    weapon: 'slot_weapon',
    body: 'slot_torso',
    shield: 'slot_shield',
    legs: 'slot_legs',
    hands: 'slot_hands',
    feet: 'slot_feet',
    ring: 'slot_ring',
};

const slotTileBg = computed(() => ({
    backgroundImage: `url(${packIcon('equipment', 'slot_tile').src})`,
    backgroundSize: '100% 100%',
}));

const equippedItem = (key) => props.account?.equipment?.[`${key}_item`] ?? null;

const emptySlot = (key) => packIcon('equipment', emptyAsset[key]);

const hoveredKey = ref(null);
</script>

<template>
    <div class="mx-auto w-[168px] p-3">
        <div v-if="account.equipment !== null" class="flex flex-col items-center gap-1">
            <div v-for="(row, rowIndex) in rows" :key="rowIndex"
                 class="flex justify-center gap-1">
                <div v-for="key in row" :key="key"
                     class="relative flex h-9 w-9 items-center justify-center"
                     :style="slotTileBg"
                     @mouseenter="hoveredKey = key" @mouseleave="hoveredKey = null">
                    <img v-if="equippedItem(key)"
                         :src="`data:image/jpeg;base64,${equippedItem(key).icon}`"
                         class="h-7 w-7 object-contain">
                    <img v-else v-bind="emptySlot(key)" class="h-7 w-7 object-contain opacity-60" alt="">

                    <ItemTooltip v-if="hoveredKey === key && equippedItem(key)"
                                 :name="equippedItem(key).name"
                                 :examine="equippedItem(key).examine" />
                </div>
            </div>
        </div>
    </div>
</template>
