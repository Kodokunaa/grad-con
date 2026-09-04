# GradConn remediation status

This checklist tracks the Laravel cleanup checkpoints. A chapter is marked complete only when the legacy compatibility code for that area has been removed and its replacement is covered by tests.

| Chapter | Status | Completed | Still needs fixing |
|---|---|---|---|
| Critical architecture | In progress | Native authentication; initial report/list controllers; native redirects and event deletion | Remaining PDO controllers, `PageContext`, `PageResponse`, `PasswordStatement`, global helpers, and `page-functions.php` |
| Database and migrations | In progress | Preserved baseline schema; Laravel infrastructure; job fields; social/interview upgrade and legacy social-data migration | Remove 29 request-time schema calls/checks; split the monolithic schema; improve rollback strategy |
| Models and relationships | Foundation complete | Active domain models, relationships, casts, and status/role enums | Adopt models throughout legacy controllers; enable role/status enum casts after string comparisons are removed |
| Routing | In progress | GET-only read pages and compatibility redirects; method regression tests | Split multi-action pages into named POST/PATCH/DELETE resource actions; remove destructive query parameters and `.php` URLs |
| Authentication and account security | In progress | Hashing, centralized password policy, rehash-on-login, reset flow, security logging, and other-session invalidation | Convert remaining administrative password writes and remove `PasswordStatement`; review production cookie settings |
| Authorization | In progress | Policies for jobs, applications, offers, interviews, events, training, and private user files | Enforce policies in every retained mutation controller and add record-level tests for each workflow |
| Validation | In progress | Auth validation plus Form Requests for administrative employer, officer, and alumni creation | Add Form Requests for editing, jobs, applications, offers, interviews, events, training, profiles, education, employment, and social actions |
| Uploads and files | In progress | Normalized private paths, Laravel disk writes, recognized categories, resume/certificate policies, portal-image scope tests | Convert deletion and reads fully to `Storage`; collision-resistant names; orphan reconciliation; MIME boundary tests |
| Mail and queues | In progress | Laravel queued delivery and reset notification | Remove PHPMailer-style setup, consolidate mail templates, harden queued attachments, document/verify worker and SMTP deployment |
| Views and frontend | In progress | Shared public-authentication document layout and logout modal regression coverage | Auth asset extraction; authenticated layouts/components/assets; remove response rewriting and duplicated inline CSS/JavaScript |
| Middleware and responses | In progress | Idempotent CSRF/asset/modal augmentation, native auth CSRF, non-fatal audit records, upload validation, security headers | Remove HTML regex rewriting and query-action interception after native forms/routes are complete |
| Duplicate helpers | In progress | Consolidated 20 identical role-prefixed HTML escaping helpers into `gc_e()` | Consolidate formatting, alignment, schema, activity-log, social-feed, and email helpers |
| Configuration and portability | In progress | Laravel root, environment templates, MySQL migration path | Set application timezone, verify clean-device install, document extensions and queue/mail requirements |
| Redundant files | In progress | Obsolete conversion tools, route dump, starter welcome view, and two blank views removed | Remove starter tests, empty directories, build cache, and migration runtime after final verification |
| Tests | In progress | Authentication, roles, routing, policy, upload, and primary workflow coverage | Remaining mutation, validation, mail-worker, migration-upgrade, browser, and clean-device coverage |
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
