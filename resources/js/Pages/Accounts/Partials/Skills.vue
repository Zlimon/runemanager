<script setup>
import { ref } from "vue";
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const activeSkill = ref(null);

/*
 * Prefer the active pack's skill icon (matches the in-game look) and fall back
 * to the bundled /images/skill/{slug}.webp when the pack doesn't ship one
 * (e.g. Sailing is only on packs updated since Nov 2025). ?v={pack.version}
 * busts the browser cache when the pack is re-installed.
 */
const skillIconSrc = (slug) => {
    const pack = page.props.pack;
    if (pack?.name) {
        return `/resource-packs/${pack.name}/skill/${slug}.png?v=${pack.version ?? ''}`;
    }
    return `/images/skill/${slug}.webp`;
};

const onIconError = (event, slug) => {
    // null the handler first so a missing fallback doesn't loop us.
    event.target.onerror = null;
    event.target.src = `/images/skill/${slug}.webp`;
};
</script>

<template>
    <ul class="mt-4 grid grid-cols-6 gap-1">
        <li>
            <Link :href="route('accounts.index')"
                  class="flex items-center justify-center gap-2 box !bg-base-200 resource-pack-box p-1"
                  @mouseleave="activeSkill = null"
                  @mouseover="activeSkill = 'total'">
                <img :src="skillIconSrc('total')"
                     class="h-6 w-6 object-contain"
                     alt="Total level"
                     @error="onIconError($event, 'total')">
                <span class="text-xs font-semibold capitalize">
                    {{ account.level }}
                </span>
            </Link>

            <!-- Tooltip -->
            <div v-if="activeSkill === 'total'"
                 class="box-tooltip">
                <p>Total level</p>
                <p>{{ account.xp.toLocaleString('en-US') }}</p>
            </div>
        </li>
        <li v-for="skill in account.skills" :key="skill.slug">
            <Link :href="route('hiscores.skills.index', skill.slug)"
                  class="flex items-center justify-center gap-2 box !bg-base-200 resource-pack-box p-1"
                  @mouseleave="activeSkill = null"
                  @mouseover="activeSkill = skill.slug">
                <img :src="skillIconSrc(skill.slug)"
                     class="h-6 w-6 object-contain"
                     :alt="skill.name"
                     @error="onIconError($event, skill.slug)">

                <span class="text-xs font-semibold capitalize">
                    {{ skill.level }}
                </span>
            </Link>

            <!-- Tooltip -->
            <div v-if="activeSkill === skill.slug"
                 class="box-tooltip">
                <p>{{ skill.name }}</p>
                <p>{{ skill.xp.toLocaleString('en-US') }}</p>
            </div>
        </li>
    </ul>
</template>
