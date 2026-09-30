<script setup>
import { computed, ref, useSlots } from "vue";

/*
 * Reusable tabbed-card chrome: a `tabs` strip sitting on top of a bordered
 * body that reads as one shape with the tabs. The body keeps its own top edge
 * along its full width; the strip overlaps it (1px here, the sprite line's
 * depth under a pack — see per-pack.css) and sits above it, so inactive tabs
 * rest on that edge while the active tab paints over it and opens straight
 * into the body. Centralises the pack-
 * aware classes (`resource-pack-tab`, `resource-pack-dialog-tabbed`) and the
 * active-tab styling so every tabbed card on the site stays in sync — see
 * Show.vue's Inventory/Looting Bag and Skills.vue's Skills/Bosses/Clues.
 *
 * Two content modes:
 *   - Named slots keyed by tab.key — every panel stays mounted and is toggled
 *     with v-show. Use when each tab renders different markup.
 *       <TabbedCard :tabs="tabs"><template #inventory>…</template></TabbedCard>
 *   - A single default slot exposing { active } — rendered once. Use when the
 *     tabs share one template driven by the active key.
 *       <TabbedCard :tabs="tabs"><template #default="{ active }">…</template></TabbedCard>
 */
const props = defineProps({
    tabs: {
        type: Array,
        required: true,
    },
    // v-model:tab — omit to let the card track its own active tab internally.
    modelValue: {
        type: String,
        default: null,
    },
    // Padding/utility classes for the body wrapper; override per consumer.
    bodyClass: {
        type: String,
        default: "p-3",
    },
    // Extra classes for each tab anchor (e.g. taller icon tabs on the Bank).
    tabClass: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);
const slots = useSlots();

const internal = ref(props.modelValue ?? props.tabs[0]?.key ?? null);

const active = computed({
    get: () => props.modelValue ?? internal.value,
    set: (value) => {
        internal.value = value;
        emit("update:modelValue", value);
    },
});

// Render per-tab named slots when the consumer provides any; otherwise fall
// back to the single default slot.
const hasNamedSlots = computed(() => props.tabs.some((tab) => slots[tab.key]));
</script>

<template>
    <div>
        <!-- One row, never wrapped: a wrapped second row would sit off the
             body's edge. Tabs that don't fit scroll sideways instead. -->
        <div class="tabs relative z-[1] -mb-px flex-nowrap overflow-x-auto [scrollbar-width:thin]" role="tablist">
            <a v-for="tab in tabs" :key="tab.key"
               class="tab resource-pack-tab gap-1.5"
               :class="[tabClass, { 'tab-active': active === tab.key }]"
               role="tab"
               @click="active = tab.key">
                <!-- Custom tab markup (e.g. the Bank's per-tab item icons);
                     defaults to the label, optionally prefixed with tab.icon
                     (an <img> attrs object from packIcon() or { src }). -->
                <slot name="tab" :tab="tab" :active="active">
                    <img v-if="tab.icon" v-bind="tab.icon" class="h-4 w-4 object-contain" alt="">
                    {{ tab.label }}
                </slot>
            </a>
        </div>

        <div class="bg-base-200 border border-base-300 rounded-b resource-pack-dialog-tabbed"
             :class="bodyClass">
            <template v-if="hasNamedSlots">
                <div v-for="tab in tabs" v-show="active === tab.key" :key="tab.key">
                    <slot :name="tab.key" :active="active" />
                </div>
            </template>
            <slot v-else :active="active" />
        </div>
    </div>
</template>
