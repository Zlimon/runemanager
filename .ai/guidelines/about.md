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
  settings, presence/position/vitals, calendar, announcements, news posts);
  MongoDB
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
- Deploys go through VitoDeploy (site 12 on the apps VM). A push to `master`
  runs `.github/workflows/ci-cd.yml`; when CI passes, its `deploy` job starts a
  Vito deployment. Vito's deploy script pulls the branch, runs `composer install
  --no-dev`, `npm ci && npm run build`, `migrate --force`, `optimize`,
  `resourcepack:ensure-vanilla`, `resourcepack:sync-css`,
  `screenshots:move-private`, and restarts the scheduler, queue and Reverb.
  Prod runtime: Supervisor runs Reverb and two `queue:work redis` workers,
  cron runs `schedule:run` every minute, `SESSION_DRIVER=database`, and
  `URL::forceScheme('https')` applies when `app.url` is https (TLS terminates
  at the proxy).
- The production database is migrated — schema changes need **new** migration
  files; do not fold changes into existing migrations anymore.
- The user pushes commits themselves; never `git push`.
