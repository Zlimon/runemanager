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
