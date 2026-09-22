# About this application

RuneManager is a SaaS CMS that bridges Old School RuneScape with a managed
website. Each customer gets one instance running in exactly one **mode** —
CLAN, GROUP (Group Ironman), or CASUAL — chosen in Settings → General; switching
mode wipes all account data. Production runs at https://runemanager.com.

## Data flow

- The **RuneLite plugin** (separate repo at `/home/simon/runemanager-plugin`;
  build with `JAVA_HOME=/usr/lib/jvm/java-21-openjdk ./gradlew build`; commit it
  separately from the website) is the sole initiator of all game↔website
  communication. It exchanges the user's credentials for a Sanctum token, then
  pushes data to `/api/plugin/*` with `X-Account-Hash`, `X-Account-Username`,
  and `X-Account-Type` headers; the `plugin.account` middleware resolves those
  to an account (and records username changes). It also polls/acknowledges
  announcements and reports the active resource pack.
- The backend pulls two things itself: official OSRS hiscores
  (`hiscores:sync`, cadence read from settings via
  `Instance::hiscoreRefreshCron()` in `routes/console.php`) and the collection
  log from TempleOSRS (`collection-log:sync`, daily).
- **Storage split**: PostgreSQL for relational data (users, accounts, hiscores
  as dynamic jsonb key-value maps, equipment, diaries, combat achievements,
  settings, presence/position/vitals, calendar, announcements); MongoDB
  (`mongodb/laravel-mongodb`) for high-volume snapshots (bank, inventory,
  looting bag, loot log, quests, group bank, collection log) plus the static
  item/monster/prayer data (`docker/gamedata`, populated from the `osrsreboxed`
  dataset on `sail up`).
- **Real-time**: feed events broadcast over Laravel Reverb + Echo. In prod the
  WebSocket is served same-domain — nginx proxies `/app|/apps` to
  127.0.0.1:8080. `VITE_REVERB_*` values are baked into the JS bundle at CI
  build time from GitHub secrets.
- Instance-wide settings live in the key-value `settings` table behind
  `App\Support\Instance`. Nothing may *require* the DB during artisan boot
  (`routes/console.php` is loaded by every artisan call, including
  `package:discover` in CI) — `Instance::hiscoreRefreshMinutes()` falls back to
  a default when the table is unavailable; keep that property for anything new.

## Theming & resource packs

- UI is DaisyUI (Tailwind v4) with one custom `runemanager` theme (light +
  dark). Shared primitives live in `resources/js/Components/<Category>/`
  and are DaisyUI-classed; in-game resource-pack textures hook onto those
  class names. Folder layout rules are in the architecture guideline below.
- Packs live at `public/resource-packs/{name}/{kind}/{slug}.png` — **gitignored,
  downloaded at runtime**, persisted across deploys via a shared symlink.
  `sample-vanilla` ("Vanilla") is the bundled baseline; installs borrow
  missing sprites from it, and `resourcepack:ensure-vanilla` restores it on
  every deploy.
- **All icons resolve through one function**: `packIcon(kind, slug)` from
  `resources/js/composables/useResourcePackIcon.js`, applied with `v-bind` —
  it serves the active pack's sprite and falls back to Vanilla via
  `onError`. Do not add bespoke icon helpers. Users may select a pack, the
  instance default, or "No resource pack" (`users.disable_resource_pack`);
  icons still come from Vanilla in that case, only component textures
  are dropped.
- Component texturing comes from `resources/css/per-pack.css`, a template
  copied into each pack as `resource-pack.css` at install time. It is served
  raw (not Tailwind-processed): no `theme()` — literal hex or `var(--pack-*)`
  only. After editing the template, re-copy it into installed packs.
- Boss/clue/account-type icons have no pack equivalent and stay as plain
  `{ src: '/images/...' }` from `public/images/`.

## Development & deployment

- Everything runs through Sail: `./vendor/bin/sail artisan test`,
  `./vendor/bin/sail npm run build`, etc.
- Deploys are zero-downtime via GitHub Actions: build artifact → scp →
  `.github/deployment/deploy.sh` (releases/N + `current` symlink; shared
  across releases: `storage`, `.env`, `public/resource-packs`). Hooks:
  `before-activation.sh` (storage:link, caches, `migrate --force`,
  `resourcepack:ensure-vanilla`) and `after-activation.sh`
  (schedule:interrupt, queue:restart, supervisor restart of
  `runemanager-reverb`). Prod runtime: supervisor programs
  `runemanager-reverb` + `runemanager-worker`, cron `schedule:run` every
  minute, `SESSION_DRIVER=database`, and `URL::forceScheme('https')` when
  `app.url` is https (TLS terminates at the proxy).
- The production database is migrated — schema changes need **new** migration
  files; do not fold changes into existing migrations anymore.
- The user pushes commits themselves; never `git push`.

<laravel-boost-guidelines>
=== .ai/architecture rules ===

# RuneManager architecture

The product overview, data flow and storage split are covered in the hand-written part of CLAUDE.md. The rules below describe how to extend the application.

## Controllers: one per page

- Each Inertia page gets its own controller. Its `index`/`show` method renders the page, and `edit`/`update` (or `store`/`destroy`) handle the page's own form. Why: splitting pages across controllers keeps each class small, and the controller namespace mirrors `resources/js/Pages/`.
- Put controllers in a namespace that mirrors the page directory. For example, `Admin\GeneralSettingsController` renders `Admin/Settings/General/Index`, `Hiscores\LootController` renders `Hiscores/Loot/Index` and `Hiscores/Loot/Show`, and `ThemeController` renders `Themes/Index`. Several controllers may render the same page when only the data differs: `Hiscores\OverallController` and `Hiscores\SkillController` both render `Hiscores/Skills/Show`, and `Hiscores\CollectionController` renders `Hiscores/Bosses/Show` for both bosses and clues.
- JSON and mutation endpoints that are not a page can live on a resource controller for their domain (`UserResourcePackController`: update, install, status, hub, destroy, updateFromPlugin). Why: a JSON endpoint has no page to mirror.
- Route URLs and route names are a public contract. The Vue code calls Ziggy's `route('admin.settings')`, and the plugin calls fixed URLs. When code is refactored, only the controller target in `routes/web.php` may change. Check with `vendor/bin/sail artisan route:list --except-vendor` before and after.
- `routes/web.php` imports controller namespaces (`use App\Http\Controllers\Admin;`) and refers to `Admin\DashboardController::class`. Why: several classes share a short name (for example, the root `DashboardController` and `Admin\DashboardController`).
- Management routes are gated by permission middleware. Under `/admin`, `can:manage instance` covers settings, packs and the admin calendar, and `can:manage members` covers the member roster. `can:manage calendar` and `can:manage announcements` gate the create, update and delete routes of the public calendar and announcements pages. The permission names are constants in `App\Support\Roles`. `Owner` passes every check through `Gate::before`. Use these permissions for new management surfaces; do not add checks on role names.

## Controllers stay thin

- A controller method validates (a Form Request in `app/Http/Requests`), authorizes (permission middleware or `abort_unless`), calls one model method or service, and returns a response. Why: the same rule then serves web, plugin and artisan callers, and can be tested without HTTP.
- Move code into `app/Services/<Domain>/` once it has domain rules, filesystem or Mongo aggregation work, or a second caller. Leave single-use prop shaping and 1–3 line Eloquent calls in the controller. Do not add interfaces or repositories.
- Naming: a class that performs one change is a verb phrase (`ResetInstanceData::run()`, `ApplyInstanceSettings::run()`, `RecordLoot::record()`, `SelectResourcePack::select()`). A read or query service is a noun (`ResourcePackHub`, `HiscoreLeaderboard`, `LootLeaderboard`, `CollectionLogView`, `InstanceStats`). Inject services as method or constructor parameters.
- Reusable query constraints are model scopes. Instance settings are typed accessors on `Instance` (for example `Instance::defaultResourcePackId()`). Response shapes shared by more than one caller are API Resources in `app/Http/Resources`.
- Controller traits are not a place for business logic; use a service.

## Validation and API resources

- Validate every request body in a FormRequest (`app/Http/Requests/<Verb><Thing>Request.php`, flat), never with `$request->validate()` or `Validator::make` in a controller. Read input back through `$request->validated()`; uploaded files (`$request->file()`) and boolean flags (`$request->boolean()`) are still read from the request. Why: rules stay in one testable place, and plugin/web controllers only do work.
- `authorize()` returns `true` when route middleware (`auth`, `plugin.account`, `can:`) already guards the route. Put a check in `authorize()` only if it must return 403 *before* validation, and return `Response::deny('message')` to keep the message. Ownership checks that need a validated id stay in the controller. Why: a FormRequest validates before the controller body runs, so a guard left in the body would turn 403s into 422s.
- Exceptions: Fortify/Jetstream actions keep `Validator::make()->validateWithBag()` (vendor contract and named error bags), and optional GET query params that fall back to a default on bad input are read directly.
- Shape shared or eager-load-sensitive output with an `app/Http/Resources` class and pass `->resolve()` to Inertia. The one exception is a paginated collection, which is passed unresolved for `links`/`meta`. A resource that hydrates Mongo items or relations for a list does it once in a static `collection()`/`collectionWith()` (see `CalendarEventResource`). `AccountResource` is the exception: its `icon` reads the owner, so callers eager-load `user`. A single-use prop shape stays inline in the controller.

## Pages and components (resources/js)

- Every page is a directory: `Pages/<Page>/Index.vue` for a list or single view, `Show.vue` for a record. Components used by only that page go in `Pages/<Page>/Partials/`. The render name matches the path (`Inertia::render('Admin/Calendar/Index')`).
- Shared components go in `Components/<Category>/`. The categories are `Auth`, `Buttons`, `Cards`, `Display`, `Form`, `Modals`, `Tables`, `ResourcePacks`, `Game/Feed` and `Game/Items`. A component used by exactly one page belongs in that page's `Partials/`, not in `Components/`. Pieces that only `AppLayout` uses go in `Layouts/Partials/`.
- Jetstream/Fortify pages keep the paths the vendor code fixes: `Auth/*`, `Profile/*`, `API/*`, `Teams/*`, `PrivacyPolicy`, `TermsOfService`. Why: the vendor code renders these names itself, so they cannot move into `<Page>/Index.vue` directories.
- Adding, removing or adding a variant to anything in `Components/` also means updating the styleguide (see the styleguide guideline).

## Instance mode and settings

- Read the mode only through `App\Support\Instance`: `mode()`, `isClan()`, `isGroup()`, `requiresRosterClaim()` (clan or group), `isConfigured()`. Do not compare raw strings against `SettingHelper::getSetting('instance_mode')`. Why: `mode()` sanitises unknown values to casual.
- A feature limited to one mode checks the mode in the controller. Pages return 404 (`abort_unless(Instance::isGroup(), 404)` in `GroupBankController`). Plugin endpoints turn the push into a no-op (`Api\GroupBankController::update()`, `Api\ClanController::roster()`). Admin actions return 403 with a message (`Admin\MemberController`).
- Instance-wide settings: add a typed accessor on `Instance` that falls back to a default. Write through `SettingHelper::setSetting($key, $value, $type)`. Why: controllers and Blade then share one default, and CLAUDE.md requires that artisan boot never needs the settings table.
- `EnsureInstanceConfigured` (web middleware group) closes the site until first-time setup is finished. Users with `manage instance` are redirected to `admin.settings`. Guests and all other users get `SetupInProgress/Index` rendered in place of the page they asked for. Exempt routes are listed in its `isExempt()`: `login*`, `register*`, `password.*`, `two-factor.*`, `admin.settings*`, `user.*`. A new route that must work before setup has to be added there.
- A mode switch into clan or group runs `App\Services\Instance\ResetInstanceData`, which wipes all account data. New account-scoped tables and Mongo collections must be added to that reset.

## Plugin API vs web

- `routes/api.php` → `/api/plugin/*` is the RuneLite plugin's push surface. It uses `auth:sanctum` plus the `plugin.account` middleware (`ResolvePluginAccount`), which resolves the OSRS account from `X-Account-Hash`, `X-Account-Username` and `X-Account-Type`. Controllers read the resolved account from the request and never from the URL. Why: usernames change, but the hash is stable.
- An endpoint that concerns the user rather than an OSRS account sits outside `plugin.account`. For example, `PUT /api/plugin/resource-pack` → `UserResourcePackController::updateFromPlugin`.
- Snapshot data (bank, inventory, quests, …) is pushed with `PUT` and overwrites the stored snapshot. Loot is append-only and pushed with `POST`.
- Every live-feed event goes through `App\Services\Feed\RecordFeedEvent`, which persists the event and broadcasts `FeedEventCreated`. Do not create `FeedEvent` rows directly. Why: otherwise connected browsers never receive the event. The admin feed thresholds (`Instance::feedLevelUpThresholds()`, `feedLootMinValue()`) do not change what gets recorded. They are shared Inertia props that the feed pages use to filter what they display.
- Plain web JSON endpoints, used by the frontend but not by the plugin (for example `styleguide.sprites`, `user.resource-pack.status`, `items.search`), live in `routes/web.php` behind the web middleware.

## Storage

- Relational data goes in PostgreSQL through normal Eloquent models. Snapshots and high-volume documents go in MongoDB on the `mongodb` connection. The static game data (the item catalogue) is read-only and lives on `mongodb-static`, filled from `docker/gamedata`; never write to it.
- A Mongo document cannot be the target of an Eloquent relation from a Postgres model. `Account` exposes its snapshots as accessors that look them up directly (see the models guideline).

=== .ai/models rules ===

# Eloquent model conventions

All models in `app/Models` follow these rules. Match them when you add or edit a model.

- Declare model configuration with class attributes, not properties: `#[Fillable([...])]`, `#[Hidden([...])]`, `#[Appends([...])]`, `#[Unguarded]`, `#[WithoutTimestamps]`, `#[Table(...)]`, `#[Connection(...)]` (all from `Illuminate\Database\Eloquent\Attributes`). Why: this keeps a model's configuration in one place above the class.
- Declare casts in a `protected function casts(): array` method. Never use a `$casts` property.
- Write local scopes as `#[Scope] protected function name(Builder $query, ...): void`. Do not use the `scopeName` prefix. Callers still use `Model::name()`.
- Write accessors as `protected function name(): Attribute { return Attribute::make(get: ...); }`. Do not write `getNameAttribute` methods.
- Do not add IDE-helper `@property` / `@method` docblocks. A short class docblock that explains what the model stores, and anything non-obvious, is welcome.

## MongoDB models

- Mongo models extend `MongoDB\Laravel\Eloquent\Model` and carry `#[Connection('mongodb')]` (account snapshots, loot, group bank) or `#[Connection('mongodb-static')]` (read-only game data: `Item`), plus `#[Table(key: '_id')]`.
- A new model on the `mongodb` connection must also be added to the per-test cleanup list in `tests/Pest.php`. Why: `RefreshDatabase` only rolls back SQL, so Mongo documents would otherwise leak between tests. If the model is account-scoped, it must also be added to `ResetInstanceData`.
- `Account` (Postgres) cannot have Eloquent relations to Mongo documents. Its snapshots (`inventory`, `lootingBag`, …) are `Attribute::make(get: fn () => Model::where('account_id', $this->id)->first())->withoutObjectCaching()`. Why: `withoutObjectCaching()` keeps a read that follows a plugin push fresh on the same instance.

## Deliberate exceptions (do not "fix")

- `Item` keeps `protected $keyType = 'int'`. The Mongo base model declares a string key type, and the `Table` attribute cannot override it.
- `Membership` keeps `public $incrementing = true`. A `#[Table]` attribute would also reset the `team_user` table name that the Jetstream parent declares.
- `Team` keeps its `$dispatchesEvents` map for the Jetstream team events.

=== .ai/resource-packs rules ===

# Resource packs

CLAUDE.md ("Theming & resource packs") covers the storage layout, the Vanilla baseline, `packIcon()` and the rule that `per-pack.css` is served raw. This file adds the mechanics behind them.

## Files and commands

- Pack logic lives in `app/Services/ResourcePacks/`. `InstallResourcePack` downloads, extracts and prepares a pack. `ResourcePackHub` lists the `melkypie/resource-packs` branches and is cached. `PackPalette` extracts the background and accent colours. `DeleteResourcePack` removes a pack. `SelectResourcePack` applies a user's pack choice (and installs-then-selects for both the website hub install and the plugin push). `PackInstallQuota` enforces the member install cap. `PackSpriteInventory` lists which sprites a pack borrowed from Vanilla (for the styleguide). Reuse these services; do not write a second download or extract path.
- Artisan: `resourcepack:fetch {name}` installs a pack. `resourcepack:switch {name}` sets the instance default. `resourcepack:ensure-vanilla` restores Vanilla. `resourcepack:sync-css` re-copies the template into every installed pack, re-borrows missing sprites from Vanilla and re-runs the sprite normalisers. Both `ensure-vanilla` and `sync-css` run on every deploy (`before-activation.sh`).
- A pack counts as installed when `public/resource-packs/{name}/resource-pack.css` exists (`InstallResourcePack::isInstalled()`) and its row's `version` is not `'pending'`. Check this with `InstallResourcePack::isReady()`.

## per-pack.css template

- `resources/css/per-pack.css` is copied into each pack as `resource-pack.css`. Its `url('…png')` paths resolve relative to that pack's own directory. Only the `:root` block is rewritten per pack (`renderTemplateFor()`, which decides whether the button face tiles or stretches).
- After editing the template, run `vendor/bin/sail artisan resourcepack:sync-css`. Why: installed packs keep their old copy until then, and in production packs survive deploys through the shared symlink.
- The installer's list of sprites to borrow from Vanilla is built from the template's own `url()` references (`referencedAssets()`). When you start referencing a new sprite, `sync-css` fills it into packs that lack it. Do not hand-copy sprites.
- `completeFromVanilla()` also fills in the image directories the frontend addresses directly (`equipment`, `skill`). If a sprite is still missing at runtime, `packIcon()` falls back to Vanilla's copy.

## Which pack a viewer sees

- `ResourcePack::effectiveFor($user)` is the only resolver. The order is:
  1. The user's "no resource pack" opt-out (`users.disable_resource_pack`), or a guest's `resource_pack` cookie set to `none`. Either one returns `null`.
  2. The guest cookie's pack id, or the user's `effectiveResourcePackId()`.
  3. The instance default `settings.resource_pack_id`.
  4. Vanilla.

  `app.blade.php` and `HandleInertiaRequests` both call it. Never re-implement this chain.
- The shared Inertia prop `pack` is `{ name, version }`, where `version` is `updated_at`'s timestamp and busts caches. `no_pack` and `theme` sit next to it. `packIcon()` reads `page.props.pack` unless the caller passes an override ref, which only the styleguide switcher does.
- The resource-pack and dark-mode cookies are excluded from encryption in `bootstrap/app.php`, because Blade reads them to theme the first paint.

## Dark mode

- `Instance::resolveDarkMode($user, $pack)` resolves in this order: the user's own toggle, then the guest `dark_mode` cookie, then the owner's `default_dark_mode`, then the pack's `dark_mode` flag. Vanilla is always light and hides the toggle (`canToggleDarkMode()`). `Instance::themeFor()` picks the DaisyUI theme (`runemanager`, `runemanager-dark`, `runemanager-vanilla`).
- Picking a different pack clears the stored dark-mode choice (`App\Services\ResourcePacks\SelectResourcePack::select()` for users; the guest path in `UserResourcePackController` forgets the cookie). Why: a toggle set while an earlier pack was active would otherwise win forever and put a dark-drawn pack under the light theme. Re-saving the same pack must not clear it.

## Installing and deleting

- Hub installs from the website (`UserResourcePackController::install`, `Admin\ResourcePackController::store`) queue `FetchResourcePackJob` and answer with JSON. The page then polls `user.resource-pack.status` until the pack counts as installed.
- A member's installs are capped by `config('runemanager.resource_packs.user_install_limit')`. Holders of `manage instance` are exempt. Only a pack new to the instance counts toward the cap. The quota logic lives in `App\Services\ResourcePacks\PackInstallQuota`, which both the Themes page and the install endpoint use.
- The plugin reports the pack that is active in-game through `PUT /api/plugin/resource-pack` (a pack name, outside `plugin.account`). An unknown name gets a stub row (`version`/`author` = `'pending'`, `installed_by_user_id` set), is selected for the user and queues the fetch. The endpoint returns 202 while queued and 200 once installed.
- Deleting goes through `DeleteResourcePack::run()`, which resets affected users' picks, clears the instance default when needed, and removes the files and the row. Vanilla can never be deleted. Members may delete only their own installs, and never the current instance default.

=== .ai/styleguide rules ===

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

=== .ai/testing rules ===

# Testing notes

- Feature tests run on SQLite (`database/testing.sqlite`, set in `phpunit.xml`), not PostgreSQL, and on the `runemanager_testing` Mongo database. Keep queries portable. Do not use PostgreSQL-only SQL such as jsonb operators or `ILIKE`; filter jsonb maps in PHP or with Eloquent's JSON helpers. Why: SQL that only works on one database passes in either production or the suite, but not both.
- `RefreshDatabase` rolls back SQL only. `tests/Pest.php` deletes the documents of every `mongodb` model before each test, so a new Mongo model must be added to that list. Why: SQLite reuses primary keys after a rollback, so leftover Mongo documents would attach themselves to a fresh test's account.
- Inertia testing has `ensure_pages_exist` enabled (`config/inertia.php`). A `->component('X/Index')` assertion fails if `resources/js/Pages/X/Index.vue` does not exist, so rename a page's render name in the controller, the Vue path and the test in the same change.
- The styleguide route is open without login only in the `local` environment. Tests run as `testing`, so they exercise the gated `manage instance` branch.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/boost (BOOST) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- tightenco/ziggy (ZIGGY) - v2
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- laravel-echo (ECHO) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`). Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.
- Inspect routes with `vendor/bin/sail artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `vendor/bin/sail artisan config:show app.name`, `vendor/bin/sail artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `vendor/bin/sail artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `vendor/bin/sail artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `vendor/bin/sail artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `vendor/bin/sail artisan make:test --pest SomeFeatureTest` instead of `vendor/bin/sail artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `vendor/bin/sail artisan test --compact` or filter: `vendor/bin/sail artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
