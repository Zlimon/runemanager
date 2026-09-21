<script setup>
import { computed, useSlots } from 'vue';
import Card from './Card.vue';
import SectionTitle from './SectionTitle.vue';

/*
 * A titled settings form: description on the left, the form panel on the right.
 *
 * The panel is a Card, so it picks up the active pack's derived surface colour
 * and accent border instead of the fixed `bg-base-100` it used to hardcode.
 *
 * The actions bar stays INSIDE the Card: the Card owns the edge (with a pack
 * active that edge is a sprite frame, not a CSS line), so a strip sitting below
 * it would be left unframed and read as detached from the form it submits. It
 * is inset rather than run edge-to-edge for the same reason per-pack.css insets
 * table rows — a full-bleed child background paints over the frame sprites at
 * the panel's edges and breaks the border.
 */
defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="$emit('submitted')">
                <Card>
                    <div class="grid grid-cols-6 gap-6">
                        <slot name="form" />
                    </div>

                    <div
                        v-if="hasActions"
                        class="mt-6 flex items-center justify-end rounded border pack-accent-border pack-bg-soft px-4 py-3 text-end"
                    >
                        <slot name="actions" />
                    </div>
                </Card>
            </form>
        </div>
    </div>
</template>
