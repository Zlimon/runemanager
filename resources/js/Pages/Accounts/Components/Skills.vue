<script setup>
import {ref} from "vue";
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
let activeSkill = ref(null);
</script>

<template>
    <ul class="mt-4 grid grid-cols-6 gap-1">
        <li>
            <Link :href="route('accounts.index')"
                  class="flex items-center justify-center gap-2 box !bg-base-200 p-1"
                  @mouseleave="activeSkill = null"
                  @mouseover="activeSkill = 'total'">
                <img class="h-6 w-6 object-contain"
                     src="/images/skill/total.webp"/>
                <span class="text-xs font-semibold capitalize">
                    {{ account.level }}
                </span>
            </Link>

            <!-- Tooltip -->
            <div v-if="activeSkill === 'total'"
                 class="box-tooltip">
                <p>
                    Total level
                </p>
                <p>
                    {{ account.xp.toLocaleString('en-US') }}
                </p>
            </div>
        </li>
        <li v-for="skill in account.skills" :key="skill.slug">
            <Link :href="route('hiscores.skills.index', skill.slug)"
                  class="flex items-center justify-center gap-2 box !bg-base-200 p-1"
                  @mouseleave="activeSkill = null"
                  @mouseover="activeSkill = skill.slug">
                <img :src="`/images/skill/${skill.slug}.webp`"
                     class="h-6 w-6 object-contain"/>
                <span class="text-xs font-semibold capitalize">
                    {{ skill.level }}
                </span>
            </Link>

            <!-- Tooltip -->
            <div v-if="activeSkill === skill.slug"
                 class="box-tooltip">
                <p>
                    {{ skill.name }}
                </p>
                <p>
                    {{ skill.xp.toLocaleString('en-US') }}
                </p>
            </div>
        </li>
    </ul>
</template>
