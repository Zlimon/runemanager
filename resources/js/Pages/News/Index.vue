<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import NewsCard from '@/Components/Cards/NewsCard.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import PaginationItem from '@/Components/Tables/PaginationItem.vue';

defineProps({
    // A paginated collection — passed unresolved by the controller, so it
    // carries `data`, `links` and `meta`.
    posts: { type: Object, required: true },
    can_manage: { type: Boolean, default: false },
});
</script>

<template>
    <AppLayout title="News">
        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold">News</h1>
                    <Link v-if="can_manage" :href="route('news.create')">
                        <PrimaryButton>Write article</PrimaryButton>
                    </Link>
                </div>

                <Card v-if="posts.data.length === 0" padding="p-6"
                      class="mt-6 text-center text-base-content/60">
                    No news yet.
                </Card>

                <div v-else class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <NewsCard v-for="post in posts.data" :key="post.id" :post="post" />
                </div>

                <nav v-if="posts.meta && posts.meta.last_page > 1"
                     class="mt-8 flex justify-center" aria-label="Pagination">
                    <div class="join border border-base-300">
                        <PaginationItem v-for="(link, index) in posts.meta.links" :key="index"
                                        :href="link.url"
                                        :active="link.active"
                                        :disabled="!link.url">
                            <span v-html="link.label" />
                        </PaginationItem>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
