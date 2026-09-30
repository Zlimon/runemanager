/*
 * A short, locale-aware date + time for the user admin pages' timestamps
 * (ISO 8601 strings from the server). `fallback` shows for a null value.
 * Moved to the cross-page composables/useAdminFormatters.js (other admin
 * pages had their own copy of the same idea) — re-exported here under its
 * original name so nothing importing from this file has to change.
 */
export { formatDateTime as formatDate } from '@/composables/useAdminFormatters.js';
