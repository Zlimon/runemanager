/*
 * Canonical list of admin surfaces, grouped by domain. Both AdminNav (the
 * admin area's own sidebar) and SettingsLayout (which adds an "Admin" group
 * so an admin can reach Users/Traffic/Content pages without leaving Settings)
 * render from this one list, so the two navs can't drift out of sync.
 *
 * `inSettingsHub: true` marks an item SettingsLayout already lists in its own
 * "Instance" section under a different heading (General/Branding/Feed &
 * sync/Integrations/Calendar) — filterForSettingsHub() drops those so the
 * "Admin" group it adds only carries what Instance doesn't already cover.
 *
 * @param {object} page - the Inertia page object (`usePage()`'s return value).
 */
export function adminNavGroups(page) {
    return [
        {
            heading: 'Overview',
            items: [
                { label: 'Dashboard', route: 'admin.dashboard', active: 'admin.dashboard' },
            ],
        },
        {
            heading: 'People',
            items: [
                { label: 'Users', route: 'admin.users', active: 'admin.users*' },
                ...(page.props.instance?.mode !== 'casual'
                    ? [{ label: 'Members', route: 'admin.members', active: 'admin.members*' }]
                    : []),
                { label: 'Plugin traffic', route: 'admin.traffic', active: 'admin.traffic*' },
            ],
        },
        {
            heading: 'Content',
            items: [
                { label: 'News', route: 'admin.news', active: 'admin.news*' },
                { label: 'Announcements', route: 'admin.announcements', active: 'admin.announcements*' },
                { label: 'Calendar', route: 'admin.calendar', active: 'admin.calendar*', inSettingsHub: true },
                { label: 'Screenshots', route: 'admin.screenshots', active: 'admin.screenshots*' },
            ],
        },
        {
            heading: 'Appearance',
            items: [
                { label: 'Resource packs', route: 'admin.packs', active: 'admin.packs*' },
                { label: 'Branding', route: 'admin.branding', active: 'admin.branding*', inSettingsHub: true },
            ],
        },
        {
            heading: 'Settings',
            items: [
                { label: 'General', route: 'admin.settings', active: 'admin.settings', inSettingsHub: true },
                { label: 'Feed', route: 'admin.feed', active: 'admin.feed*', inSettingsHub: true },
                { label: 'Integrations', route: 'admin.integrations', active: 'admin.integrations*', inSettingsHub: true },
            ],
        },
    ];
}

/** Keep only items whose route is registered, and drop groups left empty. */
export function filterRegisteredRoutes(groups) {
    return groups
        .map((group) => ({ ...group, items: group.items.filter((item) => route().has(item.route)) }))
        .filter((group) => group.items.length > 0);
}

/**
 * The flat item list SettingsLayout adds as its "Admin" group: every
 * registered admin route except the ones its own "Instance" section already
 * lists (see the `inSettingsHub` note above).
 */
export function adminItemsForSettingsHub(page) {
    return filterRegisteredRoutes(adminNavGroups(page))
        .flatMap((group) => group.items)
        .filter((item) => !item.inSettingsHub);
}
