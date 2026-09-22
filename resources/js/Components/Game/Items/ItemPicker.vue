<script setup>
import { computed, ref, watch } from "vue";
import debounce from "lodash/debounce";
import TextInput from "@/Components/Form/TextInput.vue";

/*
 * Debounced OSRS item search-and-select, used to attach an icon to a calendar
 * event. Mirrors TextInput's error prop so it drops into a form the same way.
 * `modelValue` is the item id; the component resolves its own display (icon +
 * name) from the search results it fetches, or from `modelItem` when the
 * caller already has one on hand (e.g. editing an event that already has an
 * item attached) so we don't need an id → item lookup endpoint.
 */
const props = defineProps({
    modelValue: { type: Number, default: null },
    modelItem: { type: Object, default: null }, // { id, name, icon } | null
    error: { type: Boolean, default: false },
    placeholder: { type: String, default: "Search items…" },
});

const emit = defineEmits(["update:modelValue"]);

const query = ref("");
const results = ref([]);
const loading = ref(false);
const open = ref(false);

// The item currently shown as selected. Prefer whatever we resolved ourselves
// (from a search pick), fall back to the caller-supplied one for the initial
// value, and clear once modelValue no longer matches either.
const resolved = ref(props.modelItem && props.modelItem.id === props.modelValue ? props.modelItem : null);

watch(() => props.modelValue, (id) => {
    if (id === null) {
        resolved.value = null;
    } else if (resolved.value?.id !== id) {
        resolved.value = props.modelItem?.id === id ? props.modelItem : resolved.value;
    }
});

const selected = computed(() => (props.modelValue !== null ? resolved.value : null));

const search = debounce(() => {
    if (!query.value) {
        results.value = [];
        loading.value = false;
        return;
    }
    loading.value = true;
    window.axios.get(route("items.search"), { params: { q: query.value } })
        .then(({ data }) => { results.value = data; })
        .finally(() => { loading.value = false; });
}, 250);

watch(query, () => {
    open.value = true;
    search();
});

const select = (item) => {
    resolved.value = item;
    emit("update:modelValue", item.id);
    query.value = "";
    results.value = [];
    open.value = false;
};

const clear = () => {
    resolved.value = null;
    emit("update:modelValue", null);
    query.value = "";
    results.value = [];
};

// Close the dropdown when focus leaves the whole picker (input + results).
const onFocusOut = (event) => {
    if (!event.currentTarget.contains(event.relatedTarget)) {
        open.value = false;
    }
};
</script>

<template>
    <div class="relative" @focusout="onFocusOut">
        <div v-if="selected" class="flex items-center gap-2 rounded border border-base-300 p-2">
            <img v-if="selected.icon" :src="`data:image/jpeg;base64,${selected.icon}`"
                 class="h-6 w-6 shrink-0 object-contain" alt="">
            <span class="flex-1 truncate text-sm">{{ selected.name }}</span>
            <button type="button" class="text-base-content/50 hover:text-base-content" @click="clear">
                ✕
            </button>
        </div>
        <template v-else>
            <TextInput v-model="query" type="text" :placeholder="placeholder" class="w-full"
                       :error="error" @focus="open = true" />
            <div v-if="open && (loading || query)"
                 class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded pack-bg-card border pack-accent-border resource-pack-border">
                <p v-if="loading" class="p-2 text-xs text-base-content/50">Searching…</p>
                <template v-else>
                    <button v-for="item in results" :key="item.id" type="button"
                            class="flex w-full items-center gap-2 p-2 text-left text-sm hover:bg-base-content/10"
                            @click="select(item)">
                        <img v-if="item.icon" :src="`data:image/jpeg;base64,${item.icon}`"
                             class="h-6 w-6 shrink-0 object-contain" alt="">
                        <span class="truncate">{{ item.name }}</span>
                    </button>
                    <p v-if="results.length === 0" class="p-2 text-xs text-base-content/50">No items found.</p>
                </template>
            </div>
        </template>
    </div>
</template>
