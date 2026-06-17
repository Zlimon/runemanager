<script setup>
import { computed, ref, watch } from "vue";
import debounce from "lodash/debounce";
import { Link, router, usePage } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import Search from "@/Components/Search.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

const page = usePage();
const { skillIcon, onIconError } = useResourcePackIcon();

const query = ref('');
const showDropdown = ref(true);
const loading = ref(false);

const results = computed(() => page.props.accountSearchResults ?? []);

const reload = debounce(() => {
    router.reload({
        only: ['accountSearchResults'],
        data: { account_search: query.value },
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => { loading.value = true; },
        onFinish: () => {
            loading.value = false;
            showDropdown.value = true;
        },
    });
}, 250);

watch(query, (value) => {
    if (!value) {
        return;
    }
    reload();
});
</script>

<template>
    <div class="flex flex-col"
         @mouseleave="showDropdown = false"
         @mouseover="showDropdown = true">
        <div class="flex flex-wrap items-center justify-end flex-column md:flex-row">
            <InputLabel class="sr-only"
                        for="account-search-input"
                        value="Search for any account by username" />
            <Search id="account-search-input"
                    v-model="query"
                    placeholder="Search accounts" />
        </div>

        <div class="relative z-[1100] flex justify-end">
            <div v-if="showDropdown && query && results.length > 0"
                 class="absolute w-[calc(100%+4rem)] overflow-hidden rounded shadow-xl pack-bg-card resource-pack-border">
                <div v-for="account in results" :key="account.username">
                    <Link :href="route('accounts.show', account)"
                          class="flex flex-row items-center space-x-2 border-b border-base-300 p-2 hover:bg-base-300/40">
                        <img :src="`data:image/jpeg;base64,${account.icon}`"
                             class="h-16 w-16 rounded-full p-2 ring-2 ring-base-300">
                        <div class="flex flex-col justify-between">
                            <div class="flex items-center space-x-1">
                                <img v-if="account.account_type === 'ironman'"
                                     src="/images/ironman.png"
                                     class="h-6 w-6 object-contain">
                                <img v-else-if="account.account_type !== 'normal'"
                                     :src="`/images/${account.account_type}_ironman.png`"
                                     class="h-6 w-6 object-contain">
                                <p class="text-xl text-base-content">{{ account.username }}</p>
                            </div>

                            <div class="flex items-center space-x-1">
                                <img class="h-6 w-6 object-contain" alt=""
                                     :src="skillIcon('overall') ?? '/images/skill/overall.png'"
                                     @error="onIconError($event, '/images/skill/overall.png')">
                                <p class="text-md font-normal text-base-content/60">
                                    {{ account.level }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
