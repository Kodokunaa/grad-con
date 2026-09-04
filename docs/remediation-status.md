# GradConn remediation status

Updated: 2026-09-05

The Laravel migration and runtime cleanup are complete. The application runs from a standard Laravel 13 root and retains the legacy `.php` GET URLs only as user-facing compatibility paths. Mutations use named, method-specific Laravel endpoints.

| Chapter | Status | Result |
|---|---|---|
| Critical architecture | Complete | Controllers use Laravel HTTP, Eloquent/query builder, services, requests, policies, views, and responses. Raw PDO, `PageContext`, `PageResponse`, and runtime legacy bootstrap dependencies are gone. |
| Database and migrations | Complete | A preserved-schema baseline supports both fresh installs and upgrades. Ownership-aware rollback protects legacy databases and drops fresh-install schema safely. Additive migrations own later changes. |
| Routing | Complete | Read pages use GET. Mutations use POST, PUT, PATCH, or DELETE resource endpoints. Typo-era GET aliases are redirects only. No multi-action POST page controller remains. |
| Authentication and account security | Complete | Passwords are hashed, login rehashing is enabled, password changes verify the current password, other sessions are invalidated, recovery is throttled, and security events are logged. |
| Authorization | Complete | Role middleware and record policies protect jobs, applications, offers, interviews, events, training, alumni accounts, employment, certificates, and private files. |
| Validation | Complete | Mutation controllers use Form Requests or route-bound records with explicit transition rules. The former global upload/password validation hook was removed. |
| Uploads and files | Complete | Private files use Laravel `Storage`, randomized names, MIME/size validation, authorization, and failed-write cleanup. |
| Mail and queues | Complete | Mail uses dedicated Mailables and Laravel queues; no PHPMailer or `PageMailer` runtime remains. Worker and SMTP procedures are documented. |
| Views and frontend | Complete | Public auth views share a layout; authenticated feeds share a layout and component; common security/logout/feed behavior is in public assets. Existing role pages retain their presentation while using Laravel partials and named actions. |
| Middleware and responses | Complete | Native CSRF and Laravel responses are used. No response-body rewriting remains. Security headers and mutation audit records are applied centrally. |
| Duplicate helpers | Complete | Global `gc_*` helpers and `page-functions.php` are gone. Retained presentation formatting is namespaced in `ViewFormatter`; social logic is in `SocialFeedService`. |
| Configuration and portability | Complete | Environment-driven database, mail, queue, URL, timezone, and filesystem settings are documented and checked by `gradconn:check`. Windows and POSIX verification scripts are included. |
| Testing and verification | Complete | 48 automated tests cover authentication, roles, policies, password hashing, routes, workflows, files, mail, feeds, migrations, and response security. Fresh install, full rollback, and reinstall were verified on the isolated test database. |
| Documentation | Complete | Installation, upgrade, deployment, backup/restore, testing, and troubleshooting guides are current. |

## Verification boundary

Automated tests use fake mail and controlled queues so they are deterministic. Real SMTP delivery, a continuously supervised production queue worker, Linux permissions, and the final web-server deployment must be exercised on the target host with its credentials. The exact commands and expected outcomes are in `docs/testing.md` and `docs/deployment.md`; these are deployment acceptance checks rather than unfinished application code.

## Compatibility policy

Legacy `.php` GET paths remain where old bookmarks and sidebar links depend on them. They are ordinary Laravel routes and contain no copied legacy source. Misspelled historical paths only redirect to the canonical page. New mutations must be added as named resource routes and must not post back to a page URL.
