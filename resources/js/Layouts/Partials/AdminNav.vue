<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { adminNavGroups, filterRegisteredRoutes } from '@/composables/useAdminNav.js';

/*
 * The admin area's sub-navigation, grouped by domain. Rendered as a sidebar
 * on large screens and a horizontally scrolling strip on small ones — see
 * AdminLayout for the responsive wrapper. Reuses the same `settings-nav-link`
 * / `is-active` texture hooks as SettingsLayout's own sidebar (see
 * resources/css/app.css) so both sidebars repaint identically under a
 * resource pack.
 *
 * The group list itself lives in composables/useAdminNav.js, shared with
 * SettingsLayout's own "Admin" group, so the two navs can't drift out of
 * sync. Every entry is only shown when its route is registered
 * (`route().has()`), so this list stays correct regardless of which admin
 * surfaces have shipped yet, and members-only routes (hidden entirely on a
 * casual instance) simply disappear rather than 404.
 */
const page = usePage();

const groups = computed(() => filterRegisteredRoutes(adminNavGroups(page)));
</script>

<template>
    <nav>
        <!-- Sidebar on large screens -->
        <div class="hidden lg:block lg:w-56 lg:shrink-0">
            <div v-for="group in groups" :key="group.heading" class="mb-4">
                <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-base-content/50">
                    {{ group.heading }}
                </p>
                <ul class="space-y-1">
                    <li v-for="item in group.items" :key="item.route">
                        <Link :href="route(item.route)"
                              class="settings-nav-link block rounded px-3 py-2 text-sm"
                              :class="route().current(item.active)
                                  ? 'is-active bg-base-300 font-semibold text-base-content'
                                  : 'text-base-content/70 hover:bg-base-300/50'">
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Horizontal scroller on small screens -->
        <div class="-mx-4 mb-6 flex gap-1 overflow-x-auto px-4 pb-1 lg:hidden">
            <Link v-for="item in groups.flatMap((group) => group.items)"
                  :key="item.route"
                  :href="route(item.route)"
                  class="settings-nav-link shrink-0 rounded px-3 py-2 text-sm whitespace-nowrap"
                  :class="route().current(item.active)
                      ? 'is-active bg-base-300 font-semibold text-base-content'
                      : 'text-base-content/70 hover:bg-base-300/50'">
                {{ item.label }}
            </Link>
        </div>
    </nav>
</template>
