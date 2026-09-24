<script setup>
import { computed } from "vue";

/*
 * In-game status orbs (HP, Prayer, Run energy, Special attack). Values are
 * pushed live by the plugin. Laid out like the OSRS minimap orb: a sphere on
 * the left and the value on the right. When a resource pack is active its orb
 * sprites texture the sphere (see per-pack.css `.status-orb__sphere`); otherwise
 * the sphere falls back to a flat colour. Presentational — the parent feeds
 * reactive `vitals`.
 */
const props = defineProps({
    vitals: {
        type: Object,
        default: null, // { hitpoints, hitpoints_max, prayer, prayer_max, run_energy, special_attack }
    },
});

const orbs = computed(() => {
    const v = props.vitals;

    return [
        { key: "hp", label: "Hitpoints", colour: "#9a2c25", value: v?.hitpoints },
        { key: "prayer", label: "Prayer", colour: "#3a7d96", value: v?.prayer },
        { key: "run", label: "Run energy", colour: "#b59a1e", value: v?.run_energy },
        { key: "spec", label: "Special attack", colour: "#3a5a96", value: v?.special_attack },
    ];
});
</script>

<template>
    <div class="flex flex-col gap-1">
        <div v-for="orb in orbs" :key="orb.key" class="status-orb" :class="`status-orb--${orb.key}`" :title="orb.label">
            <span class="status-orb__sphere" :style="{ backgroundColor: orb.colour }"></span>
            <span class="status-orb__value">{{ orb.value ?? '–' }}</span>
        </div>
    </div>
</template>

<!-- Unscoped so the resource-pack stylesheet (loaded after) can texture the
     sphere via the same class names. -->
<style>
.status-orb {
    position: relative;
    height: 34px;
    width: 57px;
    border-radius: 17px;
    background-color: rgba(0, 0, 0, 0.45);
    border: 1px solid var(--pack-accent, #6b6048);
}
.status-orb__sphere {
    position: absolute;
    left: 3px;
    top: 4px;
    height: 26px;
    width: 26px;
    border-radius: 9999px;
    background-repeat: no-repeat;
    background-position: center;
    background-size: 26px 26px;
    box-shadow: inset 0 -6px 7px rgba(0, 0, 0, 0.45), inset 0 5px 6px rgba(255, 255, 255, 0.22);
}
.status-orb__value {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 29px;
    right: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #f0e6d2;
    text-shadow: 0 0 2px #000, 0 0 2px #000;
}
</style>
