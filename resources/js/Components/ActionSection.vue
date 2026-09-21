<script setup>
import Card from './Card.vue';
import SectionTitle from './SectionTitle.vue';

/*
 * The sibling of FormSection for panels that carry their own controls rather
 * than a submitted form (two-factor, sessions, account deletion). Same Card
 * surface, so these settings panels follow the active resource pack too.
 */
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
            <Card>
                <slot name="content" />
            </Card>
        </div>
    </div>
</template>
