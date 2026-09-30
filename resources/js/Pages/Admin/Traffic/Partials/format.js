/*
 * Formatting shared by the plugin traffic pages: body sizes, latencies,
 * ratios and the ISO 8601 timestamps the server sends. `formatBytes` and
 * `formatWhen` moved to the cross-page composables/useAdminFormatters.js
 * (Admin/Dashboard needed the same byte formatter) — re-exported under
 * their original names here so nothing importing from this file has to change.
 */
export { formatBytes, formatShortDateTime as formatWhen } from '@/composables/useAdminFormatters.js';

export const formatMs = (ms) => `${Number(ms).toFixed(ms >= 100 ? 0 : 1)} ms`;

export const formatPercent = (ratio) => `${(ratio * 100).toFixed(ratio > 0 && ratio < 0.1 ? 1 : 0)}%`;

// Human labels for the logger's flag keys.
export const flagLabels = {
    unauthenticated: 'Unauthenticated',
    forbidden: 'Forbidden',
    account_conflict: 'Account conflict',
    validation_failed: 'Validation failed',
    missing_headers: 'Missing headers',
    throttled: 'Throttled',
    oversized: 'Oversized',
    server_error: 'Server error',
    suspended: 'Suspended',
};

// The badge colour a flag reads as: hijack attempts and throttling stand out.
export const flagVariant = (flag) => ({
    account_conflict: 'error',
    throttled: 'warning',
    oversized: 'warning',
    server_error: 'error',
    suspended: 'error',
}[flag] ?? 'neutral');
