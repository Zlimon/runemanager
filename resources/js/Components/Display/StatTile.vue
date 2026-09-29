<script setup>
import { computed } from "vue";

/*
 * A single headline metric on its own themed tile — EHP/EHB/TTM-style numbers,
 * the dashboard's top-gainer figures, and similar. Framed the same way
 * AccountCard is (pack-bg-card + pack-accent-border), so it drops straight
 * into a grid without needing its own Card wrapper.
 */
const props = defineProps({
    label: { type: String, required: true },
    // A pre-formatted string is shown verbatim; a number is run through the
    // same `format` this component uses for `delta`.
    value: { type: [String, Number], required: true },
    // Signed change since some earlier point. Omit to hide the delta line.
    delta: { type: Number, default: null },
    format: {
        type: String,
        default: "number",
        validator: (value) => ["xp", "hours", "number"].includes(value),
    },
    // An object for v-bind, e.g. the result of packIcon('skill', 'overall').
    icon: { type: Object, default: null },
});

// Trims a fixed-precision string back to its significant digits, so 1.20
// reads as 1.2 while 1.17 keeps both decimals — mirrors LineChart's formatter
// so the same numbers read identically in the tile and the chart tooltip.
const trimmed = (value) => String(parseFloat(value));

const formatMagnitude = (value) => {
    if (props.format === "hours") {
        return `${value.toFixed(1)} h`;
    }

    if (props.format === "xp") {
        if (value >= 1e9) {
            return `${trimmed((value / 1e9).toPrecision(3))}B`;
        }
        if (value >= 1e6) {
            return `${trimmed((value / 1e6).toPrecision(3))}M`;
        }
        if (value >= 1e3) {
            return `${trimmed((value / 1e3).toPrecision(3))}K`;
        }

        return Math.round(value).toLocaleString();
    }

    return Number.isInteger(value) ? value.toLocaleString() : value.toFixed(1);
};

const displayValue = computed(() => (
    typeof props.value === "number" ? formatMagnitude(props.value) : props.value
));

const hasDelta = computed(() => props.delta !== null && props.delta !== undefined && !Number.isNaN(props.delta));
const deltaClass = computed(() => (props.delta >= 0 ? "text-success" : "text-error"));
const displayDelta = computed(() => {
    if (!hasDelta.value) {
        return "";
    }

    const sign = props.delta > 0 ? "+" : props.delta < 0 ? "-" : "±";
    return `${sign}${formatMagnitude(Math.abs(props.delta))}`;
});
</script>

<template>
    <div class="flex items-center gap-3 rounded p-4 pack-bg-card border pack-accent-border resource-pack-border">
        <img v-if="icon" v-bind="icon" alt="" class="h-8 w-8 shrink-0 object-contain">
        <div class="min-w-0">
            <p class="text-xs uppercase tracking-wide opacity-70">{{ label }}</p>
            <p class="text-xl font-semibold">{{ displayValue }}</p>
            <p v-if="hasDelta" class="text-xs font-medium" :class="deltaClass">{{ displayDelta }}</p>
        </div>
    </div>
</template>
