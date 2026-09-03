# GradConn application audit

Audit date: 2026-09-04

## Scope

The review covered every application route, 54 migrated page controllers and Blade pages, authentication and password reset, four role boundaries, CSRF behavior, application and offer ownership, private uploads, queued mail, migrations, seeders, current database relationships, deployment caches, dependency advisories, and PHP syntax/style.

## Corrected defects

1. The job-details POST endpoint could create an application without the required profile, terms, and resume checks. It now redirects to the complete application workflow.
2. A direct request to the application page could apply outside a job's start/end dates. The controller now enforces the same availability window as the job listing and details pages.
3. The test environment still used the retired migration database on port 3307. It now uses a dedicated `gradconn_test` database on the normal XAMPP port, with a portable example configuration.
4. Several tests depended on records from one legacy database snapshot. Tests now create their own users, jobs, applications, events, and training fixtures.
5. Validation redirects discarded safe login, registration, and recovery input. Forms now use Laravel's flashed `old()` input.
6. The login screen did not display the successful password-reset message. It now renders the flashed status.
7. Retained Word resumes could not be served. Authorized downloads now support DOC and DOCX containers as attachments while PDF remains inline.
8. Resume filenames could collide within the same second, and failed database inserts could leave orphaned files. Filenames now include cryptographic randomness and failed inserts remove the uploaded file.
9. Password screens described a six-character minimum while middleware required eight. The checks and messages now agree at eight characters.
10. Internal database and mail exception details were rendered to users. Production responses now use a generic message while the full exception is reported to Laravel's log.
11. The legacy `/archive.php` alias was available to every authenticated role even though it redirects to the alumni-officer archive. Its middleware and route inventory now require the alumni-officer role.
12. The profile password handler depended on the submit button field, which browsers may omit for Enter-key or password-manager submissions. It now recognizes the password fields themselves, and the button explicitly submits a stable value.

## Verification

- 25 automated tests pass with 133 assertions.
- Every migrated page renders for its authorized role.
- PHP lint passes for 104 application, route, configuration, migration, seeder, and test files.
- Laravel Pint passes.
- Route caching and Blade compilation pass.
- Composer validation passes and Composer reports no known dependency advisories.
- Live HTTP smoke testing returns 200 with CSRF tokens and the configured frame, content-type, and referrer headers.
- Current database checks found no orphaned application, offer, employment, education, or certificate relationships.
- No embedded API key or active mail password was found in the runtime source.

## Remaining engineering risks

- Much of the feature layer remains mechanically migrated procedural PHP using raw PDO and large controllers. Prepared statements limit injection risk, but future changes should move validation into Form Requests and database work into services or Eloquent models.
- Many pages depend on public CDNs and a remote Bing background image. The interface can lose styling or scripts offline, and a strict Content Security Policy cannot be introduced until these assets are self-hosted or explicitly allowlisted.
- Several legacy routes accept both GET and POST so historical URLs continue to work. Destructive query-string actions are protected by a CSRF confirmation step, but new endpoints should use explicit HTTP methods.
- Production requires a persistent queue worker for email delivery. Without it, mail jobs remain in the `queue_jobs` table.
- The exact legacy source archive may contain the old Gmail app password. It remains ignored and outside the web root, but that credential should be rotated.
