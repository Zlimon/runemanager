# RuneManager — Product Specification

> Version 1.1 — Draft  
> Last updated: 2026-06-01

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
11. [Webhook System](#11-webhook-system)
12. [Administrative Backend](#12-administrative-backend)
13. [Technology Stack](#13-technology-stack)
14. [Business Model](#14-business-model)

---

## 1. Overview

RuneManager is a SaaS CMS that bridges Old School RuneScape (OSRS) with a managed website. It allows players — whether in a large clan, a Group Ironman team, or playing solo/casually — to display, share, and compete on in-game data through a public-facing website backed by live game data.

Data flows between the game and the website via the **RuneManager RuneLite plugin**. The plugin is the sole initiator of all communication: it pushes in-game data to the website backend, and it polls the backend to pull down notifications such as announcements and alerts. The website never initiates a connection to the game client. The plugin also handles authentication, theming synchronisation, and in-game display of website content.

Each customer receives a managed instance of RuneManager. Instance management and billing are handled by a separate administrative system (out of scope for this spec).

---

## 2. Modes

Every RuneManager instance operates in exactly one mode, chosen at setup. The mode determines which features are available and what identity the instance is built around.

### 2.1 CLAN

Intended for clan leaders who want a website representing their OSRS clan.

- Requires a valid **clan name** at setup.
- Supports the full feature set: leaderboards, live feed, announcements, calendar, webhooks, data sync for all members.
- Members join by registering on the website and linking their OSRS account.
- Clan leaders are assigned an admin role with elevated permissions.

### 2.2 GROUP

Intended for Group Ironman (GIM) groups who want to share in-game data.

- Requires a valid **Group Ironman group name** at setup.
- Group name should be validated against the official OSRS hiscores GIM endpoint where possible.
- Supports group-specific features: shared group bank view, combined group hiscores, and GIM-specific leaderboard categories.
- All GROUP features are a subset of CLAN, minus clan-management-specific functionality.

### 2.3 CASUAL

Intended for small friend groups or solo players who want the tracking and display features without the overhead of a full clan or GIM setup.

- No clan or group name required.
- Supports the same core features as CLAN and GROUP, minus the functionality that is specific to those modes (group bank, GIM validation, clan management).
- Suitable for personal stat tracking, loot logging, sharing a public profile, and organising events with a small group of friends.

### Feature Matrix

| Feature                  | CLAN | GROUP | CASUAL |
|--------------------------|:----:|:-----:|:------:|
| Hiscores & stats         | ✓    | ✓     | ✓      |
| Inventory & bank sync    | ✓    | ✓     | ✓      |
| Group bank view          | –    | ✓     | –      |
| Loot log                 | ✓    | ✓     | ✓      |
| Quest list               | ✓    | ✓     | ✓      |
| Collection log           | ✓    | ✓     | ✓      |
| Achievements             | ✓    | ✓     | ✓      |
| Leaderboards             | ✓    | ✓     | ✓      |
| Live feed                | ✓    | ✓     | ✓      |
| Announcements            | ✓    | ✓     | ✓      |
| Calendar                 | ✓    | ✓     | ✓      |
| Webhooks                 | ✓    | ✓     | ✓      |
| Clan management          | ✓    | –     | –      |
| GIM group validation     | –    | ✓     | –      |

---

## 3. User Accounts & Authentication

### 3.1 Registration

- Users register on the RuneManager website with an email address and password.
- Email verification is required before the account is active.
- OAuth providers (e.g. Discord) may be supported as an alternative registration path.

### 3.2 OSRS Account Linking

- After registration, the user installs the **RuneManager RuneLite plugin** and logs in using their RuneManager credentials from within the plugin.
- The plugin authenticates with the RuneManager backend and associates the player's in-game OSRS username with their website account.
- A single website account may link multiple OSRS usernames (e.g. main + ironman).
- The primary linked account is used for hiscores, leaderboards, and public profile display.

### 3.3 Username Changes

- OSRS usernames can change. The system must track username history.
- When a data submission arrives with a username not matching the stored one, the backend checks whether it corresponds to an existing linked account (via account ID or other stable identifier from the plugin) and updates the stored username accordingly.
- Full username history is retained per account for audit and display purposes.

### 3.4 Roles

- **Member**: standard registered user with a linked OSRS account.
- **Admin / Clan Leader**: elevated permissions for managing the instance (announcements, calendar events, webhook configuration, member management in CLAN mode).
- **Instance Owner**: top-level role, typically the person who set up the instance.

---

## 4. RuneLite Plugin Integration

The RuneManager RuneLite plugin is the primary bridge between the game client and the website.

### 4.1 Responsibilities

- Authenticate the player against the RuneManager backend using their website credentials.
- Push in-game data to the backend on the appropriate triggers (see Section 5).
- Poll the backend at a regular interval to retrieve pending notifications: announcements, alerts (e.g. notable loot drops from other members), and any other server-side messages.
- Display retrieved notifications in-game as chat messages or plugin panel entries.
- Detect the active Resource Pack and report it to the backend for theme synchronisation (see Section 6).

### 4.2 Communication Model

The plugin is the **sole initiator** of all network communication. The website backend never opens a connection to the plugin or game client. This means:

- **Outbound (plugin → backend)**: data submissions (push events), authentication requests, resource pack reports.
- **Inbound (plugin polls backend)**: announcements, alerts, and other queued notifications. The plugin polls at a configurable interval (e.g. every 30 seconds). The backend queues notifications until they are acknowledged by the plugin.

### 4.3 Authentication Flow

1. User enters their RuneManager credentials in the plugin config panel.
2. Plugin exchanges credentials for a session token with the RuneManager API.
3. Token is stored locally in the plugin and attached to all subsequent API requests.
4. Token refresh is handled automatically; the user is only prompted to re-authenticate if the token is invalidated.

### 4.4 Data Submission

- All data is submitted over HTTPS to the RuneManager REST API.
- Each request includes the player's session token and the OSRS username of the currently logged-in character.
- The backend validates the token and associates the data with the correct website account.

---

## 5. Data Sync

Data synchronisation is the core of RuneManager. Different data types use different sync strategies depending on how they change in-game.

### 5.1 Sync Strategies

**Push (plugin-initiated, event-driven)** — The plugin detects a specific in-game event and immediately submits the relevant data to the RuneManager backend. This is the primary sync mechanism for all in-game data.

**Backend pull from official hiscores (server-initiated, periodic)** — For hiscore data only, the RuneManager backend periodically fetches from the **official OSRS hiscores API** (not from the game client). This is a server-to-server operation and requires no plugin involvement beyond the initial account link.

> The website never initiates a connection to the game client or plugin. All game-to-website data flow originates from the plugin.

### 5.2 Data Types

#### Hiscores
- **Strategy**: Backend pull from official OSRS hiscores API (not from the plugin)
- The backend fetches skill levels, experience, and boss/activity kill counts from the official OSRS hiscores API.
- Refresh frequency is configurable per instance (e.g. every 30 minutes, every hour).
- When the official hiscores adds a new skill, boss, or activity entry, the backend must support dynamically ingesting the new field without requiring a code deployment. This is achieved by storing hiscore entries as a flexible key-value structure rather than a rigid schema.

#### Inventory
- **Strategy**: Push
- The plugin submits the full inventory state (item IDs and quantities) whenever the inventory is opened or its contents change.
- Stored in MongoDB as a snapshot per player with a timestamp.

#### Bank
- **Strategy**: Push
- The plugin submits the full bank contents (all tabs, item IDs and quantities) whenever the bank is opened or changes.
- Stored in MongoDB as a snapshot per player with a timestamp.
- **Group Bank (GROUP mode only)**: In OSRS, Group Ironman members share a single in-game group bank. Whenever any group member opens the bank, the plugin submits the full current state of the group bank to the backend. The website displays this as a live shared bank view. Because any member's bank access triggers a submission, the group bank on the website stays dynamically up to date as members interact with it in-game. The group bank is stored as a single document in MongoDB, keyed to the group, and overwritten on each submission.

#### Loot
- **Strategy**: Push
- The plugin submits a loot entry on each drop event (NPC kill, chest open, etc.), including source name, item IDs, quantities, and a timestamp.
- Loot entries are appended (not replaced) to form a full loot history per player.
- Stored in MongoDB.

#### Quest List
- **Strategy**: Push
- The plugin submits the full quest state (quest name, completion status) on quest completion.
- Stored as a structured list in PostgreSQL.

#### Collection Log
- **Strategy**: Push
- The plugin submits individual collection log slot unlocks as they occur (item name, source category, timestamp).
- Full collection log state is stored per player, with each slot tracked individually.
- Stored in MongoDB.

#### Achievements & Combat Achievements
- **Strategy**: Push
- The plugin submits achievement unlocks as they occur (achievement name, tier for combat achievements, timestamp).
- Stored in PostgreSQL as an append-only log per player.

### 5.3 Data Freshness Indicators

- The UI displays a "last updated" timestamp for each data type on player profiles.
- Stale data (beyond a configurable threshold) is visually flagged.

---

## 6. Theming & Resource Packs

RuneManager uses DaisyUI for its component library, enabling full theme customisation.

### 6.1 Resource Pack Sync

- The RuneManager plugin detects which Resource Pack (via the RuneLite Resource Packs plugin) the player currently has active.
- When the player loads the website, their active resource pack theme is automatically applied if a corresponding RuneManager theme exists.
- Theme mappings between resource pack identifiers and DaisyUI themes are maintained by the instance admin.

### 6.2 Manual Theme Selection

- Users can override the auto-detected theme and manually select any available theme from their profile settings.
- The manual selection persists across sessions until changed.

### 6.3 Theme Management (Admin)

- Instance admins can define and upload custom DaisyUI-compatible themes.
- Admins can map resource pack identifiers to specific themes.
- A default theme is set for unauthenticated visitors and users without a manual selection.

---

## 7. Leaderboards

Leaderboards allow members to compete against each other on a variety of metrics.

### 7.1 Categories

- **Skills**: Total level, total XP, individual skill XP/level rankings.
- **Bosses & Activities**: Kill counts / score for each hiscore boss and activity entry.
- **Loot**: Total loot value (calculated from GE prices), loot by source. GE price data source to be determined; candidate: [ge-tracker/osrs-api](https://github.com/ge-tracker/osrs-api).
- **Collection Log**: Total slots unlocked.
- **Combat Achievements**: Total points, breakdown by tier (Easy / Medium / Hard / Elite / Master / Grandmaster).
- **Achievements**: Total count.

### 7.2 Scope

- Leaderboards are scoped to the instance (i.e. rankings are among members of the clan/group, not global).
- In CLAN mode, leaderboards show all clan members.
- In GROUP mode, leaderboards show group members only.

### 7.3 Display

- Leaderboards are paginated and sortable.
- Each entry links to the player's public profile.
- The viewer's own rank is highlighted if they are a member.

---

## 8. Live Feed

The live feed displays a real-time stream of notable in-game events from all members of the instance.

### 8.1 Event Types

The following event types appear in the live feed:

- **Level up / Skill milestone**: e.g. "PlayerX reached 99 Slayer" or "PlayerX reached level 70 Agility".
- **Boss kill / Loot drop**: e.g. "PlayerX received Twisted Bow from Chambers of Xeric".
- **Quest completion**: e.g. "PlayerX completed Dragon Slayer II".
- **Combat achievement unlock**: e.g. "PlayerX unlocked 'Ghommal's Hilt 6' (Grandmaster)".
- **Collection log slot**: e.g. "PlayerX added Tumeken's Shadow to their collection log".

### 8.2 Behaviour

- Events are generated server-side when pushed data is processed and a qualifying change is detected.
- The feed is displayed in reverse chronological order.
- Feed entries link to the relevant player profile or data section.
- Configurable milestone thresholds (e.g. only announce level-ups at 99, or at every 10 levels) should be available to instance admins.
- The feed is publicly visible on the instance website.

### 8.3 Real-time Updates

- The live feed uses WebSockets or Server-Sent Events (SSE) to push new entries to connected browsers without requiring a page refresh.

---

## 9. Announcements

Announcements allow instance admins to broadcast messages to all members, both on the website and in-game via the plugin.

### 9.1 Creating Announcements

- Admins create announcements via the website dashboard.
- Each announcement has a title, body (rich text), and optional expiry date.

### 9.2 Delivery

- **Website**: Announcements are displayed prominently on the instance homepage and in a dedicated announcements section.
- **In-game**: The plugin polls the RuneManager API at its configured interval and retrieves any unacknowledged announcements. New announcements are displayed as in-game chat messages or a plugin panel notification. Once displayed, the plugin marks them as acknowledged so they are not shown again.

### 9.3 Availability

- Available in all modes (CLAN, GROUP, and CASUAL).

---

## 10. Calendar

The calendar provides a shared schedule of in-game events for instance members.

### 10.1 Events

Each calendar event includes:

- Title
- Description
- Start date/time and optional end date/time
- Event type (e.g. PvM mass, Clan wars, Skilling event, Custom)
- Created by (admin or member)

### 10.2 Permissions

- **Viewing**: Anyone (including unauthenticated visitors) can view the calendar.
- **Creating events**: Instance admins and clan leaders can create events. Regular members may be granted event creation rights at the admin's discretion.

### 10.3 Display

- Calendar is displayed in a monthly/weekly view on the website.
- Upcoming events are surfaced in the website sidebar or homepage widget.

### 10.4 Availability

- Available in all modes (CLAN, GROUP, and CASUAL).

---

## 11. Webhook System

Webhooks allow instance members and admins to subscribe to events and receive notifications in external services, primarily Discord.

### 11.1 Configuration

- Any registered member can configure their own webhooks from their profile settings.
- Each webhook consists of:
  - A target URL (e.g. a Discord channel webhook URL).
  - A display name for identification.
  - Enabled/disabled toggle.

### 11.2 Event Triggers

- A webhook fires on any live feed event (see Section 8.1) that belongs to the configuring user's account.
- Instance admins can additionally configure instance-wide webhooks that fire on any member's events.
- All event types are supported; there is no per-webhook event type filtering (any event triggers the webhook).

### 11.3 Payload

- Webhook payloads are delivered as HTTP POST requests with a JSON body.
- The payload includes: event type, player name, description, timestamp, and a URL to the relevant page on the RuneManager instance.
- Discord-compatible embeds are supported in the payload format.

### 11.4 Reliability

- Failed webhook deliveries are retried with exponential backoff (up to 3 attempts).
- Delivery status (success/failure) is logged and visible to the webhook owner.

---

## 12. Administrative Backend

### 12.1 Username Change Tracking

- The system maintains a `username_history` record per linked OSRS account.
- Each entry records the old username, the new username, and the date the change was detected.
- The current username is always the most recent entry.
- The plugin submits the current in-game username with every request, allowing the backend to detect changes automatically.

### 12.2 Dynamic Hiscore Schema

- Hiscore data is not stored against a fixed list of skill/boss columns.
- The backend stores hiscore entries as a flexible map of `{ key: { rank, level_or_score, xp } }`.
- When the official OSRS hiscores introduces a new entry (e.g. a new skill or boss), the backend begins storing it automatically on the next pull without any schema migration or deployment.
- The UI renders hiscore entries dynamically from whatever keys are present in the stored data.

### 12.3 Member Management (CLAN mode)

- Admins can view all registered members, their linked accounts, and their join date.
- Admins can remove members from the instance.
- Admins can promote members to admin role.

### 12.4 Instance Configuration

- Admins can update the instance name, description, and branding (logo, banner).
- Admins can set the default theme.
- Admins can configure hiscore refresh frequency.
- Admins can configure live feed milestone thresholds.

---

## 13. Technology Stack

| Layer              | Technology                                      |
|--------------------|-------------------------------------------------|
| Primary database   | PostgreSQL — relational data (users, roles, quests, achievements, username history, webhooks, calendar events, announcements) |
| Document store     | MongoDB — high-volume snapshot data (bank, inventory, loot log, collection log) |
| Frontend component | DaisyUI — theming and UI components             |
| Plugin platform    | RuneLite (Java plugin)                          |
| Real-time          | WebSockets or Server-Sent Events (SSE) for live feed |
| External API       | Official OSRS Hiscores API (backend pull for hiscores) |
| GE Prices (TBD)    | Candidate: [ge-tracker/osrs-api](https://github.com/ge-tracker/osrs-api) — used for loot valuation on leaderboards |

---

## 14. Business Model

RuneManager is delivered as a managed SaaS. Each customer receives a dedicated instance.

- Customers select a **mode** (CLAN, GROUP, or CASUAL) when signing up.
- Billing, provisioning, and instance lifecycle management are handled by a separate **RuneManager Administrative System** (not in scope for this specification).
- The RuneManager application itself has no awareness of billing or subscription state; it assumes a validly provisioned instance.

---

*End of specification.*
