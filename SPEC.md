# RuneManager — Remaining Work

> Version 3.0
> Last updated: 2026-07-03

The full v2.0 product specification has been retired: everything it described as
implemented is now built and documented in `CLAUDE.md` (§ "About this
application"). This file tracks only what **remains**.

---

## 1. Implemented (summary)

All core product surface is done:

- Three instance modes (CLAN / GROUP / CASUAL) with roster claiming, GIM group
  validation, and mode-switch data wipe.
- RuneLite plugin integration: auth, push sync for all data types (inventory,
  bank, group bank, looting bag, equipment, loot, quests, diaries, combat
  achievements, position, vitals, activity/presence, avatar, clan rank),
  announcement polling, resource-pack detection, screenshots.
- Backend pulls: official OSRS hiscores (admin-configurable cadence) and
  TempleOSRS collection log.
- Hiscores/leaderboards (overall, skills, bosses, clues, loot, collection log,
  combat achievements, diaries), live feed over WebSockets, live map,
  announcements, calendar, outbound Discord webhook.
- Theming: DaisyUI `runemanager` theme (light/dark) + full resource-pack
  texturing, per-user pack selection incl. "No resource pack", bundled Default
  Vanilla baseline with automatic fallback, admin pack hub + reinstall.
- Admin settings hub (General, Branding, Feed & sync, Integrations, Members,
  Packs).
- Zero-downtime deployment to production (runemanager.com) with persistent
  resource packs, self-healing Vanilla, Reverb/queue/scheduler runtime.

## 2. Remaining

### 2.1 Role elevation & member promotion

Today the **Owner is the only admin**; all management permissions attach to the
Owner role alone.

- Per-mode automatic elevation: CLAN mode granting admin by in-game clan rank,
  GROUP mode granting it by group membership.
- Manual promotion: an admin action to promote/demote a member from the Members
  settings page.

### 2.2 OAuth registration

Registration is email + password only (Jetstream/Fortify). Social login
(primarily **Discord**) is not implemented — Socialite is not installed.

### 2.3 Icons with no resource-pack equivalent

Boss icons (~80), clue-tier icons, and account-type badges (ironman variants)
have no counterpart in any resource pack and are served as static files from
`public/images/`. Deliberately deferred; revisit only if a sprite source turns
up or packs start shipping them.

## 3. Out of scope

- **RuneManager Administrative System** — billing, provisioning, and instance
  lifecycle live in a separate system. The application assumes a validly
  provisioned instance and has no awareness of subscription state.
- **Per-player in-game event forwarding** — delegated to dedicated RuneLite
  plugins such as Dink; RuneManager only forwards its own website-originated
  events (announcements, calendar) to the instance webhook.
