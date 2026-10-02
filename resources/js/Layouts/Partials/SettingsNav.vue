<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { settingsNavSections } from '@/composables/useSettingsNav.js';

/*
 * The sidebar of every settings and admin page (SettingsLayout). Each section
 * ("Account", "Admin") is a dropdown: the one holding the current page is
 * open and the other is collapsed until clicked, so moving between pages of a
 * section never shifts the list. On small screens the whole list folds
 * behind a toggle that names the current page.
 *
 * `settings-nav-link` / `is-active` are the texture hooks app.css repaints
 * from the pack palette; the DaisyUI classes are the untextured rendering.
 */
const page = usePage();

const sections = computed(() => settingsNavSections(page));

const isCurrent = (item) => route().current(item.active);

const items = (section) => section.groups.flatMap((group) => group.items);

const currentSection = computed(() => sections.value.find((section) => items(section).some(isCurrent)));
const currentItem = computed(() => currentSection.value && items(currentSection.value).find(isCurrent));

// The layout remounts on every visit, so this starts from the current page each time.
const open = ref(new Set(currentSection.value ? [currentSection.value.key] : sections.value.map((section) => section.key)));

const toggle = (key) => {
    const next = new Set(open.value);
    next.has(key) ? next.delete(key) : next.add(key);
    open.value = next;
};

const mobileOpen = ref(false);
</script>

<template>
    <nav class="shrink-0 md:w-56">
        <button type="button"
                class="settings-nav-link flex w-full items-center justify-between rounded bg-base-300 px-3 py-2 text-sm font-semibold md:hidden"
                :aria-expanded="mobileOpen"
                @click="mobileOpen = !mobileOpen">
            <span>{{ currentSection ? `${currentSection.label} · ${currentItem.label}` : 'Menu' }}</span>
            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': mobileOpen }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div class="mt-2 space-y-2 md:mt-0 md:block" :class="{ hidden: !mobileOpen }">
            <div v-for="section in sections" :key="section.key">
                <button type="button"
                        class="settings-nav-link flex w-full items-center justify-between rounded px-3 py-2 text-xs font-semibold uppercase tracking-wide text-base-content/60 hover:bg-base-300/50"
                        :aria-expanded="open.has(section.key)"
                        @click="toggle(section.key)">
                    {{ section.label }}
                    <svg class="h-4 w-4 transition-transform" :class="{ '-rotate-90': !open.has(section.key) }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div v-show="open.has(section.key)" class="mt-1 space-y-3">
                    <div v-for="group in section.groups" :key="group.heading ?? section.key">
                        <p v-if="group.heading" class="px-3 pb-1 text-[11px] font-medium uppercase tracking-wide text-base-content/40">
                            {{ group.heading }}
                        </p>
                        <ul class="space-y-1">
                            <li v-for="item in group.items" :key="item.route">
                                <Link :href="route(item.route)"
                                      class="settings-nav-link block rounded px-3 py-2 text-sm"
                                      :class="isCurrent(item)
                                          ? 'is-active bg-base-300 font-semibold text-base-content'
                                          : 'text-base-content/70 hover:bg-base-300/50'">
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
