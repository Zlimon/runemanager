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

