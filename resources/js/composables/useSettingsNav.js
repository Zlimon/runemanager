/*
 * The settings sidebar's sections, each a dropdown of pages: "Account" (the
 * user's own pages) for everyone and "Admin" (every instance surface, grouped
 * by domain) for admins. SettingsNav renders them; the section holding the
 * current page opens, the other stays collapsed.
 *
 * Every item is only listed when its route is registered (`route().has()`),
 * and groups left empty are dropped, so members-only surfaces disappear on a
 * casual instance rather than 404.
 *
 * @param {object} page - the Inertia page object (`usePage()`'s return value).
 */
export function settingsNavSections(page) {
    const sections = [
        {
            key: 'account',
            label: 'Account',
            groups: [{
                heading: null,
                items: [
                    { label: 'Profile', route: 'profile.show', active: 'profile.show' },
                    { label: 'Appearance', route: 'themes.index', active: 'themes.*' },
                    { label: 'Privacy', route: 'privacy.index', active: 'privacy.*' },
                    { label: 'Screenshots', route: 'screenshots.index', active: 'screenshots.*' },
                    ...(page.props.jetstream?.hasApiFeatures
                        ? [{ label: 'API tokens', route: 'api-tokens.index', active: 'api-tokens.*' }]
                        : []),
                ],
            }],
        },
    ];

    if (page.props.is_admin) {
        sections.push({
            key: 'admin',
            label: 'Admin',
            groups: [
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
                        { label: 'Calendar', route: 'admin.calendar', active: 'admin.calendar*' },
                        { label: 'Screenshots', route: 'admin.screenshots', active: 'admin.screenshots*' },
                    ],
                },
                {
                    heading: 'Appearance',
                    items: [
                        { label: 'Resource packs', route: 'admin.packs', active: 'admin.packs*' },
                        { label: 'Branding', route: 'admin.branding', active: 'admin.branding*' },
                    ],
                },
                {
                    heading: 'Settings',
                    items: [
                        { label: 'General', route: 'admin.settings', active: 'admin.settings' },
                        { label: 'Feed & sync', route: 'admin.feed', active: 'admin.feed*' },
                        { label: 'Integrations', route: 'admin.integrations', active: 'admin.integrations*' },
                    ],
                },
            ],
        });
    }

    return sections
        .map((section) => ({
            ...section,
            groups: section.groups
                .map((group) => ({ ...group, items: group.items.filter((item) => route().has(item.route)) }))
                .filter((group) => group.items.length > 0),
        }))
        .filter((section) => section.groups.length > 0);
}
