# Testing notes

- Feature tests run on in-memory SQLite (forced in `phpunit.xml`), not PostgreSQL, and on the `runemanager_testing` Mongo database. Keep queries portable. Do not use PostgreSQL-only SQL such as jsonb operators or `ILIKE`; filter jsonb maps in PHP or with Eloquent's JSON helpers. Why: SQL that only works on one database passes in either production or the suite, but not both.
- `RefreshDatabase` rolls back SQL only. `tests/Pest.php` deletes the documents of every `mongodb` model before each test, so a new Mongo model must be added to that list. Why: SQLite reuses primary keys after a rollback, so leftover Mongo documents would attach themselves to a fresh test's account.
- Inertia testing has `ensure_pages_exist` enabled (`config/inertia.php`). A `->component('X/Index')` assertion fails if `resources/js/Pages/X/Index.vue` does not exist, so rename a page's render name in the controller, the Vue path and the test in the same change.
- The styleguide route is open without login only in the `local` environment. Tests run as `testing`, so they exercise the gated `manage instance` branch.
