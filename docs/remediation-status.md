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
| Views and frontend | Not started | Logout modal regression coverage | Shared layouts/components/assets; remove response rewriting and duplicated inline CSS/JavaScript |
| Middleware and responses | In progress | CSRF compatibility, upload validation, audit records, security headers | Remove HTML regex rewriting and query-action interception after native forms/routes are complete |
| Duplicate helpers | Not started | Initial duplicates identified | Consolidate role-prefixed helpers, formatting, alignment, schema, and email functions |
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
