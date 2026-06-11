<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const page = usePage();

/*
 * The OSRS equipment widget is laid out as a fixed 5-row grid. The active pack
 * (Default Vanilla at minimum — a pack is always in effect) ships one
 * slot_tile.png that paints the textured cell background and a slot_{name}.png
 * "ghost" for every empty slot. We render per-slot so an equipped item swaps
 * the empty ghost out cleanly.
 */
const rows = [
    ['head'],
    ['cape', 'neck', 'ammo'],
    ['weapon', 'body', 'shield'],
    ['legs'],
    ['hands', 'feet', 'ring'],
];

const emptyAsset = {
    head: 'slot_head.png',
    cape: 'slot_cape.png',
    neck: 'slot_neck.png',
    ammo: 'slot_ammunition.png',
    weapon: 'slot_weapon.png',
    body: 'slot_torso.png',
    shield: 'slot_shield.png',
    legs: 'slot_legs.png',
    hands: 'slot_hands.png',
    feet: 'slot_feet.png',
    ring: 'slot_ring.png',
};

const packAsset = (path) => {
    const pack = page.props.pack;
    if (!pack?.name) {
        return null;
    }
    return `/resource-packs/${pack.name}/equipment/${path}?v=${pack.version ?? ''}`;
};

const slotTileBg = computed(() => {
    const url = packAsset('slot_tile.png');
    return url ? { backgroundImage: `url(${url})`, backgroundSize: '100% 100%' } : {};
});

const equippedItem = (key) => props.account?.equipment?.[`${key}_item`] ?? null;

const emptySrc = (key) => packAsset(emptyAsset[key]);
</script>

<template>
    <div class="mx-auto w-[168px] p-3">
        <div v-if="account.equipment !== null" class="flex flex-col items-center gap-1">
            <div v-for="(row, rowIndex) in rows" :key="rowIndex"
                 class="flex justify-center gap-1">
                <div v-for="key in row" :key="key"
                     class="relative flex h-9 w-9 items-center justify-center"
                     :style="slotTileBg">
                    <img v-if="equippedItem(key)"
                         :src="`data:image/jpeg;base64,${equippedItem(key).icon}`"
                         :title="equippedItem(key).name"
                         class="h-7 w-7 object-contain">
                    <img v-else-if="emptySrc(key)"
                         :src="emptySrc(key)"
                         class="h-7 w-7 object-contain opacity-60">
                </div>
            </div>
        </div>
    </div>
</template>
