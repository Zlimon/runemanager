<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import LootItems from "@/Components/Game/LootItems.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";

/*
 * One row of the live feed — the account (with ironman badge), a relative
 * timestamp, the event sentence and, for loot drops, the dropped items.
 * Shared by the Live Feed page and the homepage activity widget.
 */
const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['deleted']);

const page = usePage();

// Instance admins, or the owner of the account the entry belongs to, may delete.
const canDelete = computed(() => page.props.is_admin
    || (page.props.auth?.user?.id != null && page.props.auth.user.id === props.event.account?.user_id));

const remove = () => {
    if (!confirm('Delete this feed entry?')) {
        return;
    }
    router.delete(route('feed.destroy', props.event.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => emit('deleted', props.event.id),
    });
};

const formatSkill = (slug) => slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const sentence = (event) => {
    const { type, payload } = event;
    switch (type) {
        case 'level_up':
            return `reached level ${payload.level} ${formatSkill(payload.skill)}`;
        case 'loot_drop':
            return `received a drop from ${payload.source}`;
        case 'quest_complete':
            return `completed ${payload.quest}`;
        case 'combat_achievement':
            return payload.tier
                ? `completed the ${formatSkill(payload.tier)} combat task: ${payload.task}`
                : `completed the combat task: ${payload.task}`;
        case 'collection_log':
            return `added ${payload.item} to their collection log`;
        case 'pet':
            return 'received a pet';
        case 'death':
            return 'died';
        case 'reward':
            return payload.source ? `opened ${payload.source} rewards` : 'received rewards';
        default:
            return type;
    }
};
</script>

<template>
    <div class="rounded p-3 pack-bg-card resource-pack-border">
        <div class="flex items-baseline justify-between">
            <Link :href="route('accounts.show', { account: event.account.username })"
                  class="flex items-center gap-2 font-semibold hover:underline">
                <img v-if="event.account.account_type === 'ironman'"
                     src="/images/ironman.png"
                     class="h-5 w-5 object-contain"
                     alt="">
                <img v-else-if="event.account.account_type && event.account.account_type !== 'normal'"
                     :src="`/images/${event.account.account_type}_ironman.png`"
                     class="h-5 w-5 object-contain"
                     alt="">
                {{ event.account.username }}
            </Link>
            <div class="flex items-center gap-2">
                <span class="text-xs text-base-content/60"
                      :title="dayjs(event.occurred_at).format('MMM D, YYYY h:mm A')">
                    {{ dayjs(event.occurred_at).fromNow() }}
                </span>
                <button v-if="canDelete" type="button" @click="remove" aria-label="Delete entry"
                        class="text-base-content/40 hover:text-error" title="Delete entry">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-7 0v12a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V7" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="mt-1 text-sm text-base-content/80">
            <p>{{ sentence(event) }}</p>
            <LootItems v-if="event.type === 'loot_drop'"
                       :items="event.payload.items"
                       :total-value="event.payload.total_value"
                       class="mt-1.5" />
            <ImageLightbox v-if="event.screenshot_url" :src="event.screenshot_url"
                           alt="Event screenshot" class="mt-2" />
        </div>
    </div>
</template>
