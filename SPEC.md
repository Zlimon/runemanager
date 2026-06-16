# RuneManager — Product Specification

> Version 2.0  
> Last updated: 2026-06-16

This document describes the RuneManager product **as implemented**. Where the
build deliberately diverges from an earlier design intent, an
`> Implementation note` calls it out. Items not yet built are marked
**(not yet implemented)**.

---

## Table of Contents

1. [Overview](#1-overview)
2. [Modes](#2-modes)
3. [User Accounts & Authentication](#3-user-accounts--authentication)
4. [RuneLite Plugin Integration](#4-runelite-plugin-integration)
5. [Data Sync](#5-data-sync)
6. [Theming & Resource Packs](#6-theming--resource-packs)
7. [Leaderboards](#7-leaderboards)
8. [Live Feed](#8-live-feed)
9. [Announcements](#9-announcements)
10. [Calendar](#10-calendar)
11. [Outbound Webhooks](#11-outbound-webhooks)
12. [Administrative Backend](#12-administrative-backend)
13. [Technology Stack](#13-technology-stack)
14. [Business Model](#14-business-model)

---

## 1. Overview

RuneManager is a SaaS CMS that bridges Old School RuneScape (OSRS) with a managed website. It allows players — whether in a large clan, a Group Ironman team, or playing solo/casually — to display, share, and compete on in-game data through a public-facing website backed by live game data.

Data flows between the game and the website via the **RuneManager RuneLite plugin**. The plugin is the sole initiator of all communication: it pushes in-game data to the website backend, and it polls the backend to pull down notifications such as announcements. The website never initiates a connection to the game client. The plugin also handles authentication, theme synchronisation, and in-game display of website content.

Beyond stat tracking, the site surfaces a **public landing page** (recruitment-facing digest), a **live feed**, **leaderboards**, a **live map**, per-account profiles (inventory, bank, equipment, quests, diaries, collection log, combat achievements, loot, a 3D avatar, and live status orbs), **announcements**, and a **calendar**.

Each customer receives a managed instance of RuneManager. Instance management and billing are handled by a separate administrative system (out of scope for this spec).

---

## 2. Modes

Every RuneManager instance operates in exactly one mode, chosen at setup. The mode determines which features are available and what identity the instance is built around. Switching mode after setup wipes all account data and requires a typed confirmation.

### 2.1 CLAN

Intended for clan leaders who want a website representing their OSRS clan.

- Requires a **clan name** (auto-filled from the owner's clan once the plugin syncs the roster).
- Supports the full feature set: leaderboards, live feed, announcements, calendar, data sync for all members.
- The roster is seeded by the owner's plugin push; members **claim** a pre-created roster account when they register.
- Clan rank and title are pushed by the plugin and displayed on accounts.

### 2.2 GROUP

Intended for Group Ironman (GIM) groups who want to share in-game data.

- Requires a **Group Ironman group name**, which is **validated against the official OSRS GIM hiscores page** at save time. A definitively unknown group is rejected; a transient lookup failure is allowed through so an outage doesn't block setup.
- Supports group-specific features: a shared **group bank** view and GIM-flavoured accounts.
- Members are added to the roster by hand by the admin and claimed on registration.

### 2.3 CASUAL

Intended for small friend groups or solo players who want tracking/display without a clan or GIM setup.

- No clan or group name required.
- Open registration; supports the same core features minus the roster-claim flow and group-only features.

### Feature Matrix

| Feature                  | CLAN | GROUP | CASUAL |
|--------------------------|:----:|:-----:|:------:|
| Hiscores & stats         | ✓    | ✓     | ✓      |
| Inventory & bank sync    | ✓    | ✓     | ✓      |
| Group bank view          | –    | ✓     | –      |
| Loot log                 | ✓    | ✓     | ✓      |
| Quest list               | ✓    | ✓     | ✓      |
| Collection log           | ✓    | ✓     | ✓      |
| Achievement diaries      | ✓    | ✓     | ✓      |
| Combat achievements      | ✓    | ✓     | ✓      |
| Leaderboards             | ✓    | ✓     | ✓      |
| Live feed                | ✓    | ✓     | ✓      |
| Live map                 | ✓    | ✓     | ✓      |
| Announcements            | ✓    | ✓     | ✓      |
| Calendar                 | ✓    | ✓     | ✓      |
| GIM group validation     | –    | ✓     | –      |
| Roster claim on register | ✓    | ✓     | –      |

> Implementation note: the original spec described per-mode automatic role
> elevation (clan ranks / group membership granting website admin). Today only
> the **Owner** holds management permissions; rank/membership-based elevation is
> **(not yet implemented)** — see §3.4.

---

## 3. User Accounts & Authentication

### 3.1 Registration

- Users register with an email address and password (Laravel Jetstream/Fortify).
- Email verification is required before the account is active.
- In CLAN/GROUP modes, registration is gated on **claiming a pre-created roster account**.
- OAuth providers (e.g. Discord) — **(not yet implemented)**.

### 3.2 OSRS Account Linking

- After registration, the user installs the **RuneManager RuneLite plugin** and logs in with their RuneManager credentials from the plugin config panel.
- The plugin authenticates with the backend and associates the player's OSRS account (by a stable account hash + username) with the website account.
- A single website account may link multiple OSRS usernames.

### 3.3 Username Changes

- OSRS usernames can change; the system tracks username history (`username_histories`).
- Every plugin request carries the current in-game username; when it differs from the stored one (matched by account hash), the backend updates it and records the change.

### 3.4 Roles

- **User**: standard registered member (default; no management permissions).
- **Owner**: top-level role, holds every management permission (`manage instance`, `manage members`, `manage announcements`, `manage calendar`).
- Role/permission handling uses Spatie Laravel-Permission.

> Implementation note: clan-leader / group-member elevation to admin is
> **(not yet implemented)**; the Owner is currently the only admin.

---

## 4. RuneLite Plugin Integration

The RuneManager RuneLite plugin is the primary bridge between the game client and the website.

### 4.1 Responsibilities

- Authenticate the player against the backend using their website credentials, exchanging them once for an API token stored locally.
- Push in-game data to the backend on the appropriate triggers (see §5), gated by per-data **"Data sync" toggles** in the plugin config.
- Poll the backend for unacknowledged **announcements** and display them in-game, then acknowledge them.
- Detect the active RuneLite **resource pack** and report it for theme sync (§6).
- Maintain presence (a periodic heartbeat) and, opt-in, share live **location** for the map.

### 4.2 Communication Model

The plugin is the **sole initiator** of all network communication.

- **Outbound (plugin → backend)**: data submissions, authentication, resource-pack reports, presence/heartbeat.
- **Inbound (plugin polls backend)**: announcements (pulled then acknowledged). The backend never opens a connection to the plugin.

### 4.3 Authentication Flow

1. User enters RuneManager credentials in the plugin config.
2. Plugin exchanges them for a session token via the API.
3. Token is stored locally and attached (with the OSRS account hash + username) to all subsequent requests.
4. A rejected token (HTTP 401) is cleared so the plugin re-authenticates.

### 4.4 Data Submission

- All data is submitted over HTTPS to the REST API.
- Each request includes the session token and the headers `X-Account-Hash`, `X-Account-Username`, `X-Account-Type`; middleware resolves these to the correct account.

---

## 5. Data Sync

### 5.1 Sync Strategies

**Push (plugin-initiated, event-driven)** — the primary mechanism for in-game data.

**Backend pull from official hiscores (server-initiated, periodic)** — for hiscore data only, the backend fetches from the official OSRS hiscores API on a configurable cadence.

**Backend pull from TempleOSRS** — the collection log is fetched from TempleOSRS rather than pushed slot-by-slot.

> The website never initiates a connection to the game client or plugin. All
> game-to-website data flow originates from the plugin.

### 5.2 Data Types

| Data | Strategy | Store | Notes |
|------|----------|-------|-------|
| Hiscores (skills, bosses, activities, clues) | Backend pull (OSRS API) | PostgreSQL (jsonb) | Dynamic key-value schema; new skills/bosses ingested without migration. |
| Inventory | Push | MongoDB | Full snapshot. |
| Bank | Push | MongoDB | Full snapshot. |
| Group bank (GROUP only) | Push | MongoDB | Single shared document, overwritten on each member's bank access. |
| Looting bag | Push | MongoDB | Snapshot when opened. |
| Equipment | Push | PostgreSQL | Worn-slot snapshot. |
| Loot | Push (append-only) | MongoDB | One document per drop; `total_value` is the plugin-computed GE total. |
| Quests | Push | MongoDB | Snapshot of quest states. |
| Achievement diaries | Push | PostgreSQL | 12 areas × 4 tiers, read from completion varbits. |
| Combat achievements | Push | PostgreSQL | Total points + completed-task count per tier (read from varbits); individual task unlocks are pushed to the feed. |
| Collection log | Backend pull (TempleOSRS) | MongoDB | Full log overlaid on the TempleOSRS category structure. |
| Position (live map) | Push (opt-in) | PostgreSQL | Latest WorldPoint while sharing is enabled. |
| Vitals (status orbs) | Push | PostgreSQL | HP/prayer/run energy/special attack. |
| Activity & presence | Push / heartbeat | PostgreSQL | Current in-game activity + `last_seen_at`-derived online status. |
| Avatar (3D model) | Push | public disk | Default-pose player model (OBJ/MTL), captured on login + equipment change. |
| Clan rank & title | Push (CLAN) | PostgreSQL | Mirrors the in-game clan. |

> Implementation notes:
> - Quests are stored in **MongoDB** (the original spec called for PostgreSQL).
> - The collection log is **pulled from TempleOSRS** rather than pushed per-slot.
> - Combat achievements store **points + per-tier counts**, not all ~600 task rows.
> - There is no generic "achievements" type separate from Achievement Diaries
>   and Combat Achievements.

### 5.3 Data Freshness Indicators

- Profiles show a "last updated" timestamp per data type.
- Data older than a configurable threshold is visually flagged as stale.

---

## 6. Theming & Resource Packs

RuneManager uses DaisyUI for its component library, with a custom `runemanager` theme (light + dark) as the base. In-game **resource packs** are layered on top to texture the entire UI.

### 6.1 Resource Pack Sync

- The plugin detects the active RuneLite Resource Pack (via the community Resource Packs plugin, opt-in) and pushes its identifier.
- The website renders the matching pack's in-game sprites across DaisyUI components (buttons, panels, inputs, scrollbars, borders, badges, tooltips, etc.).
- A pack is always in effect — a bundled **Default Vanilla** pack is the render floor, so missing sprites fall back cleanly.

### 6.2 Manual Selection (per user)

- Users can override the auto-detected pack from **Settings → Appearance** and pick any installed pack; the choice persists.
- Each user may install a limited number of packs from the hub (the Owner is exempt); a user may delete packs they installed.
- Light/dark is independent of the pack and can be toggled by anyone.

### 6.3 Theme Management (Admin)

- Admins install packs from a resource-pack **hub** and set the instance-wide **default** pack (applied to guests and users without an override).
- Admins set the **default appearance** (light/dark, or "follow the pack").

> Implementation note: theming is delivered through resource packs rather than
> admin-uploaded custom DaisyUI themes (the original §6.3). Pack sprites are
> normalised at install time for cross-pack consistency.

---

## 7. Leaderboards

Leaderboards let members compete on a variety of metrics.

### 7.1 Categories (implemented)

- **Overall** — total level + total XP.
- **Skills** — per-skill level/XP rankings.
- **Bosses & Activities** — kill count / score per hiscore entry.
- **Clues** — counts per clue tier.
- **Loot** — total loot value (plugin-computed GE) and loot by source.
- **Collection Log** — total slots unlocked (via TempleOSRS).
- **Combat Achievements** — total points, with completed-task counts per tier.
- **Achievement Diaries** — total tiers completed, with a per-tier breakdown.

### 7.2 Scope

- Leaderboards are scoped to the instance (rankings among the instance's accounts).

### 7.3 Display

- Paginated and sortable; each entry links to the player's profile.
- The viewer's own accounts are highlighted.

---

## 8. Live Feed

A real-time stream of notable in-game events from all members of the instance.

### 8.1 Event Types (implemented)

- **Level-up** — every level is stored; the feed UI shows only the admin's milestone levels (the thresholds are a display filter, not a storage filter).
- **Loot drop** — for drops clearing a configurable minimum value.
- **Quest completion**.
- **Combat achievement unlock** — task name (+ tier when available).
- **Collection log slot** — the item added (the plugin parses the in-game "new item" notice; the full log is still pulled from TempleOSRS).
- **Pet** — a pet drop.
- **Death** — the player died.
- **Reward** — a reward screen opened (clue / CoX / ToB / ToA / Barrows).

> All of these are detected client-side by the plugin (mirroring the official
> Screenshot plugin's triggers), so the set of feed events and the set of
> screenshot-able moments line up. Level-ups are pushed for every level; the
> hiscores sweep no longer derives them (it just keeps stats fresh).

### 8.2 Behaviour

- Events are generated server-side when pushed data is processed and a qualifying change is detected.
- Reverse-chronological; entries link to the relevant player/section.
- Admin-configurable thresholds: level-up milestones and minimum loot value.
- Optional **screenshots**: with an opt-in plugin toggle, a full-frame screenshot is captured for loot drops, combat achievements, and collection-log slots and attached to the matching feed event. (RuneLite has no API to exclude other plugins' in-world overlays, so the shot is the full client frame.)
- Publicly visible.

### 8.3 Real-time Updates

- New entries are pushed to connected browsers over WebSockets (Laravel Reverb + Laravel Echo) with no polling. The feed also seeds the public homepage.

---

## 9. Announcements

Admins broadcast messages to members, both on the website and in-game.

### 9.1 Creating Announcements

- Admins create announcements (title, body, optional expiry) from the website.

### 9.2 Delivery

- **Website**: shown on the homepage and a dedicated announcements page (active/non-expired, newest first).
- **In-game**: the plugin polls the API, displays new announcements, and acknowledges them so they aren't repeated.
- New announcements are also forwarded to the instance webhook if configured (§11).

### 9.3 Availability

- Available in all modes.

---

## 10. Calendar

A shared schedule of in-game events.

### 10.1 Events

Title, description, start (and optional end) date/time, event type (PvM mass, Clan wars, Skilling event, Custom), a custom colour, and the creator.

### 10.2 Permissions

- **Viewing**: anyone, including unauthenticated visitors.
- **Creating**: gated behind the `manage calendar` permission. Events can't be scheduled before today.

### 10.3 Display

- Monthly grid (V-Calendar); multi-day events span every day they cover.
- Upcoming events are surfaced on the homepage.
- New events are forwarded to the instance webhook if configured (§11).

### 10.4 Availability

- Available in all modes.

---

## 11. Outbound Webhooks

A single, admin-configured instance webhook forwards **website-originated** events to an external service (primarily a Discord channel).

### 11.1 Configuration

- The Owner sets one **webhook URL** under **Settings → Integrations**. Blank disables it.

### 11.2 Triggers

- New **announcements** and new **calendar events** are POSTed as Discord-compatible embeds.

### 11.3 Delivery

- Sent via a queued job with retries and exponential backoff; failures are logged.

> Implementation note: this replaces the original §11 design (per-user webhooks
> firing on every personal feed event). Per-player **in-game** event forwarding
> is intentionally delegated to dedicated RuneLite plugins such as
> [Dink](https://github.com/pajlads/DinkPlugin) — which other plugins integrate
> with by posting a RuneLite `PluginMessage` — rather than reimplemented here.
> RuneManager only owns instance-wide, website-originated notifications.

---

## 12. Administrative Backend

Settings are organised as a sidebar **settings hub** shared by users and admins. Admin sections: **General**, **Branding**, **Feed & sync**, **Integrations**.

### 12.1 Username Change Tracking

- A `username_history` record per linked account (old/new username, detection date); the current username is the latest entry.

### 12.2 Dynamic Hiscore Schema

- Hiscore entries are stored as a flexible `{ key: { rank, level_or_score, xp } }` map; new official hiscore entries are ingested automatically with no migration, and the UI renders whatever keys are present.

### 12.3 Member Management

- Admins view members and their linked accounts.
- In CLAN/GROUP modes, admins manage the roster (add/remove accounts).
- Promoting a member to admin — **(not yet implemented)**.

### 12.4 Instance Configuration

- **General**: mode, clan/group name, description, default resource pack, default appearance (light/dark), and a public-landing toggle to anonymise account names.
- **Branding**: logo + banner uploads.
- **Feed & sync**: hiscores refresh cadence, level-up milestone thresholds, minimum loot value.
- **Integrations**: the outbound webhook URL (§11).

---

## 13. Technology Stack

| Layer              | Technology                                      |
|--------------------|-------------------------------------------------|
| Framework          | Laravel 11 (Jetstream, Fortify, Sanctum), Inertia v1 |
| Primary database   | PostgreSQL — relational data (users, roles, accounts, hiscores, equipment, diaries, combat achievements, username history, calendar events, announcements, settings, presence/position/vitals) |
| Document store     | MongoDB — high-volume snapshots (bank, inventory, looting bag, loot log, quests, group bank, collection log); a static osrsbox item DB for item lookups |
| Frontend           | Vue 3 + Inertia + DaisyUI (Tailwind v3), with resource-pack texturing |
| Plugin platform    | RuneLite (Java plugin) |
| Real-time          | WebSockets — Laravel Reverb + Laravel Echo |
| Auth & roles       | Fortify/Sanctum auth; Spatie Laravel-Permission |
| Queue              | Database queue (queued jobs for webhooks, resource-pack installs, hiscore sync) |
| External APIs      | Official OSRS Hiscores API (skills/bosses/clues), OSRS GIM group page (group-name validation), TempleOSRS (collection log) |
| GE prices          | Loot value is computed plugin-side and pushed with each drop (no server-side price service) |

---

## 14. Business Model

RuneManager is delivered as a managed SaaS. Each customer receives a dedicated instance.

- Customers select a **mode** (CLAN, GROUP, or CASUAL) at setup.
- Billing, provisioning, and instance lifecycle management are handled by a separate **RuneManager Administrative System** (not in scope for this specification).
- The application itself has no awareness of billing or subscription state; it assumes a validly provisioned instance.

---

*End of specification.*
