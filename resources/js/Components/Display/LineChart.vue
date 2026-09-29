<script setup>
import { computed, onBeforeUnmount, onMounted, ref, shallowRef, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import {
    Chart,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from "chart.js";

Chart.register(Filler, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip);
dayjs.extend(utc);

/*
 * A themed time series line chart for progress-over-time data (WOM XP/EHP/EHB
 * history and similar). The x-axis is a linear scale of millisecond
 * timestamps rather than Chart.js's date adapter, so no extra adapter package
 * is required — dayjs (already a dependency) formats ticks and tooltips.
 *
 * Points are UTC-midnight timestamps (one per calendar day server-side), so
 * every date computation here — tick boundaries, tick labels, the tooltip
 * title — goes through dayjs's `utc()` mode rather than the viewer's local
 * time. Formatting in local time would shift a UTC-midnight point onto the
 * previous or next calendar day for viewers west or east of UTC, which both
 * mislabels the point and can throw off the day/month/year tick counting
 * below.
 *
 * Colours are read from the DaisyUI theme's CSS custom properties rather than
 * hard-coded, so the chart repaints itself for every resource pack and both
 * themes without code changes here. `--pack-accent` — the pack's own accent
 * colour — wins for the first series when a pack sets it, matching how
 * pack-accent-border/pack-bg-card themed surfaces already behave elsewhere.
 */
const props = defineProps({
    // [{ label: String, points: [{ t: Number (ms), v: Number }] }]
    series: { type: Array, default: () => [] },
    format: {
        type: String,
        default: "number",
        validator: (value) => ["xp", "hours", "number"].includes(value),
    },
    height: { type: Number, default: 240 },
});

const page = usePage();
const canvas = ref(null);
const chart = shallowRef(null);

// --- Value formatting ------------------------------------------------------

// Trims a fixed-precision string back to its significant digits, so 1.20
// reads as 1.2 while 1.17 keeps both decimals.
const trimmed = (value) => String(parseFloat(value));

const formatValue = (value, format = props.format) => {
    if (value === null || value === undefined || Number.isNaN(value)) {
        return "–";
    }

    if (format === "hours") {
        return `${value.toFixed(1)} h`;
    }

    if (format === "xp") {
        const abs = Math.abs(value);
        const sign = value < 0 ? "-" : "";

        if (abs >= 1e9) {
            return `${sign}${trimmed((abs / 1e9).toPrecision(3))}B`;
        }
        if (abs >= 1e6) {
            return `${sign}${trimmed((abs / 1e6).toPrecision(3))}M`;
        }
        if (abs >= 1e3) {
            return `${sign}${trimmed((abs / 1e3).toPrecision(3))}K`;
        }

        return `${sign}${Math.round(abs).toLocaleString()}`;
    }

    return Number.isInteger(value) ? value.toLocaleString() : value.toFixed(1);
};

// --- Theme-aware colours ----------------------------------------------------

// Fixed hue order — identity follows the entity (series index), never a
// re-cycled palette, matching the categorical rule used across the app.
const SERIES_VARS = ["--color-primary", "--color-secondary", "--color-accent"];

const readColours = () => {
    const style = getComputedStyle(document.documentElement);
    const read = (name, fallback) => {
        const value = style.getPropertyValue(name).trim();
        return value || fallback;
    };

    return {
        series: SERIES_VARS.map((name, index) => {
            // The pack's own accent colour takes over the first series when a
            // pack sets one, so the chart's headline line matches the pack's
            // other accent-coloured chrome (pack-accent-border, badges, …).
            if (index === 0) {
                const packAccent = style.getPropertyValue("--pack-accent").trim();
                if (packAccent) {
                    return packAccent;
                }
            }

            return read(name, "#888888");
        }),
        text: read("--color-base-content", "#888888"),
        grid: read("--color-base-300", "#cccccc"),
    };
};

const colours = ref(readColours());

// --- Chart lifecycle ---------------------------------------------------

const datasets = computed(() => props.series.map((s, index) => {
    const colour = colours.value.series[index % colours.value.series.length];

    return {
        label: s.label,
        data: s.points.map((point) => ({ x: point.t, y: point.v })),
        borderColor: colour,
        backgroundColor: colour,
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointHitRadius: 8,
        tension: 0.15,
        fill: false,
    };
}));

const hasData = computed(() => props.series.some((s) => s.points.length > 0));

// The first and last timestamp across every series — the x-axis is clamped
// to exactly this, rather than Chart.js's default of rounding the range out
// to its own "nice" tick spacing, which was overshooting the last real point
// by weeks (see the linear scale's default `bounds: 'ticks'`).
const dataRange = () => {
    const values = props.series.flatMap((s) => s.points.map((point) => point.t));
    return values.length ? { min: Math.min(...values), max: Math.max(...values) } : null;
};

const DAY_MS = 86_400_000;

// Picks a calendar unit to align ticks to, and its label format, from how
// much time the series covers. WOM ranges run from a week to a full year;
// ticks with no notion of a calendar boundary either repeat the same day's
// label on a short range or crowd together on a long one.
const tickUnitFor = (spanMs) => {
    const days = spanMs / DAY_MS;
    if (days <= 60) {
        return { unit: "day", format: "D MMM" };
    }
    if (days <= 731) {
        return { unit: "month", format: "MMM YYYY" };
    }
    return { unit: "year", format: "YYYY" };
};

// Evenly spaced tick timestamps, capped at a sensible maximum (8) and at the
// number of `unit` boundaries actually spanned — so a 7-day range gets at
// most 7 ticks (one a day) instead of Chart.js's numeric tick spacing, which
// has no notion of a day and can land two ticks on the same one.
const buildTicks = (min, max) => {
    if (max <= min) {
        return [min];
    }

    const { unit } = tickUnitFor(max - min);
    const unitCount = dayjs.utc(max).diff(dayjs.utc(min), unit) + 1;
    const count = Math.min(8, Math.max(2, unitCount));

    return Array.from({ length: count }, (_, index) => min + ((max - min) * index) / (count - 1));
};

const buildConfig = () => {
    const range = dataRange();
    const tickFormat = range ? tickUnitFor(range.max - range.min).format : "D MMM";

    return {
        type: "line",
        data: { datasets: datasets.value },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: { mode: "index", intersect: false },
            scales: {
                x: {
                    type: "linear",
                    ...(range ? { min: range.min, max: range.max } : {}),
                    afterBuildTicks: (scale) => {
                        if (range) {
                            scale.ticks = buildTicks(range.min, range.max).map((value) => ({ value }));
                        }
                    },
                    grid: { color: withAlpha(colours.value.grid, 0.35) },
                    ticks: {
                        color: colours.value.text,
                        maxRotation: 0,
                        callback: (value) => dayjs.utc(value).format(tickFormat),
                    },
                },
                y: {
                    grid: { color: withAlpha(colours.value.grid, 0.35) },
                    ticks: {
                        color: colours.value.text,
                        callback: (value) => formatValue(value),
                    },
                },
            },
            plugins: {
                legend: {
                    display: props.series.length > 1,
                    labels: { color: colours.value.text, boxWidth: 12, boxHeight: 12 },
                },
                tooltip: {
                    callbacks: {
                        title: (items) => dayjs.utc(items[0]?.parsed.x).format("D MMM YYYY"),
                        label: (item) => `${item.dataset.label}: ${formatValue(item.parsed.y)}`,
                    },
                },
            },
        },
    };
};

// getComputedStyle returns colours as plain hex/rgb, not a colour object, so
// grid lines are softened with a manual alpha blend rather than a CSS
// function that some of those value shapes wouldn't accept.
function withAlpha(colour, alpha) {
    if (colour.startsWith("#") && (colour.length === 7 || colour.length === 4)) {
        const hex = colour.length === 4
            ? colour.slice(1).split("").map((c) => c + c).join("")
            : colour.slice(1);
        const r = parseInt(hex.slice(0, 2), 16);
        const g = parseInt(hex.slice(2, 4), 16);
        const b = parseInt(hex.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    return colour;
}

// Structural rebuild — new points, a new series list, or a format change.
// Low-frequency (driven by props), so a plain destroy + recreate is simpler
// and safer than trying to patch a Chart.js scale-type change in place.
const rebuild = () => {
    if (!canvas.value) {
        return;
    }

    colours.value = readColours();
    chart.value?.destroy();
    chart.value = hasData.value ? new Chart(canvas.value, buildConfig()) : null;
};

const coloursEqual = (a, b) => a.text === b.text && a.grid === b.grid
    && a.series.length === b.series.length && a.series.every((c, i) => c === b.series[i]);

// Colour-only refresh — patches the existing chart's colours in place rather
// than rebuilding it, and does nothing at all when nothing actually changed.
// This path can run often (see the MutationObserver below), so it has to
// stay cheap and idempotent rather than re-running the full build on every
// call — an unconditional rebuild here previously froze the tab under
// Vite's dev-mode <head> churn, which fires far more often than the pack/
// theme changes this is actually meant to catch.
const refreshColours = () => {
    if (!canvas.value) {
        return;
    }

    const next = readColours();
    if (coloursEqual(next, colours.value)) {
        return;
    }
    colours.value = next;

    if (!chart.value) {
        return;
    }

    const { options, data } = chart.value;
    data.datasets.forEach((dataset, index) => {
        const colour = next.series[index % next.series.length];
        dataset.borderColor = colour;
        dataset.backgroundColor = colour;
    });
    const gridColour = withAlpha(next.grid, 0.35);
    options.scales.x.ticks.color = next.text;
    options.scales.x.grid.color = gridColour;
    options.scales.y.ticks.color = next.text;
    options.scales.y.grid.color = gridColour;
    if (options.plugins.legend) {
        options.plugins.legend.labels.color = next.text;
    }
    chart.value.update("none");
};

watch(() => [props.series, props.format], rebuild, { deep: true });

// Re-read colours whenever the active theme or resource pack changes on the
// normal, Inertia-driven site — DarkModeToggle's page.props.theme/pack are
// the source of truth there.
watch(() => [page.props.theme, page.props.pack?.name, page.props.pack?.version], refreshColours);

// The styleguide's pack switcher deliberately previews packs without an
// Inertia visit (see Pages/Styleguide/Index.vue) — it mutates <html
// data-theme>/class and swaps the pack <style>/<link> directly, all
// synchronously in its one `apply()` function. A MutationObserver on those
// two attributes is the only way to notice that from in here; it doesn't
// need to watch <head> itself, because by the time this fires `apply()` has
// already finished writing the new --pack-accent style block too, so
// readColours() picks it up regardless of which of the two changes it woke
// up for. Scoping to these two attributes (rather than all of <head>, which
// Vite's dev-mode HMR touches constantly) keeps this from firing far more
// often than the pack/theme changes it's meant to catch.
let observer = null;
let scheduled = null;

const scheduleColourRefresh = () => {
    if (scheduled !== null) {
        return;
    }
    scheduled = requestAnimationFrame(() => {
        scheduled = null;
        refreshColours();
    });
};

onMounted(() => {
    rebuild();

    observer = new MutationObserver(scheduleColourRefresh);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["data-theme", "class"] });
});

onBeforeUnmount(() => {
    observer?.disconnect();
    if (scheduled !== null) {
        cancelAnimationFrame(scheduled);
    }
    chart.value?.destroy();
});
</script>

<template>
    <div :style="{ height: `${height}px` }" class="relative w-full">
        <canvas v-show="hasData" ref="canvas"></canvas>
        <div v-if="!hasData" class="flex h-full items-center justify-center text-sm text-base-content/60">
            No data yet.
        </div>
    </div>
</template>
