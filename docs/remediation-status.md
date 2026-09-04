# GradConn remediation status

This checklist tracks the Laravel cleanup checkpoints. A chapter is marked complete only when the legacy compatibility code for that area has been removed and its replacement is covered by tests.

| Chapter | Status | Completed | Still needs fixing |
|---|---|---|---|
| Critical architecture | In progress | Native authentication; initial report/list controllers; native redirects and event deletion | Remaining PDO controllers, `PageContext`, `PageResponse`, `PasswordStatement`, global helpers, and `page-functions.php` |
| Database and migrations | In progress | Preserved baseline schema; Laravel infrastructure; job fields; social/interview upgrade and legacy social-data migration | Remove 29 request-time schema calls/checks; split the monolithic schema; improve rollback strategy |
| Models and relationships | Foundation complete | Active domain models, relationships, casts, and status/role enums | Adopt models throughout legacy controllers; enable role/status enum casts after string comparisons are removed |
| Routing | In progress | GET-only read pages and compatibility redirects; method regression tests | Split multi-action pages into named POST/PATCH/DELETE resource actions; remove destructive query parameters and `.php` URLs |
| Authentication and account security | In progress | Hashing, centralized password policy, rehash-on-login, reset flow, security logging, other-session invalidation, and removal of automatic PDO password rewriting | Review production cookie settings and complete native account-management controllers |
| Authorization | In progress | Policies for jobs, applications, offers, interviews, events, training, private files, and policy-enforced administrative alumni editing | Enforce policies in every retained mutation controller and add record-level tests for each workflow |
| Validation | In progress | Auth validation plus Form Requests for administrative employer, officer, alumni creation, and alumni editing | Add Form Requests for jobs, applications, offers, interviews, events, training, profiles, education, employment, and social actions |
| Uploads and files | In progress | Normalized private paths, Laravel disk writes, recognized categories, resume/certificate policies, portal-image scope tests | Convert deletion and reads fully to `Storage`; collision-resistant names; orphan reconciliation; MIME boundary tests |
| Mail and queues | In progress | Laravel queued delivery, reset notification, configured sender addresses, and removal of PHPMailer transport setup | Consolidate mail templates, harden queued attachments, document/verify worker and SMTP deployment |
| Views and frontend | In progress | Shared public-authentication document layout, native CSRF in every Blade POST form, and logout modal regression coverage | Auth asset extraction; authenticated layouts/components/assets; remove remaining response rewriting and duplicated inline CSS/JavaScript |
| Middleware and responses | In progress | Native form CSRF, idempotent asset/modal augmentation, non-fatal audit records, upload validation, security headers | Remove remaining head/modal HTML rewriting and query-action interception after native layouts/routes are complete |
| Duplicate helpers | In progress | Consolidated 20 identical role-prefixed HTML escaping helpers into `gc_e()` | Consolidate formatting, alignment, schema, activity-log, social-feed, and email helpers |
| Configuration and portability | In progress | Laravel root, environment templates, environment-driven timezone, declared PHP extensions, installation checker, MySQL migration path | Complete isolated clean-device install and production queue/mail verification |
| Redundant files | Complete | Obsolete conversion tools/documentation, route dump, starter tests/views, blank views, empty migration directories, build cache, and temporary migration runtime removed | Reassess generated compatibility files as their owning architecture chapters are completed |
| Tests | In progress | Authentication, native-CSRF form inventory, role-route contracts, explicit POST contracts, response headers, record policies, uploads, mail envelopes, and primary workflows | Remaining mutation, Form Request, mail-worker, migration-upgrade, browser, and clean-device coverage |
| Documentation | In progress | README, audit, route inventory, and this status tracker | Refresh final installation/deployment/backup/troubleshooting instructions after architecture stabilizes |

## Current mail and queue checkpoint

### Done

- Queue-based compatibility delivery through Laravel Mail.
- Correct direct-recipient and BCC separation.
- Configurable sender support through `setFrom()`.
- Plain-text alternatives are retained alongside HTML messages.
- Email addresses are validated before queueing.

### Still needs fixing

- Remove ignored PHPMailer transport properties from page controllers.
- Replace repeated `cccgradconn@gmail.com` values with configured sender/reply-to values.
- Replace compatibility mail calls with dedicated Mailables or Notifications.
- Consolidate duplicate interview, job, offer, approval, and training templates.
- Store queued attachments in a durable location or attach stored disk data.
- Add production SMTP and queue-worker installation checks.

## Current views and frontend checkpoint

### Done

- Added a shared Blade document layout for login, registration, forgot-password, and reset-password pages.
- Centralized language, viewport, title, font preconnections, and Blade style/script slots.
- Removed duplicate `DOCTYPE`, `html`, `head`, and `body` shells from all four public authentication views.
- Preserved each page's existing fields, messages, styling, and routes.

### Still needs fixing

- Extract authentication-page CSS and JavaScript into versioned assets.
- Introduce authenticated layouts for the four roles.
- Convert repeated sidebars, headers, alerts, forms, tables, and modals into Blade components.
- Remove inline CSS from the remaining page views and inline JavaScript from interactive views.
- Remove middleware-based HTML rewriting after forms and logout markup are native Blade components.

## Current middleware and responses checkpoint

### Done

- Added native CSRF fields to all public authentication forms.
- Prevented duplicate CSRF metadata, request-security assets, form tokens, and logout modals when views already provide them.
- Kept compatibility CSRF injection for retained legacy POST forms.
- Prevented audit-log storage failures from replacing an otherwise valid application response.
- Retained security headers and private cache controls.

### Still needs fixing

- Add native CSRF fields to every retained authenticated POST form.
- Move global upload and password validation into route-specific Form Requests.
- Replace destructive GET query interception with explicit mutation routes and confirmation components.
- Remove response-body regular-expression rewriting after the remaining views use shared layouts and components.
- Move audit recording to a dedicated service or event listener with operational failure monitoring.

## Current duplicate helpers checkpoint

### Done

- Replaced 20 identical role-prefixed HTML escaping functions with one shared `gc_e()` helper.
- Updated all PHP helper and Blade callers to use the shared implementation.
- Preserved the existing null handling, string conversion, quote escaping, and UTF-8 behavior.

### Still needs fixing

- Consolidate repeated year, employment-date, and date-range formatting helpers.
- Extract the three copies of course/job alignment analysis into a domain service.
- Replace repeated PDO table/column checks with migrations and schema-backed code.
- Consolidate duplicate activity/security logging functions.
- Consolidate the admin, alumni, and officer social-feed functions.
- Replace repeated application, interview, job-offer, and snapshot email helpers with dedicated Mailables or Notifications.

## Current configuration and portability checkpoint

### Done

- Made the application name and timezone environment-driven with GradConn and `Asia/Manila` defaults.
- Declared the PHP extensions required by the application in Composer metadata.
- Updated the example environment for a standard XAMPP MySQL installation and explicit session/queue settings.
- Added `php artisan gradconn:check --database` for device-readiness diagnostics.
- Documented database-port selection and the portable installation sequence.

### Still needs fixing

- Perform an isolated installation from a clean checkout without reusing the current `vendor`, `.env`, caches, or database.
- Verify queue-worker recovery and durable attachment behavior under a production process manager.
- Verify real SMTP delivery with deployment-owned credentials.
- Document web-server examples for Apache and Nginx with `public` as the document root.
- Verify Linux filesystem permissions and case-sensitive paths.

## Current redundant files and folders checkpoint

### Done

- Removed the two untouched Laravel example tests.
- Removed the obsolete `MIGRATION.md` completion snapshot in favor of the live remediation tracker and README.
- Removed ignored Composer/build caches, compiled views, PHPUnit result cache, and the unused SQLite artifact.
- Removed the stopped temporary migration database runtime and empty migration/work directories.
- Preserved `.migration-backup`, uploaded files, the active XAMPP MySQL data, application source, and frontend build configuration.

### Still needs fixing

- No standalone cleanup item remains in this chapter.
- `PageContext`, `PageResponse`, `PasswordStatement`, `page-functions.php`, and generated page controllers remain required compatibility code; remove them only through their architecture chapters.
- Reassess the Vite starter entry points when shared frontend asset extraction is complete.

## Current testing and verification checkpoint

### Done

- Added a route-contract test that requires every role-prefixed route to retain its matching account middleware.
- Added a contract test that keeps POST routes explicit and inside Laravel's web middleware group.
- Added response-header regression checks for MIME sniffing, framing, and referrer policy.
- Added record-level interview and training policy coverage for administrators, owners, participants, and unrelated accounts.
- Retained existing authentication, password, upload, application, offer, event, training, education, mail-envelope, page-inventory, and role-isolation coverage.

### Still needs fixing

- Add negative and boundary validation tests as each remaining Form Request is introduced.
- Cover every retained mutation with success, unauthorized, invalid-input, and transaction-failure cases.
- Test a real queue worker retry/failure cycle with durable attachments.
- Test migrations against both an empty database and a representative pre-Laravel schema snapshot.
- Add browser tests for login, logout modal, navigation, uploads, applications, offers, interviews, and mobile layouts.
- Run the full installation and test process from a clean checkout on Windows and Linux.

## Current combined security and framework checkpoint

### Done

- Replaced the final automatic PDO password-hashing hook with an explicit hashed Eloquent update.
- Added validated administrative alumni editing and enforced the user policy for edits and deletion.
- Removed obsolete PHPMailer transport configuration and hard-coded sender addresses from runtime mail flows.
- Added native CSRF fields to all 55 retained authenticated Blade POST forms and removed middleware form rewriting.
- Added regression coverage for native CSRF declarations and administrative password updates.

### Still needs fixing

- Complete policy and Form Request adoption in the remaining multi-action compatibility controllers.
- Convert remaining direct upload deletion and filesystem inspection to Laravel `Storage`.
- Replace `PageMailer` and repeated message builders with dedicated Mailables or Notifications.
- Add shared authenticated layouts and remove the remaining head/logout response augmentation.
- Consolidate formatting, alignment, schema, logging, social-feed, and email helper families.
- Complete clean-device, queue-worker, SMTP, browser, and migration-upgrade verification.
