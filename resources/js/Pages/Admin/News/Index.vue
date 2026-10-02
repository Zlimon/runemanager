<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { formatArticleDateTime } from '@/composables/useAdminFormatters.js';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import DataTable from '@/Components/Tables/DataTable.vue';
import Badge from '@/Components/Display/Badge.vue';
import Search from '@/Components/Form/Search.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';

/*
 * Admin → News: every post, drafts and scheduled ones included (the public
 * archive only shows those to news managers browsing it themselves).
 * Writing, editing and deleting reuse the existing `news.*` routes — this
 * page is only a searchable index over them.
 */
const props = defineProps({
    // Paginated: { data: [{ id, title, slug, published_at, is_published, created_by, … }], links, meta }
    posts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const reload = debounce(() => {
    router.get(route('admin.news'), search.value ? { search: search.value } : {}, {
        only: ['posts', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(search, reload);

const status = (post) => {
    if (post.published_at === null) {
        return { label: 'Draft', variant: 'neutral' };
    }
    if (!post.is_published) {
        return { label: 'Scheduled', variant: 'warning' };
    }
    return { label: 'Published', variant: 'success' };
};

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'created_by', label: 'Author', sortable: false },
    { key: 'published_at', label: 'Date', sortable: false },
    { key: 'actions', label: '', align: 'right', sortable: false },
];

const dt = formatArticleDateTime;

const destroy = (post) => {
    if (!confirm(`Delete "${post.title}"? This cannot be undone.`)) {
        return;
    }
    router.delete(route('news.destroy', post.slug), { preserveScroll: true });
};
</script>

<template>
    <SettingsLayout title="News">
        <template #actions>
            <Link :href="route('news.create')">
                <PrimaryButton>Write article</PrimaryButton>
            </Link>
        </template>

        <div class="mb-4 w-full sm:w-72">
            <Search v-model="search" placeholder="Search by title…" />
        </div>

        <DataTable :columns="columns" :paginator="posts" :only="['posts', 'filters']" row-key="id">
            <template #cell-title="{ row }">
                <Link :href="route('news.show', row.slug)" class="link font-medium">{{ row.title }}</Link>
            </template>
            <template #cell-status="{ row }">
                <Badge size="sm" :variant="status(row).variant">{{ status(row).label }}</Badge>
            </template>
            <template #cell-created_by="{ row }">{{ row.created_by.name ?? '—' }}</template>
            <template #cell-published_at="{ row }">{{ dt(row.published_at ?? row.created_at) }}</template>
            <template #cell-actions="{ row }">
                <div class="flex justify-end gap-2">
                    <Link :href="route('news.edit', row.slug)">
                        <SecondaryButton type="button" class="btn-xs">Edit</SecondaryButton>
                    </Link>
                    <DangerButton type="button" class="btn-xs" @click="destroy(row)">Delete</DangerButton>
                </div>
            </template>
            <template #empty>No news posts match this search.</template>
        </DataTable>
    </SettingsLayout>
</template>
