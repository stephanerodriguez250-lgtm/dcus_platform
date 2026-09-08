# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

DCUS Platform — a French-language Laravel 13 app for managing internal governance at a Beninese public institution (Direction de la Coopération Universitaire et Scientifique, under the Ministry of Higher Education): CODIR (comité de direction) meetings, "réunions", "décisions" (action items), and "accords" (agreements), with PDF/CSV export and role-based access. Views and domain vocabulary are entirely in French — keep new UI text, route names, and model fields consistent with that (e.g. `reunions`, `decisions`, `codirs`, `accords`, `utilisateurs`).

## Commands

```bash
composer install                 # PHP deps — requires PHP ^8.3 (see PHP version note below)
npm install                      # JS/CSS deps (Vite + Tailwind v4)

php artisan serve                # run app (http://localhost:8000)
npm run dev                      # Vite dev server (asset watch/HMR)
composer run dev                 # runs serve + queue:listen + pail + vite concurrently

composer test                    # clears config cache, then `php artisan test`
php artisan test --filter=Name   # run a single test
php artisan test tests/Feature/Auth/LoginTest.php

vendor/bin/pint                  # auto-fix code style
vendor/bin/pint --test           # check style without modifying (what CI runs)

php artisan migrate              # run migrations (see DB setup below)
php artisan migrate:fresh --seed # rebuild schema + seed default users
```

GitHub Actions (`.github/workflows/tests.yml`) runs Pint + the test suite (sqlite in-memory) on every push/PR to `master`/`main`.

### PHP version

`composer.json` requires `php: ^8.3`. If your local PHP (e.g. a XAMPP install) is older, install a standalone PHP 8.3+ (e.g. from windows.php.net) alongside it rather than upgrading XAMPP's Apache-bound PHP — the app is run via `php artisan serve`, not XAMPP's Apache/mod_php, so only the CLI binary matters. Needed extensions: `pdo_mysql`, `mysqli`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `intl`, `zip`, plus `pdo_sqlite`/`sqlite3` for running tests (phpunit.xml uses sqlite `:memory:`).

## Database

Default `.env.example` uses `sqlite`, but this app is developed against **MySQL** (`dcus_db`). `database/IMPORT_DB.md` documents importing the provided `database/dcus_db.sql` dump — that dump predates the CODIR/décisions tables (migrations `2024_01_01_000004` through `_000006`), so prefer `php artisan migrate:fresh --seed` over the dump for a fresh local setup unless you specifically need the dump's legacy data. Seeded accounts (`database/seeders/DatabaseSeeder.php`): `directrice@dcus.bj` (admin), `secretaire@dcus.bj` (secretaire), `j.dossou@dcus.bj` / `f.gbedo@dcus.bj` / `p.ahounou@dcus.bj` (agent), all password `Dcus2024!`.

## Mail

Default mailer is **Brevo** (`MAIL_MAILER=brevo`), wired as a custom Symfony transport in `AppServiceProvider::boot()` (`symfony/brevo-mailer`) using `BREVO_API_KEY` from `.env` (a Brevo *API* key, `xkeysib-...` — not an SMTP key). The `MAIL_FROM_ADDRESS` **must** be a sender verified in the Brevo account, or sends are rejected. Brevo blocks API calls from unrecognized IPs by default; a new account's dashboard IP-authorization page is paid-plan-gated, but the automatic "Validate your IP address" confirmation email Brevo sends on first block works regardless of plan. Password-reset and CODIR/réunion convocation emails all go through this. Locally with no Brevo key configured, fall back to `MAIL_MAILER=log` to inspect emails in `storage/logs/laravel.log` instead of sending.

## Architecture

- **Routing**: all routes live in `routes/web.php`, no API routes. Nearly everything sits behind the `auth` middleware group; user management (`utilisateurs.*`) is additionally gated by `role:admin` via the custom `role` route middleware (`App\Http\Middleware\CheckRole`, aliased in `bootstrap/app.php`). Password-reset routes (`password.request`/`password.email`/`password.reset`/`password.update`, French URLs `mot-de-passe-oublie` / `reinitialiser-mot-de-passe`) sit under `guest` middleware.
- **Roles**: plain string column `users.role` (`admin` / `secretaire` / `agent`), checked via `User::isAdmin()`, `isSecretaire()`, `isAgent()`, `canManage()` (admin or secretaire). Authorization for the CRUD resources (Accord, Reunion, Decision, Codir) goes through Laravel **Policies** in `app/Policies/` (auto-discovered by naming convention, no manual registration) called via `$this->authorize(...)` in controllers — `create`/`update`/`delete` map to `canManage()`; `CodirPolicy` additionally has `administer` (admin-only: rapport upload/delete, access grants) and `download` (admin OR a per-user `CodirAcces` grant, see below). User-management routes still use the `role:admin` middleware rather than a policy (whole-route-group gate, not per-resource). Blade views still call the `User` model helper methods directly for show/hide UI logic (not `@can`) — that's presentational only, the real enforcement is server-side in the policies/middleware.
- **Auth**: session-based via `Auth::attempt` in `AuthController`; login additionally checks a `users.actif` boolean and force-logs-out disabled accounts. Login is rate-limited (5 attempts / 60s lockout, keyed by email+IP via `RateLimiter`, mirrors Laravel Breeze's pattern) — see `AuthController::ensureIsNotRateLimited`.
- **Core domain models** (`app/Models`): `Reunion` (meetings) and `Codir` (CODIR sessions) are largely parallel concepts — each has its own `statut` enum-like array (`$statuts`/`$statutColors` static props + accessors), participants/decisions relations, and its own PDF export flow. `Decision` (action items) can originate from multiple source types (`codir_interne`, `codir_externe`, `reunion_interne`, `reunion_externe`, `note_ministerielle` — see `Decision::$sourceTypeLabels`) and polymorphically-ish links back to either a `Codir` or a `Reunion` via nullable `codir_id`/`reunion_id`. `Accord` tracks agreements tied to a `Reunion`. `AccordHistorique`/`DecisionHistorique` are audit-trail side tables populated on status changes.
- **PDF/export**: uses `barryvdh/laravel-dompdf` (`app('dompdf.wrapper')`) rendering blade views under `resources/views/exports/`. CSV export (`ExportController::accordsCsv`/`reunionsCsv`, routes `exports/{reunions,accords}/csv`) is hand-rolled with `phpoffice/phpspreadsheet`'s `Writer\Csv` directly (`app/Exports/AccordsExport.php`/`ReunionsExport.php`) — there is **no** `maatwebsite/excel` dependency, despite those class names/shapes looking like it; don't reintroduce `FromCollection`/`WithHeadings`-style Concerns interfaces, they won't resolve. `CodirController::generatePdf` / `ReunionController::generatePdf` render single-record PDFs.
- **CODIR access control**: beyond role checks, `Codir` has a per-user allowlist for downloading rapports/PDFs — `CodirAcces` model + `Codir::userPeutTelecharger($userId)`, enforced via `CodirPolicy::download`. Admins bypass this check.
- **Archives**: a private, per-user file-organization space — not shared like the rest of the app. `ArchiveFolder` (self-referencing `parent_id`, infinite nesting) and `ArchiveFichier` (belongs to a nullable `folder_id`, i.e. root-level files are allowed) are both scoped by `user_id`; `ArchiveFolderPolicy`/`ArchiveFichierPolicy` check `user_id === $user->id` only — no `canManage()`, no admin bypass, unlike every other resource in this app. Deleting a folder cascades at the DB level (`cascadeOnDelete` on `parent_id`/`folder_id`) but the controller (`ArchiveController::destroyDossier`) must walk `ArchiveFolder::fichiersRecursifs()` first to delete the physical files from `Storage::disk('public')` before the row cascade fires. The add-file form (Intitulé/Numéro/Description/fichier) has no date input — "date et heure" is just `created_at`, shown after saving.
- **Mail classes**: `app/mail/` (lowercase, note the non-standard casing — not PSR-4 for that reason, composer prints a "does not comply with psr-4" warning on every `dump-autoload`, harmless but expected) holds `ConvocationCodir` and `ConvocationReunion` mailables plus a shared `convocation.blade.php` template, used to notify participants of scheduled meetings. `App\Notifications\ResetPasswordNotification` (proper PSR-4 location) is the password-reset email, in French — `User::sendPasswordResetNotification()` is overridden to use it instead of Laravel's default English notification.
- **Views**: Blade templates under `resources/views/`, one directory per resource (`codirs/`, `reunions/`, `decisions/`, `accords/`, `utilisateurs/`, `exports/`, `email(s)/`), plus shared `layouts/`. No frontend framework — Tailwind v4 + Vite, no Livewire/Inertia. Auth pages (`login`, `forgot-password`, `reset-password`) are standalone full-page Blade files (don't extend `layouts/`), styled with Bootstrap 5 + Bootstrap Icons via CDN, not Tailwind.
- **Translations**: `lang/fr.json` overrides a handful of English strings baked into Laravel's default notification/mail Blade components (`__('All rights reserved.')` etc.) — `resources/views/vendor/` is intentionally *not* published/frozen; the JSON translation applies to the framework's own vendor views without needing a local copy.

## Testing

Real test coverage exists under `tests/Feature/Auth/` (login, rate limiting, password reset, role/policy authorization), `tests/Feature/ExportTest.php`, and `tests/Unit/Models/` (pure model logic) — 33 tests total as of this writing. Factories live in `database/factories/` (`UserFactory`, `AccordFactory`, `ReunionFactory`, `CodirFactory`); `UserFactory` was previously broken (referenced non-existent `name`/`email_verified_at` columns from the default Laravel stub) — it's now aligned with the real `users` schema (`nom`/`prenom`/`role`/`actif`/etc.), with `admin()`/`secretaire()`/`inactif()` states. Tests touching the DB use `RefreshDatabase` against sqlite `:memory:` (per `phpunit.xml`); pure-logic Unit tests still need `Tests\TestCase` (not bare `PHPUnit\Framework\TestCase`) if the model touches a date-cast attribute, since Eloquent's date casting resolves the DB connection even without querying.
