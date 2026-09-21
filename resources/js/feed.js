/**
 * Admin display filters for the Live Feed surfaces (SPEC §8.2). Everything is
 * stored; the Live Feed shows only milestone level-ups and loot at or above
 * the minimum value. The account Activity feed doesn't use this — it shows
 * every event the plugin posted.
 *
 * @param {Array} events
 * @param {Array<number>} milestones
 * @param {number} minLootValue
 */
export function visibleFeedEvents(events, milestones, minLootValue = 0) {
    return events.filter((event) => {
        if (event.type === 'level_up' && milestones?.length > 0) {
            return milestones.includes(event.payload?.level);
        }
        if (event.type === 'loot_drop' && minLootValue > 0) {
            return (event.payload?.total_value ?? 0) >= minLootValue;
        }
        return true;
    });
}

const formatSkill = (slug) => (slug ?? '').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

/** Human sentence for a feed event, shared by the feed rows and the gallery. */
export function feedSentence(event) {
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
}

/** Generic achievement image, for events with no sprite of their own. */
const TROPHY = { src: '/images/trophy.png' };

/*
 * A loot source's boss portrait from /images/boss, which names its files as
 * hyphenated slugs ("Theatre of Blood" -> theatre-of-blood.png).
 *
 * A source does not always name a file — "Barrows" is barrows-chests.png, and
 * chests and clue tiers have no portrait at all — so a miss falls back to the
 * trophy rather than leaving a broken image. The src guard stops it looping if
 * the trophy itself fails.
 */
const lootIcon = (source) => {
    const slug = (source ?? '')
        .toLowerCase()
        .replace(/['’]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    return {
        src: `/images/boss/${slug}.png`,
        onError: (event) => {
            if (!event.target.src.endsWith(TROPHY.src)) {
                event.target.src = TROPHY.src;
            }
        },
    };
};

/**
 * Icon for a feed event, as <img> attributes to spread with v-bind:
 *
 *   <img v-bind="feedIcon(event, packIcon)" class="h-6 w-6">
 *
 * Takes the caller's packIcon rather than calling useResourcePackIcon() here:
 * a composable belongs in a component's setup, and receiving it keeps pack
 * sprites on the one resolver, with its fallback to Vanilla when the active
 * pack omits a sprite.
 */
export function feedIcon(event, packIcon) {
    const { type, payload } = event;
    switch (type) {
        case 'level_up':
            return packIcon('skill', payload.skill);
        case 'loot_drop':
            return lootIcon(payload.source);
        case 'quest_complete':
            return packIcon('quests_tab', 'blue_quests_icon');
        case 'combat_achievement':
            return packIcon('quests_tab', 'combat_achievements_icon');
        case 'collection_log':
            return packIcon('quests_tab', 'combat_achievements_collections_logged');
        case 'death':
            return packIcon('button', 'equipment_items_lost_on_death');
        default:
            // Pets, rewards and anything unrecognised have no sprite in any pack.
            return TROPHY;
    }
}
