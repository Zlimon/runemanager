# Styleguide

`/styleguide` (route `styleguide`, `StyleguideController` → `Pages/Styleguide/Index.vue`) shows every shared component on one page. Its pack switcher repaints the page in any installed pack without changing anyone's saved selection. Use it to check components against every pack. Why: a texture regression often shows on only one pack (for example, a frame that nearly matches its face hides a mismatched fill), so each component has to be seen under all packs.

## Rules

- When you add or remove a component in `resources/js/Components/`, or give one a new variant, prop-driven state or size, update `Pages/Styleguide/Index.vue` in the same change. Why: a component missing from the styleguide never gets checked against every pack.
- Place the demo in the section named after the component's `Components/` category (Buttons, Display, Form, Cards, Tables, Resource packs, Game — Feed, Game — Items, Auth). Each category is rendered twice, once straight on the page and once inside a `Card`, because textured controls leave their centre unpainted and the surface behind them is part of what is being reviewed. Modals have their own section.
- Show every variant with realistic data. Use the real catalogue items the controller passes in `items` (`StyleguideController::SAMPLE_ITEM_IDS`) rather than invented items.
- Pack icons in demos go through `packIcon()` with the page's preview-pack override, so icons follow the switcher.
- Only components that cannot be demoed safely are left out. The deliberate exemptions are:
  - `DarkModeToggle`: clicking it saves the viewer's real preference, and it is already in the AppLayout header.
  - `AuthenticationCard`: a full-screen auth wrapper; its logo (`AuthenticationCardLogo`) is shown.
  - `GuestPackPicker`: renders nothing for logged-in users, and the styleguide is admin-only.
  - `ResourcePackInstallModal`: it only opens after `start()` POSTs a real install; its look is shown under Display.
  - `SectionTitle`: shown inside `FormSection` and `ActionSection`.

  Add any new exemption to this list with its reason.
- If you change the page's props (`packs`, `items`) or the `styleguide.sprites` JSON shape, update `tests/Feature/StyleguideTest.php`.

## Access and the sprites endpoint

- In the `local` environment the route needs no login, so a browser check can open it directly. Everywhere else it is gated by `auth:sanctum`, `verified` and `can:manage instance`. The middleware is chosen when routes register, in `routes/web.php`.
- `GET /styleguide/sprites?pack={name}` (`styleguide.sprites`) returns a pack's sprite inventory as plain JSON, listing its own sprites against those borrowed from Vanilla. It is deliberately not an Inertia prop or visit. Why: every Inertia visit re-sends the shared `theme` prop, which `DarkModeToggle` watches, and that would undo the pack the switcher is previewing.
