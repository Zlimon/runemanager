# Resource packs

CLAUDE.md ("Theming & resource packs") covers the storage layout, the Vanilla baseline, `packIcon()` and the rule that `per-pack.css` is served raw. This file adds the mechanics behind them.

## Files and commands

- Pack logic lives in `app/Services/ResourcePacks/`. `InstallResourcePack` downloads, extracts and prepares a pack. `ResourcePackHub` lists the `melkypie/resource-packs` branches and is cached. `PackPalette` extracts the background and accent colours. `DeleteResourcePack` removes a pack. `SelectResourcePack` applies a user's pack choice (and installs-then-selects for both the website hub install and the plugin push). `PackInstallQuota` enforces the member install cap. `PackSpriteInventory` lists which sprites a pack borrowed from Vanilla (for the styleguide). Reuse these services; do not write a second download or extract path.
- Artisan: `resourcepack:fetch {name}` installs a pack. `resourcepack:ensure-vanilla` restores Vanilla. `resourcepack:sync-css` re-copies the template into every installed pack, re-borrows missing sprites from Vanilla and re-runs the sprite normalisers. Both `ensure-vanilla` and `sync-css` run on every deploy (`before-activation.sh`). The instance-wide default pack is set from Settings → General.
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
