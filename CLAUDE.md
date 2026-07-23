# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Malifaux 4th Edition Rules Compendium — a Laravel 12 + Inertia.js + Vue 3 (TypeScript) site that publishes
tabletop game rules content (Pages, Sections, Indices, FAQs, Errata) and tournament content ("Gaining
Grounds": Seasons, SeasonPages, Strategies, Schemes), plus a read-only public JSON API (`/api/v1`) documented
via Scramble/Stoplight Elements at `/docs/api`.

## Commands

**PHP / backend**
- `composer dev` — run app server, queue listener, log tailer (pail), and Vite dev server concurrently (this is the normal local dev entrypoint)
- `composer test` / `php artisan test` — run the Pest suite (`tests/Unit`, `tests/Feature`)
- `./vendor/bin/pest --filter=testName` or `./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php` — run a single test/file
- `composer stan` — Larastan/PHPStan (level 4) over `app/`
- `composer lint` / `vendor/bin/pint` — Laravel Pint formatting (auto-fixes)
- `composer prepush` — regenerates IDE helpers, runs stan, pint, then the full parallel test suite; run before pushing if unsure
- `composer ide-generate` — regenerate `_ide_helper*.php` files after model/route changes

**JS / frontend**
- `npm run dev` — Vite dev server (normally launched via `composer dev`, not standalone)
- `npm run build` / `npm run build:ssr` — production build
- `npm run lint` — ESLint (flat config, Vue + TS) with `--fix`
- `npm run format` / `npm run format:check` — Prettier (4-space, single quotes, Tailwind class sorting, import sorting)

CI (`.github/workflows/`) runs `./vendor/bin/pest` for tests and `pint` + `npm run format` + `npm run lint` for
the linter job.

## Architecture

### Content versioning — no revision table, self-referential rows

Every content model (`Page`, `Section`, `Index`, `Errata`, `Faq`, `Season`, `SeasonPage`, `Strategy`,
`Scheme`, `Batch`) follows the same shape: `SoftDeletes` + `LogsActivity` (spatie/activitylog) +
`#[ObservedBy(XObserver::class)]` + the `UsesVersionControl` trait (`app/Traits/UsesVersionControl.php`),
which itself composes `UsesApproval` and `UsesBatches`.

Versioning is done on the same table via self-referencing FK columns, **not** a separate revisions table:
- `previous` / `original` / `newest` point back to rows of the same model
- `published_at` / `published_by` mark the live version

Editing flow: an edit creates a **new row** (a draft) linked to the old one via `previous`/`original`.
`UsesVersionControl::publish(User $publisher)`:
1. throws if the row has no approved `Approval` (`approval->approved_at` must be set)
2. sets `published_at`/`published_by`
3. bulk-updates every row sharing the same `original` so `newest` points at this row
4. deletes the previous row's `Approval` and **soft-deletes** the previous row

`scopePublished`/`scopeUnpublished` filter on `published_at`. Controllers almost always query
`->published()` then resolve `->newestVersion`. `/history` routes work by querying `withTrashed()` for the
soft-deleted chain of old versions linked via `original`/`previous`/`newest` — history is reconstructed from
that chain, there's no dedicated audit table for content body changes.

`HasContentReferences` (`app/Traits/HasContentReferences.php`) adds morphToMany relations so content pieces
can reference each other (e.g. a `{{section=slug}}` tag in one Page's body links to a Section) in both
directions (`referencedPages()` / `referencedByPages()` etc.). The pivot tables are backfilled by
`php artisan content:sync-references` (`app/Console/Commands/SyncContentReferencesCommand.php`).

### Approval workflow

`Approval` (`app/Models/Approval.php`) is a polymorphic (`approvable_type`/`approvable_id`) moderation
record: `initiated_by`, `approved_by`, `approved_at`, `change_notes`, `internal_notes`, plus
`searchable_text` (tag-stripped `change_notes`). `UsesApproval` gives content models an `approval()`
morphOne and `canBeApproved()`. `app/Actions/Approvals/CreateApprovalAction.php` creates the pending
Approval when a draft is saved (with an `approveDirectly` fast path for self-approving editors). A row can
only be `publish()`ed once its `Approval->approved_at` is set. `ApprovablesEnum` (`app/Enums/ApprovablesEnum.php`)
maps each approvable model to its admin route prefix and display metadata, used by the generic admin
approval UI (`app/Http/Controllers/Admin/ApprovalAdminController.php`, `ApprovalResource`).

### Batches

`Batch` groups a set of content changes (pages/sections/indices/seasons/etc., see `Batch::$batchables`) so
they can be reviewed and published together — used for coordinated rules updates (e.g. an errata drop
touching many pages at once). `UsesBatches` gives content models a `batch()` relation.

### ContentBuilder — the markup engine

`app/Services/ContentBuilder/ContentBuilder.php` parses/renders the custom `{{tag ...}}...{{/tag}}` markup
used in all rich-text content fields (formatting tags like `{{b}}`/`{{i}}`, game-symbol glyphs like
`{{crow /}}`, and cross-content links like `{{section=slug}}`/`{{pageLink=slug}}`). It tokenizes into a
nested AST, and `getFullyHydratedContent()` resolves slug-referencing tags against the DB (via
`withTrashed()`/`newestVersion`) to inline titles/content for rendering. This hydrated JSON is what gets
passed as an Inertia prop and rendered client-side. Static helpers: `toSearchable()` (strip tags, used to
populate `searchable_text` for LIKE-based search), `toPlainText()`, `parseTitleTags()`.

### Controller layers

- `app/Http/Controllers/Rules/*` — public web (Inertia) controllers. Fat: fetch `->published()` content with
  `newestVersion`, hydrate via `ContentBuilder`, `inertia('Rules/PageView', [...large prop payload...])`.
  `viewHistory()` actions load a specific old (possibly trashed) version and pass `viewing_old_version`.
- `app/Http/Controllers/Admin/*` — CRUD + approve/publish actions behind `routes/admin.php`, gated per-action
  by `middleware(['permission:<action>_<model>'])` (spatie/laravel-permission). Uses the plain
  `app/Http/Resources/*ListResource.php` / `ApprovalResource` classes for admin list UIs.
- `app/Http/Controllers/API/V1/*` — thin, read-only, Scramble-documented (`@tags`, `@queryParam` doc blocks
  feed the generated OpenAPI spec at `/docs/api`). Always filter to the single latest published row
  (`published_at != null && newest === null`) and return `App\Http\Resources\API\V1\*Resource`.

### Authorization

spatie/laravel-permission. `User` uses `HasRoles`. Permissions are one-per-action-per-content-type
(`view_page`, `edit_page`, `publish_page`, …), centralized in `app/Enums/PermissionEnum.php` (grouped via a
`#[PermissionGroup(...)]` attribute + `PermissionGroupEnum`). Enforced at the route level with Spatie's
built-in `permission:` middleware alias in `routes/admin.php` — there's no custom permission middleware.
Frontend checks mirror this via the `hasPermission()` composable (`resources/js/composables/hasPermission.ts`)
against a permissions array shared through Inertia (`app/Http/Middleware/HandleInertiaRequests.php`).

### Search

Plain Eloquent `LIKE` search (`app/Http/Controllers/Rules/SearchController.php` and
`API/V1/SearchController.php`) against `title`/`searchable_text` across every content type, merged into one
`Search/Results.vue` response with manual snippet extraction. **Laravel Scout is a dependency but unused** —
no model implements `Searchable`; don't assume Scout-backed search exists.

### Frontend

Standard Inertia + Vue 3 bootstrap in `resources/js/app.ts`: pages auto-resolved via
`import.meta.glob('./pages/**/*.vue')`, default layout (`AppLayout`) auto-injected unless a page sets its
own `.layout`. Two layout shells live in `resources/js/layouts/app/` (`AppHeaderLayout.vue`,
`AppSidebarLayout.vue`). Path alias `@` → `resources/js/`. Pages are organized by domain under
`resources/js/pages/` (`Rules/`, `Errata/`, `Admin/`, `Search/`, `auth/`, `settings/`).
