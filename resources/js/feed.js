/**
 * Every level-up is stored as a feed event, but the feed only shows the
 * configured milestone levels (SPEC §8.2 — milestones are a display filter).
 * Other event types always pass through.
 *
 * @param {Array} events
 * @param {Array<number>} milestones
 */
export function visibleFeedEvents(events, milestones) {
    if (!milestones || milestones.length === 0) {
        return events;
    }

    return events.filter(
        (event) => event.type !== 'level_up' || milestones.includes(event.payload?.level),
    );
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

