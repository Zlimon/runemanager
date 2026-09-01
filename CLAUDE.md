# About this application

RuneManager is a SaaS CMS that bridges Old School RuneScape with a managed
website. Each customer gets one instance running in exactly one **mode** —
CLAN, GROUP (Group Ironman), or CASUAL — chosen in Settings → General; switching
mode wipes all account data. Production runs at https://runemanager.com.
Remaining feature work is tracked in `SPEC.md`.

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
  osrsbox item DB.
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

- UI is DaisyUI (Tailwind v3) with one custom `runemanager` theme (light +
  dark). Shared primitives in `resources/js/Components/` (buttons, inputs,
  modals, dropdowns…) are DaisyUI-classed; in-game resource-pack textures hook
  onto those class names.
- Packs live at `public/resource-packs/{name}/{kind}/{slug}.png` — **gitignored,
  downloaded at runtime**, persisted across deploys via a shared symlink.
  `sample-vanilla` ("Default Vanilla") is the bundled baseline; installs borrow
  missing sprites from it, and `resourcepack:ensure-vanilla` restores it on
  every deploy.
- **All icons resolve through one function**: `packIcon(kind, slug)` from
  `resources/js/composables/useResourcePackIcon.js`, applied with `v-bind` —
  it serves the active pack's sprite and falls back to Default Vanilla via
  `onError`. Do not add bespoke icon helpers. Users may select a pack, the
  instance default, or "No resource pack" (`users.disable_resource_pack`);
  icons still come from Default Vanilla in that case, only component textures
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
