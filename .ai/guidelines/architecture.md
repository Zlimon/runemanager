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
