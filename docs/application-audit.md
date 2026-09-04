# GradConn application audit

Audit date: 2026-09-05

## Scope and result

The final review covered application source, routes, controllers, requests, models, policies, services, middleware, Mailables, migrations, seeders, Blade views, assets, configuration, scripts, documentation, and automated tests.

The migrated runtime contains no raw PDO page code, `PageContext`, `PageResponse`, `page-functions.php`, global `gc_*` domain helpers, request-time schema modification, PHPMailer transport, `PageMailer`, or HTML response rewriting. The 20 formerly PDO-backed dashboard/list/feed controllers now load their data through Laravel. Account creation, application decisions, interview scheduling, offer responses, password changes, and alumni approval use explicit Laravel endpoints.

## Security findings

- Password storage and replacement use Laravel hashing. Password changes require the current password and invalidate other sessions.
- Role middleware and record-level policies reject cross-employer and cross-user access.
- CSRF protection is native to Laravel forms.
- Uploads are private, validated, randomly named, and served only after authorization.
- Secrets are environment based. No active API key or mail password is embedded in runtime source.
- Security headers are added to responses, and all unsafe HTTP methods are audit logged.

## Database strategy

The first migration imports the preserved legacy schema only when tables do not exist and records whether it owns that schema. Rollback removes the baseline only when created by Laravel, protecting an upgraded production database. Later migrations are additive and tolerate both fresh and representative legacy starting states. Backups are mandatory before production upgrades.

## Verification evidence

- 48 tests pass with 287 assertions.
- Fresh migration, complete rollback, and reinstall pass against `gradconn_test`.
- Composer strict validation, application readiness checks, route loading, Blade compilation, PHP lint, and Pint are part of the verification workflow.
- Role route contracts, login/logout, password replacement, application ownership, feeds, private files, queued mail construction, and primary workflows are covered.

## Deployment acceptance checks

Run real SMTP, a supervised queue worker, web-server document-root, TLS/cookie, filesystem permission, backup/restore, and Windows/Linux checks on the actual target devices. These depend on deployment-owned credentials and operating systems and cannot be certified by source-level tests on this Windows workstation.
