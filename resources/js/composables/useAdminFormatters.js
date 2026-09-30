import dayjs from 'dayjs';

/*
 * Shared formatters for the admin pages. Before this file existed, several
 * admin pages each defined their own copy — a byte formatter in
 * Admin/Dashboard/Index.vue and Admin/Traffic/Partials/format.js, and four
 * near-identical date/time formatters across Admin/Users, Admin/Traffic,
 * Admin/News and Admin/Announcements. Each export below keeps the exact
 * format its original call site used (documented per function), so moving
 * them here doesn't change what any page renders — it only gives the next
 * admin page one place to reuse instead of writing a fifth copy.
 */

/**
 * "1.2 MB" style, with a GB tier and a null-safe fallback — the superset of
 * every formatBytes() this replaces (Admin/Dashboard's version already
 * needed both; Admin/Traffic's byte values are never null, so gaining the
 * fallback and GB tier changes nothing there).
 */
export const formatBytes = (bytes, fallback = '—') => {
    if (bytes === null || bytes === undefined) {
        return fallback;
    }
    if (bytes >= 1024 * 1024 * 1024) {
        return `${(bytes / 1024 / 1024 / 1024).toFixed(1)} GB`;
    }
    if (bytes >= 1024 * 1024) {
        return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
    }
    if (bytes >= 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }
    return `${bytes} B`;
};

/** "Jan 5, 2026, 3:45 PM" — Admin/Users' original format (formerly Partials/formatDate.js). */
export const formatDateTime = (value, fallback = '—') => (value
    ? new Date(value).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
    : fallback);

/** "1/5/26, 3:45:12 PM" — Admin/Traffic's original `formatWhen` (includes seconds). */
export const formatShortDateTime = (value, fallback = '—') => (value
    ? new Date(value).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'medium' })
    : fallback);

/** "Jan 5, 2026 3:45 PM" — Admin/News and Admin/Announcements' original `dt()`. */
export const formatArticleDateTime = (value, fallback = '—') => (value
    ? dayjs(value).format('MMM D, YYYY h:mm A')
    : fallback);
