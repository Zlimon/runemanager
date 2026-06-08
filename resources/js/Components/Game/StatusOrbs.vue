<script setup>
import { computed } from "vue";

/*
 * In-game status orbs (HP, Prayer, Run energy, Special attack). Values are
 * pushed live by the plugin; each orb fills bottom-up to its ratio and shows the
 * current value. Presentational — the parent feeds reactive `vitals`.
 */
const props = defineProps({
    vitals: {
        type: Object,
        default: null, // { hitpoints, hitpoints_max, prayer, prayer_max, run_energy, special_attack }
    },
});

const ratio = (value, max) => (max > 0 ? Math.min(100, (value / max) * 100) : 0);

const orbs = computed(() => {
    const v = props.vitals;

    return [
        { key: "hp", label: "Hitpoints", colour: "#c93a31", value: v?.hitpoints, fill: v ? ratio(v.hitpoints, v.hitpoints_max) : 0 },
        { key: "prayer", label: "Prayer", colour: "#46a4c4", value: v?.prayer, fill: v ? ratio(v.prayer, v.prayer_max) : 0 },
        { key: "run", label: "Run energy", colour: "#d6b400", value: v?.run_energy, fill: v ? v.run_energy : 0 },
        { key: "spec", label: "Special attack", colour: "#4f7bd0", value: v?.special_attack, fill: v ? v.special_attack : 0 },
    ];
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <div v-for="orb in orbs" :key="orb.key" class="orb" :title="orb.label">
            <div class="orb-fill" :style="{ height: `${orb.fill}%`, background: orb.colour }"></div>
            <span class="orb-value">{{ orb.value ?? '–' }}</span>
        </div>
    </div>
</template>

<style scoped>
.orb {
    position: relative;
    height: 2.1rem;
    width: 2.1rem;
    overflow: hidden;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.55);
    border: 1px solid var(--pack-accent, #94866d);
    box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.5), 0 1px 2px rgba(0, 0, 0, 0.5);
}
.orb-fill {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.85;
    transition: height 0.3s ease;
}
.orb-value {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #f0e6d2;
    text-shadow: 0 0 2px #000, 0 0 2px #000;
}
</style>
